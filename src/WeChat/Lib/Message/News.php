<?php
namespace WeChat\Lib\Message;
use WeChat\Lib\Message;
use WeChat\Util\Xml;

class News extends Message
{
	/**
	 * 单个图文
	 * @var array
	 */
	public $newItems = [];

	/**
	 * 最多发送条数
	 * @var integer
	 */
	public $maxCount = 8;

	/**
	 * 构造方法初始化
	 * @access public
	 * @param array $newItems 
	 * @return void
	 */
	public function __construct ($newItems = [])
	{
		parent::__construct();
		$this->newItems = $newItems;
	}
	/**
	 * 返回xml格式数据
	 * @return string
	 */
	public function toXml ()
	{
		$newsItem = $this->getNewItems();
		$xml = new Xml([
			'ToUserName'   => $this->data->FromUserName,
			'FromUserName' => $this->data->ToUserName,
			'CreateTime'   => time(),
			'MsgType'	   => self::REPLY_TYPE_NEWS,
			'ArticleCount' => $newsItem['count'],
			'Articles'	   => $newsItem['articles']
		]);
		return $xml->toXml();
	}
	/**
	 * 获取拼接单个图文xml
	 * @return array
	 */
	protected function getNewItems ()
	{
		$xml      = new Xml();
		$count 	  = 0;
		$articles = '';
		if (count($this->newItems) <= $this->maxCount) {
			$count = count($this->newItems);
		} else {
			$count = $this->maxCount;
			$this->newItems = $this->paged($this->newItems,1,$this->maxCount);
		}
		foreach ($this->newItems as $item) {
			$articles .= $xml->toXml([
				'item' => [
					'Title' 	  => $item['title'],
					'Description' => $item['description'],
					'PicUrl'	  => $item['picUrl'],
					'Url'   	  => $item['url'],
			    ]
			],false);
		}	
		return ['count' => $count,'articles' => $articles];
	}
}