<?php
require_once('lib/quickadm/quickadm_cfg.php');
require_once("lib/quickadm/quickadm_db.php");
 
defined('DS')				|| define('DS', DIRECTORY_SEPARATOR);

defined('FRAME_ROOT_DIR')	|| define('FRAME_ROOT_DIR', dirname(dirname(__FILE__).DS).DS);
defined('FRAME_SMARTY_DIR') || define('FRAME_SMARTY_DIR', FRAME_ROOT_DIR.'framework'.DS.'lib'.DS.'Smarty'.DS);

$str = $_SERVER['PHP_SELF'];
$arr = explode('/',$str);
$arr = array_reverse($arr);
$ROOTURL = '/' . $arr['1'] . '/';

defined('ROOTURL')				|| define('ROOTURL', $ROOTURL);
//echo $ROOTURL;
//echo $str;

session_start();

class corlyframe{
     
     private $controlerFile;
     private $controlerClass;
	 
	 private $app;
	 private $module;
	 private $action;

     private $ishtml;
     public function corlyframe()
     {}
     public function parse()
     {
        // $_params = explode('/',$_SERVER["REQUEST_URI"]);
        $this->_parsePath();
        $this->_getControlerFile();
        $this->_getControlerClassname();
     }

     public function _parsePath()
     {
		 if(isset($_REQUEST["module"]))
		 {
		    $this->module = $_REQUEST["module"];
		 }
		 else
		 {
		    $this->module = "index";
		 }
		 

		 if(isset($_REQUEST["action"]))
		 {
		    $this->action = $_REQUEST["action"];
		 }
		 else
		 {
		    $this->action = "index";
		 }
		 

		 
     }

     public function _getControlerFile()
	 {
        $this->controlerFile = "controler/". $this->module."_controler.php";
        if(!file_exists($this->controlerFile))
        die("Controler文件名(".$this->controlerFile.")解析错误");
        require_once $this->controlerFile;
     }

     public function _getControlerClassname()
	 {
         $this->controlerClass = $this->module."_controler";
         if(!class_exists($this->controlerClass))
         die("Controler类名(".$this->controlerClass.")解析错误");
     }

     public function go()
	 {
         $c = new $this->controlerClass();
		 //$c->setappname($this->app);
         if(!method_exists($c, $this->action))
		 die("Controler方法名(".$this->controlerClass."::". $this->action. ")解析错误");
		 call_user_func(array($c, $this->action));
     }
}
	function getdb()
	{
		if( empty($host) ) $dbhost = $GLOBALS['datagrid']['db']['host'];
		if( empty($user) ) $dbuser = $GLOBALS['datagrid']['db']['user'];
		if( empty($password) ) $dbpassword = $GLOBALS['datagrid']['db']['passwd'];
		if( empty($database) ) $database = $GLOBALS['datagrid']['db']['dbname'];
		if( empty($charset) ) $charset = $GLOBALS['datagrid']['db']['charset'];
		
	   if(!class_exists("PDO"))
	   {
         return new MysqlDB('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
       }
	   else
	   {
         return new MysqlPDO('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
       }
	}
abstract class controller
{
    private $app;
	public $db;
	public function __construct()
	{
		$this -> db = getdb();
	}


	public function setappname($appname)
	{
	   $this->app=$appname;
	}
	public function display($tpl, $params=null)
	{
		try
		{
			$engine = View::getEngine($this->app);
			if(!empty($params))
			{
				$params['ROOTURL'] = ROOTURL;
				$engine->assign($params);
			}
			$output = $engine->fetch($tpl);
			echo $output;
			return $this;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
	}
}

final class View{
	public static function getEngine($app){
		static $smarty = NULL;
		if(NULL === $smarty)
		{
			$fileSmarty = FRAME_SMARTY_DIR.'SmartyBC.class.php';
			if(file_exists($fileSmarty))
			{
				require_once $fileSmarty;
				$smarty					=	new SmartyBC();
				$smarty->compile_check	=	true;
				$smarty->caching		=	0;
				$smarty->compile_dir	=	FRAME_ROOT_DIR.'cache';
				$smarty->template_dir	=	FRAME_ROOT_DIR.DS.'tpl';
				$smarty->plugins_dir	=	array(FRAME_SMARTY_DIR.'plugins', FRAME_ROOT_DIR.'framework/lib/Core/plugins');
				//var_dump($smarty);exit();
				return $smarty;
			}
			else
			{
				
			}
		}
	}
}
?> 
