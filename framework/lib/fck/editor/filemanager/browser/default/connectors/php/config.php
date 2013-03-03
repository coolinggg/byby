<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the File Manager Connector for PHP.
 */

global $Config ;

// SECURITY: You must explicitelly enable this "connector". (Set it to "true").
//$Config['Enabled'] = false ;


// Path to user files relative to the document root.
//$Config['UserFilesPath'] = '/userfiles/' ;
define('MVMMALL', TRUE);
define('IN_ADMINCP', TRUE);
define('MVMMALL_ROOT', preg_replace('/include(.*)/i', '', str_replace('\\', '/', __FILE__)));
define('MVMMALL_CACHE', MVMMALL_ROOT.'data/cache/');
@ini_set('session.auto_start',0); //自动启动关闭
require_once MVMMALL_ROOT.'include/global.func.php';
//用户配置文件处理
require_once MVMMALL_ROOT.'config/config_db.php';
require_once MVMMALL_ROOT.'include/mysql_class.php';
$db = new dbmysql();
$db->dbconn($con_db_host,$con_db_id,$con_db_pass,$con_db_name);
//缓存类
require_once MVMMALL_ROOT.'include/cache.class.php';
$cache = new cache($db,$tablepre);        
//系统配置文件
$settings  = $cache->get_cache('cfg');
@extract($settings,EXTR_OVERWRITE);
unset($settings);
//执行session类
require_once MVMMALL_ROOT.'./include/session.class.php';
$sess_life = $session_lifetime ?  $session_lifetime:get_cfg_var('session.gc_maxlifetime');//SESSION的存活期
PHPVERSION()<'5'?mvm_session::handler():eval(mvm_session::handler());
session_start();
if ($_SESSION['user']['mvm_adminid']==1 || $_SESSION['mvm_user_fck']==1){
$Config['Enabled'] = true ;
}else {
  $Config['Enabled'] = false ;  
}
$root_path = preg_replace('/include(.*)/i', '', $_SERVER['PHP_SELF']);
// Path to user files relative to the document root.
$Config['UserFilesPath'] = $root_path . 'upload/';

// end by weberliu @ 2007-2-6

// Fill the following value it you prefer to specify the absolute path for the
// user files directory. Usefull if you are using a virtual directory, symbolic
// link or alias. Examples: 'C:\\MySite\\userfiles\\' or '/root/mysite/userfiles/'.
// Attention: The above 'UserFilesPath' must point to the same directory.
$Config['UserFilesAbsolutePath'] = '' ;

// Due to security issues with Apache modules, it is reccomended to leave the
// following setting enabled.
$Config['ForceSingleExtension'] = true ;

// by weberliu @  2007-3-29
//$Config['AllowedExtensions']['File']	= array() ;
//$Config['DeniedExtensions']['File']		= array('html','htm','php','php2','php3','php4','php5','phtml','pwml','inc','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','com','dll','vbs','js','reg','cgi','htaccess','asis') ;

$Config['AllowedExtensions']['File']	= array('zip','rar','txt','doc','xls','ppt','pdf') ;
$Config['DeniedExtensions']['File']		= array() ;

$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;

$Config['AllowedExtensions']['Media']	= array('swf','fla','jpg','gif','jpeg','png','avi','mpg','mpeg') ;
$Config['DeniedExtensions']['Media']	= array() ;

?>