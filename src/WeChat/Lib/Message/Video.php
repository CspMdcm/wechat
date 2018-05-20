<?php
namespace WeChat\Lib\Message;
use WeChat\Lib\Message;
use WeChat\Util\Xml;
class Video extends Message
{
	/**
	 * 通过素材管理中的接口上传多媒体文件，得到的id。
	 * @var integer
	 */
	public $mediaId;
	/**
	 * 标题
	 * @var string
	 */
	public $title;
	/**
	 * 描述
	 * @var string
	 */
	public $description;

	/**
	 * 构造方法初始化
	 * @access public
	 * @param string $mediaId 
	 * @param array $options 
	 * @return void
	 */
	public function __construct ($mediaId = '',$options = [])
	{
		parent::__construct();
		$this->mediaId = $mediaId;

		$this->title 	   = isset($options['title']) ? $options['title'] : '';
		$this->description = isset($options['description']) ? $options['description'] : '';
		
	}
	/**
	 * 返回xml格式数据
	 * @return string
	 */
	public function toXml ()
	{
		$video = $this->pushSendData([
			'mediaId' 	  => 'MediaId', 
			'title' 	  => 'Title', 
			'description' => 'Description', 
		]);
		$xml  = new Xml([
			'ToUserName'   => $this->data->FromUserName,
			'FromUserName' => $this->data->ToUserName,
			'CreateTime'   => time(),
			'MsgType'	   => self::REPLY_TYPE_VIDEO,
			'Video'	   	   => $video
		]);
		return $xml->toXml();
	}
}