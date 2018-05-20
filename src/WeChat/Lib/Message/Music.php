<?php
namespace WeChat\Lib\Message;
use WeChat\Lib\Message;
use WeChat\Util\Xml;
class Music extends Message
{
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
	 * 音乐链接
	 * @var string
	 */
	public $musicUrl;
	/**
	 * 高质量音乐链接，WIFI环境优先使用该链接播放音乐
	 * @var string
	 */
	public $hqMusicUrl;
	/**
	 * 缩略图的媒体id，通过素材管理中的接口上传多媒体文件，得到的id
	 * @var string
	 */
	public $thumbMediaId;

	/**
	 * 构造方法初始化
	 * @access public
	 * @param array $options
	 * @return void
	 */
	public function __construct ($options = [])
	{
		parent::__construct();

		$this->title        = isset($options['title']) ? $options['title'] : '';
		$this->description  = isset($options['description']) ? $options['description'] : '';
		$this->musicUrl     = isset($options['musicUrl']) ? $options['musicUrl'] : '';
		$this->hqMusicUrl   = isset($options['hqMusicUrl']) ? $options['hqMusicUrl'] : '';
		$this->thumbMediaId = isset($options['thumbMediaId']) ? $options['thumbMediaId'] : '';
	}
	/**
	 * 返回xml格式数据
	 * @return string
	 */
	public function toXml ()
	{
		$music = $this->pushSendData([
		 	'title' 	   => 'Title',
		 	'description'  => 'Description',
		 	'musicUrl'	   => 'MusicURL',
		 	'hqMusicUrl'   => 'HQMusicUrl',
		 	'thumbMediaId' => 'ThumbMediaId'
		 ]);
		$xml = new Xml([
			'ToUserName'   => $this->data->FromUserName,
			'FromUserName' => $this->data->ToUserName,
			'CreateTime'   => time(),
			'MsgType'	   => self::REPLY_TYPE_MUSIC,
			'Music'	   	   => $music
		]);
		return $xml->toXml();
	}
}