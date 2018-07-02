<?php
namespace WeChat;
use WeChat\Util\Log;
use WeChat\Util\HttpRequest;
use WeChat\Exception\ErrorException;
use WeChat\Exception\ClassNotException;
use WeChat\Util\Xml;

class Application
{
	/**
	 * 应用配置
	 * @var array
	 */
	protected static $config = [];

	/**
	 * 接收到的数据
	 * @var mixed
	 */
	public $data;

	/**
	 * 日志存储对象
	 * @var object
	 */
	protected $log;

	/**
	 * accessToken
	 * @var string
	 */
	protected $accessToken;

	/**
	 * apiUrl
	 * @var string
	 */
	protected $apiUrl 	= 'https://api.weixin.qq.com/';

	/**
	 * class类集合
	 * @var array
	 */
	protected $classMap = [];

	/**
	 * 构造初始化
	 * @access public
	 * @param array $config 
	 * @return void
	 */
	public function __construct ($config = [])
	{
		if (empty(self::$config))
		{
			// 获取默认配置
			$defaultCfg   = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
			// 赋值配置
			self::$config = empty($config) ? $defaultCfg : array_merge($defaultCfg,$config); 
		}
		// 构造日志存储对象
		$this->log = new Log(self::$config['log']);
		// 获取解析后的请求数据
		$this->data = $this->getRequestData();
		// 获取accessToken
		$this->accessToken = $this->getAccessToken();
	}
	/**
	 * 获取微信返回的数据
	 * @access protected
	 * @return object
	 */
	protected function getRequestData ()
	{
		$data = file_get_contents('php://input');
		$xml = new Xml();
		if (!empty($data) && $xml->isXml($data) && substr($data,0,1) == '<') {
			return simplexml_load_string($data,'SimpleXMLElement',LIBXML_NOCDATA);
		}
		return [];
	}
	/**
	 * 配置处理
	 * @access public
	 * @param  string $name  
	 * @param  string $value 
	 * @return string
	 */
	public function config ($name = '',$value = '')
	{
		if (empty($name)) {
			return self::$config;
		}
		if (is_array($name)) {
			self::$config = array_merge(self::$config,$name);
		} elseif (!empty($name) && !empty($value)) {
			self::$config[$name] = $value;
		} else {
			return self::$config[$name];
		}
	}
	// 实例化获取对象
	public function __get ($name)
	{
		return $this->instance($name);
	}
	/**
	 * 实例化获取对象
	 * @access public
	 * @param  string $name 
	 * @return object 
	 */
	public function instance ($name)
	{
		if (isset($this->classMap[$name]) && is_object($this->classMap[$name])) {
			return $this->classMap[$name];
		}
		$class = "\\WeChat\\Lib\\" . ucfirst($name);
		if (!class_exists($class)) {
			throw new ClassNotException($name . ' class not exists!',$class);
		}
		$this->classMap[$name] = new $class();
		return $this->classMap[$name];
	}
	/**
	 * 获取access_token
	 * @param  boolean $cache 
	 * @return string  
	 */
	public function getAccessToken ($cache = true)
	{
		$cacheFileName = md5(self::$config['app_id'] . self::$config['app_secret']);
		$cacheFile     = __DIR__ . '/Cache/' . $cacheFileName . '.php';
		if ($cache === true && is_file($cacheFile) && unserialize(file_get_contents($cacheFile))['create_time'] + 7000 > time()) {
			// 缓存有效,直接获取缓存内容
			$content = file_get_contents($cacheFile);
			$data 	 = unserialize($content)['data'];
		} else {
			$data = HttpRequest::get($this->apiUrl . 'cgi-bin/token',[
				'grant_type' => 'client_credential','appid' => self::$config['app_id'],'secret' => self::$config['app_secret']
			])->jsonToArray()->read();
			// 获取失败返回
			if (isset($data['errcode'])) {
				$this->log->write(['message' => 'access_token 获取失败','data' => $data]);
				throw new ErrorException("基础支持access_token获取失败");
			}
			// 缓存access_token
			$cachePath 	  = dirname($cacheFile);
			is_dir($cachePath) || mkdir($cachePath,0755,true);
			$cacheContent = serialize(['data' => $data,'create_time' => time()]);
			file_put_contents($cacheFile,$cacheContent);
		}
		return $data['access_token'];
	}
}