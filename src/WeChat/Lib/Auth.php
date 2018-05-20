<?php
namespace WeChat\Lib;
use WeChat\Application;
use WeChat\Exception\ErrorException;

class Auth extends Application
{
	/**
	 * 验证token
	 * @access public
	 * @param  string $token 
	 * @return void
	 */
	public function checkToken ($token = '')
	{
		$token = $token ?: self::$config['token'];
		if (!isset($_GET['signature']) || !isset($_GET['nonce']) || !isset($_GET['timestamp'])) {
			throw new ErrorException("参数错误！");
		}
		//先获取到这三个参数
		$signature = $_GET['signature'];   
		$nonce     = $_GET['nonce']; 
		$timestamp = $_GET['timestamp']; 
	    //把这三个参数存到一个数组里面
	    $tmpArr = array($timestamp,$nonce,$token); 
	    //进行字典排序
	    sort($tmpArr);  

	    //把数组中的元素合并成字符串，impode()函数是用来将一个数组合并成字符串的
	    $tmpStr = implode('',$tmpArr);  

	    //sha1加密，调用sha1函数
	    $tmpStr = sha1($tmpStr);
	    //判断加密后的字符串是否和signature相等        
	    $result = $tmpStr == $signature ? true : false;
	    if ($result) {
	    	$echostr = isset($_GET['echostr']) && !empty($_GET['echostr']) ? $_GET['echostr'] : '';
	    	echo $echostr;exit;
	    }
	    throw new ErrorException("验证错误");
	}
}