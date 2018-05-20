<?php
namespace WeChat\Lib;
use WeChat\Application;
use WeChat\Exception\ErrorException;

class Message extends Application
{	
	/**
	 * 请求文本消息
	 * @var string
	 */
	const MSG_TYPE_TEXT   = 'text';
	/**
	 * 请求图片消息
	 * @var string
	 */
	const MSG_TYPE_IMAGE  = 'image';
	/**
	 * 请求语音消息
	 * @var string
	 */
	const MSG_TYPE_VOICE  = 'voice';
	/**
	 * 请求视频消息
	 * @var string
	 */
	const MSG_TYPE_VIDEO  = 'video';
	/**
	 * 请求小视频消息
	 * @var string
	 */
	const MSG_TYPE_SMALL_VIDEO = 'shortvideo';
	/**
	 * 请求地理位置消息
	 * @var string
	 */
	const MSG_TYPE_LOCATION = 'location';
	/**
	 * 请求链接消息
	 * @var string
	 */
	const MSG_TYPE_LINK    = 'link';
	/**
	 * 回复文本消息
	 * @var string
	 */
	const REPLY_TYPE_TEXT  = 'text';
	/**
	 * 回复图片消息
	 * @var string
	 */
	const REPLY_TYPE_IMAGE = 'image';
	/**
	 * 回复语音消息
	 * @var string
	 */
	const REPLY_TYPE_VOICE = 'voice';
	/**
	 * 回复视频消息
	 * @var string
	 */
	const REPLY_TYPE_VIDEO = 'video';
	/**
	 * 回复音乐消息
	 * @var string
	 */
	const REPLY_TYPE_MUSIC = 'music';
	/**
	 * 回复图文消息
	 * @var string
	 */
	const REPLY_TYPE_NEWS  = 'news';

	/**
	 * 输出类型
	 * @var string
	 */
	public $contentType    = 'application/xml';

	/**
	 * 消息处理
	 * @param  object $handle 
	 * @return mixed 
	 */
	public function pushMessage ($handle)
	{
		if ($handle instanceof \Closure && isset($this->data) && isset($this->data->MsgType)) {
			return $handle($this->data->MsgType);
		}
		return $this;
	}

	/**
	 * 发送数据
	 * @return void
	 */
	public function send ()
	{
		header('Content-type:' . $this->contentType);
		echo $this->toXml();
	}
	/**
	 * 转换成xml
	 * @return void
	 */
	public function toXml ()
	{
		throw new ErrorException("data not exists!");
	}
	/**
	 * 设置对象属性
	 * @param string $name  
	 * @param string $value 
	 * @return object
	 */
	public function setAttribute ($name,$value = '')
	{
		 if (is_array($name)) {
		 	 foreach ($name as $k => $V) {
		 	 	$this->$k = $v;
		 	 }
		 } else {
		 	$this->$name = $value;
		 }
		 return $this;
	}
	/**
	 * push send data
	 * @param  array $sendData 
	 * @return array           
	 */
	public function pushSendData ($sendData = [])
	{
		 $data = [];
		 foreach ($sendData as $k =>$v) {
		 	 if (!empty($this->$k)) {
		 	 	 $data[$v] = $this->$k;
		 	 }
		 }
		 return $data;
	}
	/**
	 * 对象输出
	 * @access public
	 * @return null
	 */
	public function __tostring ()
	{
		header('Content-type:' . $this->contentType);
		return $this->toXml();
	}
}