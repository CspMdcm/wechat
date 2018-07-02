<?php
namespace WeChat\Lib;
use WeChat\Exception\ErrorException;
use WeChat\Util\Request;
use WeChat\Util\HttpRequest;
use WeChat\Application;
use WeChat\Util\Error;
use WeChat\Exception\WeChatPayException;
use WeChat\Util\Ohter;
use WeChat\Util\Xml;

class Order extends Application
{
	use Ohter;

	/**
	 * 请求接口url
	 * @var string
	 */
	private $apiUrlPrefix = 'https://api.mch.weixin.qq.com';
	/**
	 * 统一下单接口
	 * @var string
	 */
	private $apiUrlUnify  = '/pay/unifiedorder';


	/**
	 * 统一下单
	 * @param  array $params 
	 * @return mixed
	 */
	public function unify ($params = [])
	{
		$params['app_id'] = self::$config['app_id'];	
		$params['mch_id'] = self::$config['merch_id'];
		
		if (!isset($params['body']))
		{
			$params['body']  = 'Pay';
		}
		if (!isset($params['nonce_str']))
		{
			$params['nonce_str'] = $this->createNonceStr();
		}
		if (!isset($params['spbill_create_ip']))
		{
			$params['spbill_create_ip'] = $this->getClientIp();
		}
		if (!isset($params['notify_url']))
		{
			$params['notify_url'] = self::$config['notify_url'];
		}
		if (!isset($params['trade_type']))
		{
			$params['trade_type'] = 'JSAPI';
		}
		if (!isset($params['openid']))
		{
			$params['openid'] = $this->getOpenId();
		}
		$sign = $this->makeSign($params,self::$config['api_key']);
		$params['sign'] = $sign;
		$xml = new Xml($params);
		$response = $this->xmlHttpRequest($xml->toXml(),$this->apiUrlPrefix . $this->apiUrlUnify);
		if (!$response)
		{
			return false;
		}
		$result = $xml->toArray($response);
		if (!empty($result['result_code']) && !empty($result['err_code']))
		{
			$result['erro_msg'] = $this->getErrCodeMsg($result['err_code']);
		}
		return $result;
	}
	/**
	 * 通知回调验证
	 * @param  mixed $callback 
	 * @return mixed
	 */
	public function notify ($callback = null)
	{
		$xmlStr = file_get_contents("php://input");
		$xml    = new Xml();
		if (!$xml->isXml($xmlStr))
		{
			exit("FAIL");
		}
		$data = $xml->toArray($xmlStr);
		$wechatSign = $data['sign'];
		unset($data['sign']);
		$sign = $this->makeSign($data,self::$config['api_key']);
		if ($sign != $wechatSign)
		{
			exit("FAIL");
		}
		if ($callback == null)
		{
			return $data;
		}
		$callback($data);
		exit("SUCCESS");
	}
	/**
	 * 发送xmlhttpcurl请求
	 * @param  string  $xml     
	 * @param  string  $url     
	 * @param  boolean $useCert 
	 * @param  integer $second  
	 * @return mixed      
	 */
	public function xmlHttpRequest ($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		if($useCert)
		{
			//设置证书
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,self::$config['ssl_cert_path']);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,self::$config['ssl_key_path']);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		if (!$data)
		{
			$error = curl_errno($ch);
			throw new WeChatPayException($error);
		}
		curl_close($ch);
		return $data;
	}
	/**
	 * 获取错误码对应错误消息
	 * @param  string $errCode 
	 * @return string   
	 */
	public function getErrCodeMsg ($errCode)
	{
		$errList = array (
			'NOAUTH' => '商户未开通此接口权限',
			'NOTENOUGH' => '用户帐号余额不足',
			'ORDERNOTEXIST' => '订单号不存在',
			'ORDERPAID' => '商户订单已支付，无需重复操作',
			'ORDERCLOSED' => '当前订单已关闭，无法支付',
			'SYSTEMERROR' => '系统错误!系统超时',
			'APPID_NOT_EXIST' => '参数中缺少APPID',
			'MCHID_NOT_EXIST' => '参数中缺少MCHID',
			'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
			'LACK_PARAMS' => '缺少必要的请求参数',
			'OUT_TRADE_NO_USED' => '同一笔交易不能多次提交',
			'SIGNERROR' => '参数签名结果不正确',
			'XML_FORMAT_ERROR' => 'XML格式错误',
			'REQUIRE_POST_METHOD' => '未使用post传递参数 ',
			'POST_DATA_EMPTY' => 'post数据不能为空',
			'NOT_UTF8' => '未使用指定编码格式',
		);
		if (array_key_exists($errCode,$errList))
		{
			return $errList[$errCode];
		}
		return "未知错误";
	}
	/**
	 * 获取openid
	 * @return string
	 */
	public function getOpenId ()
	{
		if (isset($_SESSION['wechat_user_openid']))
		{
			return $_SESSION['wechat_user_openid'];
		}
		$openid = '';
		$this->oauth->scope('snsapi_base')->push(function ($data) use (&$openid) {
			$openid = $data['openid'];
			$_SESSION['wechat_user_openid'] = $openid;
		})->redirect();
		return $openid;
	}
}