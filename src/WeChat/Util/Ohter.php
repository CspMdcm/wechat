<?php
namespace WeChat\Util;

trait Ohter
{
	/**
	 * 模拟分页获取数据
	 * @param  array  $data   待获取数据
	 * @param  integer $index 获取索引   
	 * @param  integer $length 获取行数
	 * @return array
	 */
	public function paged ($data,$index,$length = 3)
	{
	     $offset = ($index - 1) * $length;
	     $array  = [];
	     for ($i = 0; $i < $length; $i++) {
	     	$key = $offset + $i;
	        if (isset($data[$key])) {
	           $array[] = $data[$key];
	        }
	     }
	     return $array;
	}
	/**
	 * 生成随机位数字符串
	 * @param  integer $length 
	 * @return string      
	 */
	public function createNonceStr ($length = 16)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str   = "";
	    for ($i = 0; $i < $length; $i++) {
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	}
	/**
	 * 获取请求客户端ip
	 * @param integer $type 
	 * @return string
	 */
	public function getClientIp ($type = 0)
	{
		$type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {//nginx 代理模式下，获取客户端真实IP
            $ip=$_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
	}
	/**
	 * 生成签名
	 * @param  array $params 
	 * @param  string $apiKey
	 * @return string    
	 */
	public function makeSign ($params = [],$apiKey = '')
	{
		//签名步骤一：按字典序排序数组参数
		ksort($params);
		$string = $this->toUrlParams($params);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=" . $apiKey;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	/**
	 * 生成url参数
	 * @param  array $params 
	 * @return string         
	 */
	public function toUrlParams ($params = [])
	{
		$string = '';
		if (!empty($params)) 
		{
			$array = [];
			foreach($params as $key => $value){
				$array[] = $key.'='.$value;
			}
			$string = implode("&",$array);
		}
		return $string;
	}
}