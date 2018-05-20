<?php
namespace WeChat\Exception;

class ErrorException extends Exception
{
	protected $errorCode;

	public function __construct ($message = '',$code = 400)
	{
		parent::__construct($message);
		$this->errorCode = $code;
	}
	/**
	 * 错误代码
	 * @return string
	 */
	public function getErrorCode ()
	{
		return $this->errorCode;
	}
	public function __toString ()
	{
		return sprintf("%s:[%s]: %s::%s",__CLASS__,$this->code,$this->message,$this->errorCode);
	}
}