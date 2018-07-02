<?php
namespace WeChat\Lib;
use WeChat\Exception\ErrorException;
use WeChat\Util\Request;
use WeChat\Util\HttpRequest;
use WeChat\Application;
use WeChat\Util\Error;

class Oauth extends Application
{
	use Error;
	/**
	 * scope
	 * @var string
	 */
	protected $scope;

	/**
	 * 信息存储key
	 * @var string
	 */
	protected $saveKey = 'wechat_user';

	/**
	 * 回调函数组
	 * @var array
	 */
	protected $callbacks = [];

	/**
	 * 设置获取作用域
	 * @param  string $scope snsapi_base || snsapi_userinfo
	 * @return void
	 */
	public function scope ($scope = 'snsapi_base')
	{
		$this->scope = $scope;
		return $this;
	}

	/**
	 * 设置信息保存key
	 * @param string $key 
	 * @return object
	 */
	public function saveKey ($key = 'wechat_user')
	{
		$this->saveKey = $key;
		return $this;
	}
	/**
	 * 压入执行回调函数
	 * @param  closures  $callback 
	 * @return object         
	 */
	public function push ($callback)
	{
		 if ($callback instanceof \Closure)
		 {
		 	array_push($this->callbacks, $callback);
		 }
		 return $this;
	}
	/**
	 * 网页授权获取
	 * @param  string $redirectUrl 
	 * @return mixed     
	 */
	public function redirect ($redirectUrl = '')
	{
		$request = Request::getInstance();

		if (empty($redirectUrl))
		{
			$queryString = '';
			if (strpos($_SERVER['REQUEST_URI'],'?') !== false)
			{
				$queryString = '?' . explode('?', $_SERVER['REQUEST_URI'])[1];
			}
			$redirectUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . $queryString);
		}
		// 获取code
		if (empty($request->get('code')) && $request->get('state') != 'STATE') {
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$config['app_id'] . "&redirect_uri=" .$redirectUrl . "&response_type=code&scope=" . $this->scope . "&state=STATE#wechat_redirect";
			return $request->redirect($url);
		}
		// 通过code获取access_token
		$result = HttpRequest::get($this->apiUrl . 'sns/oauth2/access_token',[
			'appid'  	 => self::$config['app_id'],
			'secret' 	 => self::$config['app_secret'],
			'code'	 	 => $request->get('code'),
			'grant_type' => 'authorization_code'
		])->jsonToArray()->read();

		$data = $this->getData($result);

		if (isset($data['errcode']))
		{
			throw new ErrorException("授权登录错误,获取失败->" . $data['errmsg']);
		}
		if ($this->scope == 'snsapi_base')
		{
			$this->callBack($data);
			return $data;
		}
		// 检测token是否过期
		if ($this->tokenWhetherExpire($data['access_token'],$data['openid']))
		{
			// 过期则刷新access_token
			$data = $this->refreshAccessToken(self::$config['app_id'],$data['refresh_token']);	
		}

		$userInfo = new \WeChat\Lib\Oauth\User($data);
		$_SESSION[$this->saveKey] = $userInfo;

		$this->callBack($userInfo);

		return $data;
	}

	/**
	 * 执行回调
	 * @param  array $params 
	 * @return mixed    
	 */
	private function callBack ($params)
	{
		if (!empty($this->callbacks))
		{
			foreach ($this->callbacks as $callback)
			{
				$callback($params);
			}
		}
	}
	/**
	 * 授权获取用户信息
	 * @param  string $redirectUrl 
	 * @return object
	 */
	public function user ($redirectUrl = '')
	{
		if (empty($_SESSION[$this->saveKey])) {
			$this->scope('snsapi_userinfo')->redirectUrl($redirectUrl);
		}
		return $_SESSION[$this->saveKey];
	}
	/**
	 * 检测token是否过期
	 * @param string $accessTiken 
	 * @param string $openId 
	 * @return bool
	 */
	public function tokenWhetherExpire ($accessToken,$openId)
	{
		$result = HttpRequest::get($this->apiUrl . 'sns/auth',[
			'access_token' => $accessToken,
			'openid'       => $openId
		])->jsonToArray()->read();
		return $result['errcode'] == '0' ? false : true;
	}
	/**
	 * 刷新access_token
	 * @param  string $appId        
	 * @param  string $refreshToken 
	 * @return array             
	 */
	public function refreshAccessToken ($appId,$refreshToken)
	{
		$jsonData = HttpRequest::get($this->apiUrl . 'sns/oauth2/refresh_token',[
			'appid' 	    => $appId,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken
		])->jsonToArray()->read();
		$result = $this->getData($jsonData);
		if (isset($result['errcode']))
		{
			throw new ErrorException("refresh_token刷新失败");
		}
		return $result;
	}
}