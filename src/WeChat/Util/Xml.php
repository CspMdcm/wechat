<?php
namespace WeChat\Util;

class Xml
{
	/**
	 * xml数据
	 * @var mixed
	 */
	protected $xmlData;
    
    /**
     * 构造方法初始化解析xml信息
     * @access public
     * @param mixed $xmlData
     * @return void 
     */
	public function __construct ($xmlData = '')
	{
		$this->data($xmlData);	
	}
	/**
	 * 获取xml节点数据
	 * @access public
	 * @param  string $name 
	 * @return string
	 */
	public function __get ($name)
	{
         if (!empty($name)) {
         	switch ($name) {
         		case is_array($this->xmlData):
         			return !empty($this->xmlData[$name]) ? $this->xmlData[$name] : '';
         			break;
         		case is_object($this->xmlData):
         			return !empty($this->xmlData->$name) ? $this->xmlData->$name : '';
         			break;
         	}
         }
	}

	/**
	 * 转换xml返回
	 * @access public
	 * @param mixed $xmlData 
	 * @param bool $headXml 
	 * @return string
	 */
	public function toXml ($xmlData = '',$headXml = true)
	{
		$xmlData = $xmlData ?: $this->xmlData;
		$xml = $headXml ? '<xml>' : '';
    	foreach ($xmlData as $key => $val) {
    		if (is_numeric($val) || $this->isXml($val)) {
    			$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
    		} elseif (is_object($val) || is_array($val)) {
    			 $xml .= "<". $key .">" . $this->toXml($val,false) . "</". $key .">";
    		} else {
    			$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
    		}
        }
        $xml .= $headXml ? '</xml>' : '';
        return $xml;
	}
	/**
	 * xml转换array
	 * @param string $xml
	 * @return array
	 */
	public function toArray ($xml = '')
	{
		if (!$this->isXml($xml) || substr($xml,0,1) != '<')
		{
			return [];
		}
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true); 
		return $data;
	}
	/**
	 * 对象输出
	 * @access public
	 * @return void
	 */
	public function __tostring ()
	{
		header('Content-type:application/xml');
		return $this->toXml();
	}
	/**
	 * 设置数据
	 * @param  mixed $xmlData 
	 * @return object      
	 */
	public function data ($xmlData = [])
	{
		switch (!empty($xmlData)) {
			case is_string($xmlData):
				$this->xmlData = $this->toArray($xmlData);
				break;
			case is_array($xmlData) || is_object($xmlData):
				$this->xmlData = $xmlData;
				break;
			default:
				$this->xmlData = new \stdClass();
				break;
		}
		return $this;
	}
	/**
	 * 判断是否为xml
	 * @param  string  $str 
	 * @return boolean      
	 */
	public function isXml ($str)
	{
		if (!is_string($str)) {
			return false;
		}
		$str = sprintf("<root>%s</root>",$str);
		$xmlParse = xml_parser_create();
		if (!xml_parse($xmlParse, $str,true)) {
			xml_parser_free($xmlParse);
			return false;
		}
		return !empty(json_decode(json_encode(simplexml_load_string($str)),true)); 
	}
}