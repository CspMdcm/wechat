<?php
namespace WeChat\Exception;

class WeChatPayException extends Exception
{
	public function __construct ($message = 'wechat run error~')
	{
		parent::__construct($message);
	}
}