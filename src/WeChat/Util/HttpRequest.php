<?php
namespace WeChat\Util;

class HttpRequest 
{
    /**
     * 获取返回值
     * @var string
     */
    private $result;
    /**
     * 编码列表
     * @var array
     */
    public $encodes = ["ASCII","UTF-8","GBK","BIG5","Unicode"];

    /**
     * 构造方法初始化
     * @param array $result 
     */
    public function __construct ($result)
    {
        $this->result = $result;
    }
    /**
     * 读取
     * @return string
     */
    public function read ()
    {
        return $this->result;
    }
    /**
     * 编码转换
     * @param  string $encoding 编码
     * @return string
     */
    public function encode ($encoding = 'utf-8',$deconding = '')
    {
        $encode = $deconding ?: mb_detect_encoding($this->result,$this->encodes);
        $this->result = iconv($encode, $encoding . '//IGNORE', $this->result);
        return $this;
    }
    /**
     * 发送post请求
     * @param  string $url 请求url     
     * @param  array $data 请求data
     * @param  array $headerData 发送头部信息
     * @return array
     */
    public static function post ($url = '',$data = [],$headerData = [])
    {
        return self::send($url,$data,'POST',$headerData);
    }
    /**
     * 发送get请求 
     * @param  string $url 请求url     
     * @param  array $data 请求data
     * @param  array $headerData 发送头部信息
     * @return array
     */
    public static function get ($url = '',$data = [],$headerData = [])
    {
        return self::send($url,$data,'GET',$headerData);
    }
    /**
     * json转换
     * @param bool $toArr 
     * @return array|object
     */
    private function jsonTo ($toArr)
    {
        try {
            $json = json_decode($this->result,$toArr);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }
            $this->result = $json;
        } catch (\Exception $e) {
             throw $e;
        }
        return $this;
    }
    /**
     * json转换为数组
     * @return object
     */
    public function jsonToArray ()
    {
        return $this->jsonTo(true);
    }
    /**
     * json转换为对象
     * @return object
     */
    public function jsonToObject ()
    {
        return $this->jsonTo(false);
    }
    /**
     * 发送http请求
     * @param  string $url 请求url     
     * @param  array $data 请求data
     * @param  string $method 请求方法
     * @param  array $headerData 发送头部信息
     * @return array             
     */
    public static function send ($url = [],$data = [],$method = 'GET',$headerData = [])
    {
        // 统一大写
        $method = strtoupper($method);
        //初始化 
        $ch = curl_init();   
        // 如果请求头信息不为空则设置请求头信息
        !empty($headerData) && curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        // post方式的时候添加数据  
        $method == 'POST' && !empty($data) && curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // get方法的时候添加get数据
        $method == 'GET' && !empty($data) && $url .= '?' . http_build_query($data);
        // 请求地址
        curl_setopt($ch, CURLOPT_URL, $url);   
        // 请求方式
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);  
        // 设置
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//  https请求不验证hosts 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面 
        // 执行获取结果
        $result = curl_exec($ch);
        // 如果错误返回错误信息
        if (curl_errno($ch)) throw new \Exception(curl_error($ch));
        // 关闭
        curl_close($ch);
        // 返回结果
        return new static($result);  
    }
}