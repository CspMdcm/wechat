<?php
namespace WeChat\Lib\Oauth;
use WeChat\Lib\User as UserInfo;
use WeChat\Util\Error;

class User implements \ArrayAccess
{
	use Error;
	/**
	 * user
	 * @var object
	 */
	protected $user;
	/**
	 * data
	 * @var array
	 */
	protected $data;

	/**
	 * 构造方法初始化
	 * @param array $data 
	 * @return void
	 */
	public function __construct ($data = [])
	{
		$this->data = $this->getData($data);
		if (isset($this->data['errcode']))
		{
			throw new \Exception("授权错误->" . $this->data['errmsg']);
		}
		$this->user = (new UserInfo())->get($this->data['openid']);
	}
	/**
	 * 返回openid
	 * @return string
	 */
	public function getId ()
	{
		return isset($this->user['openid']) ? $this->user['openid'] : '';
	}
	/**
	 * 用户昵称
	 * @return string
	 */
	public function getName ()
	{
		return isset($this->user['nickname']) ? $this->user['nickname'] : '';
	}
	/**
	 * 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
	 * @return integer
	 */
	public function getSex ()
	{
		return isset($this->user['sex']) ? $this->user['sex'] : '';
	}
	/**
	 * 用户个人资料填写的省份
	 * @return string
	 */
	public function getProvince ()
	{
		return isset($this->user['province']) ? $this->user['province'] : '';
	}
	/**
	 * 普通用户个人资料填写的城市
	 * @return string
	 */
	public function getCity ()
	{
		return isset($this->user['city']) ? $this->user['city'] : '';
	}
	/**
	 * 国家，如中国为CN
	 * @return string
	 */
	public function getCountry ()
	{
		return isset($this->user['country']) ? $this->user['country'] : '';
	}
	/**
	 * 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
	 * @return string
	 */
	public function getAvatar ()
	{
		return isset($this->user['headimgurl']) ? $this->user['headimgurl'] : '';
	}
	/**
	 * 返回user原始数据
	 * @return array
	 */
	public function getOriginal ()
	{
		return $this->user;
	}
	/**
	 * 返回access_token
	 * @return string
	 */
	public function getToken ()
	{
		return $this->data['access_token'];
	}
	/**
	 * 检查一个偏移位置是否存在
	 * @param  string $index 
	 * @return bool       
	 */
    public function offsetExists($index) {
        return isset($this->user[$index]);
    }
    /**
     * 获取一个偏移位置的值
     * @param  string $index 
     * @return string      
     */
    public function offsetGet($index) {
        return isset($this->user[$index]) ? $this->user[$index] : '';
    }
    /**
     * 设置一个偏移位置的值
     * @param  string $index    
     * @param  string $newvalue 
     * @return void           
     */
    public function offsetSet($index, $newvalue) {
        $this->user[$index] = $newvalue;
    }
    /**
     * 复位一个偏移位置的值
     * @param  string $index 
     * @return void
     */
    public function offsetUnset($index) {
        unset($this->user[$index]);
    }
}