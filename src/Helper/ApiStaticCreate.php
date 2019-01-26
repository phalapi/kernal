<?php
/**
 * Created by PhpStorm.
 * User: niebangheng
 * Date: 2019/1/26
 * Time: 14:53
 */

namespace PhalApi\Helper;


class ApiStaticCreate extends ApiList
{

    protected $webRoot = '';

    public function render($tplPath = NULL) {

        $trace = debug_backtrace();
        $listFilePath = $trace[0]['file'];
        $this->webRoot = substr($listFilePath, 0, strrpos($listFilePath, D_S));
        global $argv;
        $theme = isset($argv[1]) ? $argv[1] : 'fold';
        ob_start();
        // 运行模式
        parent::render($tplPath);
        $string = ob_get_clean();
        \PhalApi\Helper\saveHtml($this->webRoot, 'index', $string);
        $str = <<<EOT
Usage:

生成展开版：  php {$argv[0]} expand
生成折叠版：  php {$argv[0]} fold

脚本执行完毕！离线文档保存路径为：
EOT;
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $str = iconv('utf-8', 'gbk', $str);
        }
        echo $str, $this->webRoot . D_S . 'docs', PHP_EOL, PHP_EOL;

    }

    public function makeApiServiceLink($service, $theme = '') {
        ob_start();
        // 换一种更优雅的方式
        \PhalApi\DI()->request = new \PhalApi\Request(array('service' => $service));
        $apiDesc = new \PhalApi\Helper\ApiDesc($this->projectName);
        $apiDesc->render();

        $string = ob_get_clean();
        \PhalApi\Helper\saveHtml($this->webRoot, $service, $string);
        $link = $service . '.html';
        return $link;
    }

    public function getUri() {
        return '';
    }
}