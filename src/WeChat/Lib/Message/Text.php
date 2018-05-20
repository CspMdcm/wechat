<?php
namespace WeChat\Lib\Message;
use WeChat\Lib\Message;
use WeChat\Util\Xml;

class Text extends Message
{
	/**
	 * 内容
	 * @var string
	 */
	public $content;

	/**
	 * 构造方法初始化
	 * @access public
	 * @param string $content 
	 * @return void
	 */
	public function __construct ($content = '')
	{
		parent::__construct();
		$this->content = $content;
	}
	/**
	 * 返回xml格式数据
	 * @return string
	 */
	public function toXml ()
	{
		$xml = new Xml([
			'ToUserName'   => $this->data->FromUserName,
			'FromUserName' => $this->data->ToUserName,
			'CreateTime'   => time(),
			'MsgType'	   => self::REPLY_TYPE_TEXT,
			'Content'	   => $this->content
		]);
		return $xml->toXml();
	}
}