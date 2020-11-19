<?php
namespace PhalApi\Response;

/**
 * HtmlResponse 响应类
 *
 * - 支持页面渲染返回输出
 *
 * \PhalApi\DI()->response = new \PhalApi\Response\HtmlResponse(); // 重新注册
 * 
 * @author 大卫 dogstar
 */
class HtmlResponse extends JsonResponse
{
    protected $namespace = 'app';   // 命名空间
    protected $themes;  // 模板主题
    protected $name = 'Site/index'; // 要调用的模板名
    protected $param = array();     // 模板参数[app数据、公共数据等]
    protected $ext;


    public function __construct($themes = 'Default', $ext = '.php') {
        $this->themes = $themes;
        $this->ext = $ext;

        $this->addHeaders('Content-Type', 'text/html;charset=utf-8');
    }

    /**
     * 格式化需要输出返回的结果
     * @param $result
     * @return false|string
     * @throws \Exception
     */
    protected function formatResult($result)
    {
        $this->adjustHttpStatus();
        $this->namespace = \PhalApi\DI()->request->getNamespace();
        $api        = \PhalApi\DI()->request->getServiceApi();
        $action     = \PhalApi\DI()->request->getServiceAction();
        $this->name = $api . '/' . $action;
        if ($this->ret === 200) {
            return $this->load($this->name, $result['data']);
        } elseif ($this->ret > 200 && $this->ret <=206) {
            return $this->load($api, $result['data']);
        }

        return $this->load('error', $result);
    }

    /**
     * 注入单个变量
     * @param $k
     * @param $v
     */
    public function fetch($k, $v)
    {
        $this->param[$k] = $v;
    }

    /**
     * 注入数组变量
     * @param array $param 参数 $K => $v
     */
    public function assign($param = array())
    {
        if (is_array($param)) {
            foreach ($param as $k => $v) {
                $this->fetch($k, $v);
            }
        }
    }

    /**
     * 设置模板主题
     * @param $themes
     */
    public function setThemes($themes)
    {
        $this->themes = $themes;
    }

    /**
     * 获取模板路径
     * @param string $name
     * @return string
     */
    private function path($name = '') {
        return API_ROOT . '/src/' . lcfirst($this->namespace) . '/View/' . $this->themes . '/' . $name . $this->ext;
    }

    /**
     * 装载模板
     * @param string $name html文件名称
     * @param array $param
     * @return false|string
     * @throws \Exception
     */
    public function load($name, $param = array())
    {
        $viewTplPath = $this->path($name);
        if (!file_exists($viewTplPath)) {
            exit($viewTplPath . ' 模板文件不存在');
        }
        // 合并参数
        $param = is_array($param) ? array_merge($this->param, $param) : $this->param;
        // 将数组键名作为变量名，如果有冲突，则覆盖已有的变量
        extract($param, EXTR_OVERWRITE);
        unset($param);

        ob_start();
        //ob_implicit_flush(false);
        require($viewTplPath);
        // 获取当前缓冲区内容
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
