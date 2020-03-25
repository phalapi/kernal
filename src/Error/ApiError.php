<?php
namespace PhalApi\Error;

/**
 * 接口错误类
 *
 * @package     PhalApi\Error\ApiError
 * @license     http://www.phalapi.net/license GPL 协议 GPL 协议
 * @link        http://www.phalapi.net/
 * @author      dogstar <chanzonghuang@gmail.com> 2020-03-25
 */
class ApiError implements \PhalApi\Error {

    public function __construct() {
        set_error_handler(array($this, 'handleError'));
    }

    /**
     * 自定义的错误处理函数
     * - 错误转异常
     * - 追加项目的回调处理
     * 
     * @param int $errno 包含了错误的级别，是一个 integer
     * @param string $errstr 包含了错误的信息，是一个 string
     * @param string $errfile 可选的，包含了发生错误的文件名，是一个 string
     * @param int $errline 可选项，包含了错误发生的行号，是一个 integer
     * @link https://www.php.net/manual/zh/function.set-error-handler.php
     */
    public function handleError($errno, $errstr, $errfile = '', $errline = 0) {
        // if (!(error_reporting() & $errno)) {
        //    // This error code is not included in error_reporting, so let it fall
        //    // through to the standard PHP error handler
        //    return false;
        // }

        $error = 'Unknow';
        $isstop = FALSE;

        switch ($errno) {
        case E_PARSE:
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
            $error = 'ERROR';
            $isstop = TRUE;
            break;
        case E_WARNING:
        case E_USER_WARNING:
        case E_COMPILE_WARNING:
        case E_RECOVERABLE_ERROR:
            $error = 'WARNING';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $error = 'NOTICE';
            break;
        case E_STRICT:
            $error = 'STRICT';
            $isstop = TRUE;
            break;
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $error = 'DEPRECATED';
            break;
        default:
            break;
        }

        $context = array(
            'error' => $error,
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'isstop' => $isstop,
            'time' => time(),
        );
        $context['message'] = \PhalApi\T('{error} ({errno}): {errstr} in [File: {errfile}, Line: {errline}, Time: {time}]', $context);

        $this->reportError($context);

        if ($isstop) {
            throw new \Exception($context['message']);
        }

        return TRUE;
    }

    /**
     * 上报错误
     * @param array $context
     */
    protected function reportError($context) {
        $logger = \PhalApi\DI()->logger;
        if ($logger) {
            $message = $context['message'];
            unset($context['message']);
            $logger->error($message, $context);
        }
    }

}

