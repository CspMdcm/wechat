<?php
namespace WeChat\Lib\Message;
use WeChat\Lib\Message;
use WeChat\Util\Xml;
class Image extends Message
{	
	/**
	 * 通过素材管理中的接口上传多媒体文件，得到的id。
	 * @var integer
	 */
	public $mediaId;

	/**
	 * 构造方法初始化
	 * @access public
	 * @param string $mediaId 
	 * @return void
	 */
	public function __construct ($mediaId = '')
	{
		parent::__construct();
		$this->mediaId = $mediaId;
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
			'MsgType'	   => self::REPLY_TYPE_IMAGE,
			'Image'	   	   => [
				'MediaId'  => $this->mediaId
			]
		]);
		return $xml->toXml();
	}
}