<?php
namespace PhalApi\Crypt;

use PhalApi\Crypt;
use PhalApi\Exception\InternalServerErrorException;

/**
 * McryptCrypt 对称加密（OpenSSL 实现）
 *
 * 原实现依赖 mcrypt 扩展，而 mcrypt 自 PHP 7.2 起已从 PHP 移除，
 * 因此在 PHP 8 下原实现必然抛出 "Call to undefined function mcrypt_*()"。
 * 本类改用 OpenSSL 的 AES-256-CBC 重新实现，对外接口、类名与调用方式保持不变。
 *
 * <br>使用示例：<br>
```
 *  $mcrypt = new McryptCrypt('12345678');
 *
 *  $data = 'dogstar love php';
 *  $key = 'secrect';
 *
 *  // 加密
 *  $encryptData = $mcrypt->encrypt($data, $key);
 *
 *  // 解密
 *  $decryptData = $mcrypt->decrypt($encryptData, $key);
```
 *
 * - 补位方式沿用原实现：按块大小补 \0，解密后 rtrim("\0")，因此密文长度恒为 16 的整数倍
 * - 与历史 mcrypt 密文<b>不兼容</b>：如需迁移旧数据，须先在 PHP 7.1 及以下用 mcrypt 解密，再在 PHP 8 下重新加密
 *
 * @package     PhalApi\Crypt
 * @license     http://www.phalapi.net/license GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2014-12-10
 */

class McryptCrypt implements Crypt {

    /**
     * @var string $iv 加密向量，最大长度不得超过 McryptCrypt::MAX_IV_SIZE
     */
    protected $iv;

    /**
     * @var string 使用的加密算法，AES-256-CBC 的密钥长度固定 32 字节、块大小 16 字节
     */
    const CIPHER = 'AES-256-CBC';

    /**
     * @var int 最大加密向量长度（AES 块大小）
     */
    const MAX_IV_SIZE = 16;

    /**
     * @var int 最大加密key的长度（AES-256）
     */
    const MAX_KEY_LENGTH = 32;

    /**
     * @param string $iv 加密的向量 最大长度不得超过 MAX_IV_SIZE
     */
    public function __construct($iv = '********') {
        $this->iv = str_pad($iv, static::MAX_IV_SIZE, '*');
        if (strlen($this->iv) > static::MAX_IV_SIZE) {
            $this->iv = substr($this->iv, 0, static::MAX_IV_SIZE);
        }
    }

    /**
     * 对称加密 
     *
     * @param string $data 待加密的数据
     * @param string key 私钥
     * @return string 加密后的数据
     */
    public function encrypt($data, $key) {
        if ($data === '') {
            return $data;
        }

        $this->checkOpenSSL();

        $encrypted = openssl_encrypt(
            $this->padZero($data),
            static::CIPHER,
            $this->formatKey($key),
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->iv
        );

        if ($encrypted === FALSE) {
            throw new InternalServerErrorException(
                \PhalApi\T('openssl_encrypt with {cipher}', array('cipher' => static::CIPHER))
            );
        }

        return $encrypted;
    }

    /**
     * 对称解密
     *
     * @see McryptCrypt::encrypt()
     * 
     * @param string $data 待解密的数据
     * @param string key 私钥
     * @return string 解密后的数据
     */
    public function decrypt($data, $key) {
        if ($data === '') {
            return $data;
        }

        $this->checkOpenSSL();

        $decrypted = openssl_decrypt(
            $data,
            static::CIPHER,
            $this->formatKey($key),
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            $this->iv
        );

        if ($decrypted === FALSE) {
            throw new InternalServerErrorException(
                \PhalApi\T('openssl_decrypt with {cipher}', array('cipher' => static::CIPHER))
            );
        }

        return rtrim($decrypted, "\0");
    }

    /**
     * 按块大小补 \0，与 mcrypt_generic 的补位行为保持一致
     * @param string $data
     * @return string
     */
    protected function padZero($data) {
        $blockSize = static::MAX_IV_SIZE;
        $remainder = strlen($data) % $blockSize;

        return $remainder === 0 ? $data : $data . str_repeat("\0", $blockSize - $remainder);
    }

    /**
     * 格式化私钥，不足补 \0，超长截断，确保恒为 32 字节
     * @param string $key 私钥
     */
    protected function formatKey($key) {
        $key = (string) $key;

        return strlen($key) >= static::MAX_KEY_LENGTH
            ? substr($key, 0, static::MAX_KEY_LENGTH)
            : str_pad($key, static::MAX_KEY_LENGTH, "\0");
    }

    /**
     * 检查运行环境是否支持 OpenSSL
     * @throws InternalServerErrorException
     */
    protected function checkOpenSSL() {
        if (!function_exists('openssl_encrypt')) {
            throw new InternalServerErrorException(
                \PhalApi\T('openssl extension is required by {class}', array('class' => __CLASS__))
            );
        }
    }
}
