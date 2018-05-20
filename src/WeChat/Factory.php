<?php
namespace WeChat;
class Factory
{	
	protected static $classMap = [];

	/**
	 * 获取Application对象
	 * @param  array $config 
	 * @param  bool $signature
	 * @return object  
	 */
	public static function get ($config = [],$signature = true)
	{
		if (isset(self::$classMap['app']) && is_object(self::$classMap['app'])) {
			return self::$classMap['app'];
		}
		if ($signature) {
			self::$classMap['app'] = new Application($config);
			return self::$classMap['app'];
		}
		return new Application($config);
	}
}