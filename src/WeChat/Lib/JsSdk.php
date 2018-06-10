<?php
namespace WeChat\Lib;
use WeChat\Exception\ErrorException;
use WeChat\Application;
use WeChat\Util\Error;
use WeChat\Util\HttpRequest;

class JsSdk extends Application
{
	/**
	 * 获取sign package
	 * @return array
	 */
	public function getSignPackage ()
	{
	   $jsapiTicket  = $this->getJsApiTicket();

	    $protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $url         = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	    $timestamp   = time();
	    $nonceStr    = $this->createNonceStr();

	    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
	    $string      = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

	    $signature   = sha1($string);

	    $signPackage = array(
	      "appId"     => self::$config['app_id'],
	      "nonceStr"  => $nonceStr,
	      "timestamp" => $timestamp,
	      "url"       => $url,
	      "signature" => $signature,
	      "rawString" => $string
	    );
	    return $signPackage; 
	}
	/**
	 * 获取ticket
	 * @param boolean $cache 
	 * @return string
	 */
	public function getJsApiTicket ($cache = true)
	{
		$cacheFileName = md5(self::$config['app_id'] . self::$config['app_secret']) . 'JsApiTicket';
		$cacheFile     = dirname(__DIR__) . '/Cache/' . $cacheFileName . '.php';

		if ($cache === true && is_file($cacheFile) && unserialize(file_get_contents($cacheFile))['create_time'] + 7000 > time())
		{
			// 缓存有效,直接获取缓存内容
			$content = file_get_contents($cacheFile);
			$data 	 = unserialize($content)['data'];
		}
		else
		{
			$data    = HttpRequest::get($this->apiUrl . 'cgi-bin/ticket/getticket',[
				'type' 			=> 'jsapi',
				'access_token'  => $this->getAccessToken()
			])->jsonToArray()->read();
			if (!isset($data['errcode']) || $data['errcode'] != 0)
			{
				$this->log->write(['message' => 'jsapi_ticket 获取失败','data' => $data]);
				throw new ErrorException('jsapi_ticket获取失败' . $data['errmsg']);
			}
			// 缓存access_token
			$cachePath 	  = dirname($cacheFile);
			is_dir($cachePath) || mkdir($cachePath,0755,true);
			$cacheContent = serialize(['data' => $data,'create_time' => time()]);
			file_put_contents($cacheFile,$cacheContent);
		}
		return $data['ticket'];
	}
}