<?php
namespace WeChat\Lib;
use WeChat\Exception\ErrorException;
use WeChat\Application;
use WeChat\Util\Error;
use WeChat\Util\HttpRequest;

class TemplateMessage extends Application
{
	use Error;

	/**
	 * 发送模板消息
	 * @param  string $templateId 
	 * @param  string $toUser      
	 * @param  array $data       
	 * @param  string url       
	 * @return mixed
	 */
	public function send ($templateId,$toUser,$data = [],$url = '')
	{
		$params = [];

		$params['touser']      = $toUser ;
		$params['template_id'] = $templateId;
		$params['url']         = $url;
		$params['data']        = $data;
		$result = $this->getData(HttpRequest::post($this->apiUrl . 'cgi-bin/message/template/send?access_token=' . $this->getAccessToken(),json_encode($params))->jsonToArray()->read());
		return $result === true ? true : $result['errmsg'];
	}
} 