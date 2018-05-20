<?php
namespace WeChat\Lib;
use WeChat\Application;
use WeChat\Exception\ErrorException;
use WeChat\Util\Error;
use WeChat\Util\HttpRequest;

class User extends Application
{
	use Error;

	/**
	 * 最大查询数
	 * @var integer
	 */
	protected $maxSelect = 100;

	/**
	 * 获取用户列表
	 * @param  string $nextOpenId 
	 * @return array
	 */
	public function lists ($nextOpenId = '')
	{
		$params = ['access_token' => $this->accessToken];
		if (!empty($nextOpenId)) {
			$params['next_openid'] = $nextOpenId;
		}
		$userList = HttpRequest::get($this->apiUrl . 'cgi-bin/user/get',$params)->jsonToArray()->read();
		return $this->getData($userList);
	}
	/**
	 * 获取用户信息
	 * @param  string $openId 
	 * @return array
	 */
	public function get ($openId,$lang = 'zh_CN')
	{
		$params   = ['access_token' => $this->accessToken,'openid' => $openId,'lang' => $lang];
		$userInfo = HttpRequest::get($this->apiUrl . 'cgi-bin/user/info',$params)->jsonToArray()->read();
		return $this->getData($userInfo);
	}
	/**
	 * 批量获取用户信息
	 * @param  array $options 
	 * @return array  
	 */
	public function select ($options = [])
	{
		$params['user_list'] = [];
		if (count($options) > $this->maxSelect) {
			$options = $this->paged($options,1,$this->maxSelect);
		}
		foreach ($options as $item) {
			if (is_array($item)) {
				$params['user_list'][] = ['openid' => $item['openId'],'lang' => isset($item['lang']) ? $item['lang'] : 'zh_CN'];
			} else {
				$params['user_list'][] = ['openid' => $item,'lang' => 'zh_CN'];
			}
		}
		$params = urldecode(json_encode($params,JSON_UNESCAPED_UNICODE));
		$url    = $this->apiUrl . 'cgi-bin/user/info/batchget?access_token=' . $this->accessToken;
		$result = HttpRequest::post($url,$params)->jsonToArray()->read();
		return $this->getData($result);
	}
	/**
	 * 修改用户备注
	 * @param  string $openId 
	 * @param  string $remark 
	 * @return array        
	 */
	public function remark ($openId,$remark = '')
	{
		$url 	= $this->apiUrl . 'cgi-bin/user/info/updateremark?access_token=' . $this->accessToken;
		$params = urldecode(json_encode(['openid' => $openId,'remark' => $remark],JSON_UNESCAPED_UNICODE));
		$result = HttpRequest::post($url,$params)->jsonToArray()->read();
		return $this->getData($result);
	}
	/**
	 * 获取黑名单列表
	 * @param  string $beginOpenid 
	 * @return array        
	 */
	public function blacklist ($beginOpenid = '')
	{
		$url 	= $this->apiUrl . 'cgi-bin/tags/members/getblacklist?access_token=' . $this->accessToken;
		$params = urldecode(json_encode(['begin_openid' => $beginOpenid],JSON_UNESCAPED_UNICODE));
		$result = HttpRequest::post($url,$params)->jsonToArray()->read();
		return $this->getData($result);
	}
	/**
	 * 拉黑用户
	 * @param  array $opendIds 
	 * @return array     
	 */
	public function black ($openIds)
	{
		$url 	= $this->apiUrl . 'cgi-bin/tags/members/batchblacklist?access_token=' . $this->accessToken;
		$params['openid_list'] = $openIds;
		$result = HttpRequest::post($url,urldecode(json_encode($params,JSON_UNESCAPED_UNICODE)))->jsonToArray()->read();
		return $this->getData($result); 
	}
	/**
	 * 取消拉黑用户
	 * @param  array $opendIds 
	 * @return array     
	 */
	public function unblack ($openIds)
	{
		$url 	= $this->apiUrl . 'cgi-bin/tags/members/batchunblacklist?access_token=' . $this->accessToken;
		$params['openid_list'] = $openIds;
		$result = HttpRequest::post($url,urldecode(json_encode($params,JSON_UNESCAPED_UNICODE)))->jsonToArray()->read();
		return $this->getData($result); 
	}
}