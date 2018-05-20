<?php
namespace WeChat\Util;

class Log
{
	protected $config = [];
  
  /**
   * 构造方法初始化
   * @access public
   * @param array $config 
   * @return void
   */
	public function __construct ($config = [])
	{
     $this->config = $config;
	}
	/**
     * 写日志
     * @access public
     * @param  string $message   [错误信息]
     * @param  string $level     [消息级别]
     * @param  string $path [可自定义存放位置]
     * @return void
     */
    public function write ($message,$level = 'DEBUG',$path = '') {
       if (empty($message)) return ;
       $path = $path ?: $this->config['path'];
       $savePath = $this->isBack($path);
       $logMsg = '';
       switch (!empty($message)) {
         case is_array($message) || is_object($message):
           $logMsg = json_encode($message);
           break;
         default:
           $logMsg = $message;
           break;
       }
       $date = date('Y-m-d H:i:s',time());
       $content  = "[TIME:]{$date} {$level}:{$logMsg}\r\n";
       $fp  = fopen($savePath,'ab');
	     fwrite($fp,$content);
	     fclose($fp);
    }
    /**
     * 返回存放日志文件地址
     * @access private
     * @param  string  $path 
     * @return mixed       
     */
    private function isBack ($path = '') {
       $headerMessage = "--------------------------WeChat message record debugging--------------------------\r\n";
       $logDir = empty($path) ? dirname(__DIR__) . '/Logs' : $path; 
       is_dir($logDir) || mkdir($logDir,0755,true);

       $fileLogPath = $logDir . '/' . $this->config['save_name'] . '.' . trim($this->config['save_ext']);

       // 如果文件不存在创建返回
       if ( !is_file($fileLogPath) ) {
          file_put_contents($fileLogPath,$headerMessage);
          return $fileLogPath;
       }
       // 清除缓存
       clearstatcache(true);
       // 文件大小未超出限制
       if (filesize($fileLogPath) <= $this->config['save_size']) {
       	  return $fileLogPath;
       }
       // 文件大小超出限制需要备份
       $bak = $logDir . '/' . date('Y_m_d') . '.' . ltrim($this->config['bak_ext'],'.');
       $res = rename($fileLogPath,$bak);
       if ($res) {
       	  file_put_contents($fileLogPath,$headerMessage);
          return $fileLogPath;
       }
       return $fileLogPath;
    }
}
