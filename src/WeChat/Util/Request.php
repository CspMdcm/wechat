<?php
namespace WeChat\Util;

class Request 
{
	private static $instance;  // 单例对象
    protected $method;        // 请求方法
    private $get;
    private $post;
    private $server;
    private function __construct ()
    {

    }
    private function __clone () {} // 单例禁止克隆
    
    /**
     * 路由重定向
     * @param  string $url  URL地址
     * @param  string $time 跳转时间
     * @return void
     */
    public function redirect ($url,$time = '')
    {
         if ( !headers_sent() ) {
            empty($time) ? header('Location:' . $url) : header("refresh:{$time};url={$url}");;
         } else {
            echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
         }
         exit;
    }
    /**
     * 获取header数据
     * @param  string $key    获取数据参数
     * @param  array  $ignore 自动忽略header数据
     * @return string
     */
    public function getHeaderData ($key = '',$ignore = ['host','accept','content-length','content-type'])
    {
        $headers = [];
        $server  = $this->server();
        foreach ($server as $k => $v) {
            if (substr($k,0,5) == 'HTTP_') {
                $k = substr($k,5);
                $k = str_replace('_','',$k);
                $k = str_replace('','-',$k);
                $k = strtolower($k);
                if ( !in_array($k,$ignore) ) {
                    $headers[$k] = $v;
                }
            }
        }
        if ( empty($key) ) return $headers;
        return isset($headers[$key]) ? $headers[$key] : '';
    }

    /**
     * 返回服务器请求时间
     * @return integer 时间戳
     */
    public function requestTime ()
    {
        return !empty($this->server('REQUEST_TIME')) ? $this->server('REQUEST_TIME') : time();
    }
    /**
     * 获取返回请求来源
     * @return string
     */
    public function requesting ()
    {
        if (strpos($this->server('HTTP_USER_AGENT'),'iPhone') || strpos($this->server('HTTP_USER_AGENT'),'iPad')) {
            return 'IOS';
        } elseif ( strpos($this->server('HTTP_USER_AGENT'),'Android') ) {
           return 'Android';
        }
        return 'Web';
    }

    /**
     * 获取GLOBALS数据
     * @param bool $tranJson 是否转化为json
     * @return string
     */
    public function getGlobalData ($tranJson = false)
    {
       $data   = isset($GLOBALS['HTTP_RAW_POST_DATA']) && !empty($GLOBALS['HTTP_RAW_POST_DATA']) 
                           ? $GLOBALS['HTTP_RAW_POST_DATA']                                                              
                           : file_get_contents('php://input');
       $result = $tranJson ? json_decode($data,true) : $data; 
       return $result;
          
    }
    /**
     * 返回单例对象
     * @return Object
     */
    public static function getInstance ()
    {
         if (is_null(self::$instance)) {
            self::$instance = new static(); 
         }
         return self::$instance;
    }
    /**
     * 获取GET数据
     * @param  string 获取数据名称
     * @param  string 自定义数据
     * @param  string $default 默认值
     * @return string
     */
    public function get ($name = '',$data = [],$default = '')
    { 
        // 如果没传入名称返回过滤的$_GET
        if (empty($name)) {
            return !empty($_GET) ? $this->filterData($_GET) : [];
        }
        if (empty($data)) {
            $this->get = $_GET;
        } else {
            $this->get = array_merge($this->get,$data);
        }
        return $this->input($name,$this->get,$default);
    }
    /**
     * 获取POST数据
     * @param  string 获取数据名称
     * @param  string 自定义数据
     * @param  string $default 默认值
     * @return string
     */
    public function post ($name = '',$data = [],$default = '')
    {
        // 如果没传入名称返回过滤的$_POST
        if (empty($name)) {
            return !empty($_POST) ? $this->filterData($_POST) : [];
        }
        if (empty($data)) {
           $this->post = $_POST;
        } else {
           $this->post = array_merge($this->post,$data);
        }
        return $this->input($name,$this->post,$default);
    }
    /**
     * 获取 SERVER数据
     * @param  string $name    获取数据名称
     * @param  array $data     自定义数据
     * @param  string $default 默认值
     * @return string
     */
    public function server ($name = '',$data = [],$default = '')
    {
        // 如果没传入名称返回过滤的$_SERVER
        if (empty($name)) {
            return !empty($_SERVER) ? $this->filterData($_SERVER) : [];
        }
        if (empty($data)) {
            $this->server = $_SERVER;
        } else {
            $this->server = array_merge($this->server,$data);
        }
        return $this->input($name,$this->server,$default);
    }
    /**
     * 获取输入数据
     * @param  string 获取数据名称
     * @param  string|array 获取的数据值
     * @param  string $default 默认值
     * @return string
     */
    public function input ($name,$data = [],$default = '')
    {
         if (empty($data)) {
            return $default;
         }
         $filterData = $this->filterData($data);
         if (!strpos($name,'.')) {
             return isset($filterData[$name]) ? $filterData[$name] : $default;
         }
         $args = explode('.',$name); // 最高可获取二维数组值

         return isset($filterData[$args[0]][$args[1]]) ? $filterData[$args[0]][$args[1]] : $default;
    }
    /**
     * php防注入和xss攻击通用过滤
     * @param  string|array|object $data 需要过滤字符串数组或对象
     * @return string
     */
    public function filterData ($data = [])
    {
        $result = '';
        if (is_string($data)) {
            return htmlspecialchars($data);
        }
        if (is_array($data)) {
           foreach ($data as $k => $v) {
               $result[$k] = $this->filterData($v);
           }
        } else if (is_object($data)) {
           foreach ($data as $k => $v) {
               $data->$k = $this->filterData($v);
           }
        } else {
          $result = htmlspecialchars($result);
        }
        return $result;
    }
}