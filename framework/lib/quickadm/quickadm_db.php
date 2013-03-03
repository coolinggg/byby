<?php
/**
 * Phpgrid
 *
 * Copyright (c) 2008 - 2012 Phpgrid
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   Phpgrid
 * @package    Phpgrid
 * @copyright  Copyright (c) 2006 - 2012 Phpgrid (http://phpgrid.sf.net/)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    3.0.0, 2009-09-27
 */

/**
 * pdo custom class extends pdo
 * @author phpgrid2@gmail.com
 *
 */
class MysqlPDO extends PDO
{
	
	/**
	 * pdo object
	 * @var object
	 */
	public $database;	

	/**
	 * query of id
	 * @var string
	 */
	public $queryId;	

	/**
	 * 
	 * @var string
	 */
	public $sqlErr;    

	/**
	 * last query of sql
	 * @var string
	 */
	public $lastSql;			    

	/**
	 * 构造函数，直接连接数据库
	 * @todo  在需要的时候再进行数据库连接
	 * @param string $type   数据库类型
	 * @param string $host   数据库主机
	 * @param string $user   用户名
	 * @param string $pass   密码
	 * @param string $dbname 数据库名称
	 * @param string $charset 字符集
	 * @param string $port   数据库端口
	 */
	public function MysqlPDO($type, $host, $user, $pass, $dbname,$charset = 'utf8', $port = '3306')
	{
		$dsn = sprintf("%s:host=%s;port=%s;dbname=%s", $type, $host,$port, $dbname);
		try{
			PDO::__construct($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$charset}';"));
			$this->database = $dbname ;
		} catch(PDOException $e) {
			die('Can not connect to MySQL server');
		}
	}
	
	
	/**
	 * $query_type = 1 返回影响记录数量；2，返回查询数组,3,返回单条数据 
	 * @see PDO::query()
	 */
	public function query($sql,$query_type = 1)
	{
	    $sql = strtolower($sql);
		if( $sql == false ) return false;
		try{
			$RS = PDO::query($sql);
			if (PDO::errorCode() != '00000'){
				$this->lastSql = $sql;
				$err = PDO::errorInfo();
				echo "<b>$sql</b><br>";
				print_r($err);die;
			}
			$RS->setFetchMode(PDO::FETCH_ASSOC);
			$this->last_sql = $sql;
			switch ($query_type){
				case 1:
					return $RS->rowCount();            //返回影响的记录数量
				case 2:
					return $RS->fetchall();		       //查询数据并形成数组
				case 3:
					return $RS->fetchColumn();	       //单列数据记录返回 
				case 4:
					return $RS->fetch();               //单条记录返回    
				default:
					return $RS->rowCount();
			}
		}catch(PDOException $e){
			die('数据库操作异常错误:'.$e);
		}
	}
	
	/**
	 * 返回第一条记录第一个字段
	 * @param sting $sql
	 * @return array
	 */
	public function getOne($sql) {
	    $sql = strtolower($sql);
		return $this->query($sql,3);
	}
	
	/**
	 * 取之返回第一条数据 组成的数组
	 * @param string $sql 要执行的sql语句
	 * @return array
	 */
	public function getRow($sql) //注意必须为单行数据
	{
	    $sql = strtolower($sql);
		return $this->query($sql,4);
	}
	
	/**
	 * 返回所有数据
	 * @param string $sql 要执行的sql语句
	 * @return array
	 */
	public function getAll($sql)
	{
	    $sql = strtolower($sql);
		return $this->query($sql,2);
	}

	/**
	 * 取得上一步 INSERT 操作产生的 ID
	 */
	public function insertId()
	{
	  
		return PDO::lastInsertId();
	}

}

/**
 * mysql libary for no pdo 
 * @author phpgrid2@gmail.com
 *
 */
class MysqlDB {
	
	/**
	 * handlder of mysql resouce
	 * @var resource
	 */
	public $link = null;
	
	/**
	 * 
	 * @todo  在需要的时候再进行数据库连接
	 * @param string $type   数据库类型
	 * @param string $host   数据库主机
	 * @param string $user   用户名
	 * @param string $pass   密码
	 * @param string $dbname 数据库名称
	 * @param string $charset 字符集
	 * @param string $port   数据库端口
	 */
	public function MysqlDB($type, $host, $user, $pass, $dbname,$charset = 'utf8', $port = '3306')
	{
		$link = mysql_connect($host.":".$port,$user,$pass);
		if ( !$link )  die('Could not connect: ' . mysql_error());
		if( !mysql_select_db($dbname,$link) ) die("Could not select db: ".mysql_error());
		mysql_query( "SET NAMES '{$charset}';", $link );
		$this->link = $link;

	}
	
	/**
	 * $query_type = 1 返回影响记录数量；2，返回查询数组,3,返回单条数据 
	 * @see PDO::query()
	 */
	public function query($sql)
	{
	    $sql = strtolower($sql);
		return mysql_query($sql,$this->link);
	}
	
	/**
	 * 返回第一条记录第一个字段
	 * @param sting $sql
	 * @return array
	 */
	public function getOne($sql) {
	    $sql = strtolower($sql);
		$res=$this->query($sql);
		$row=mysql_fetch_array($res,MYSQL_NUM);
		return $row[0];
	}
	
	/**
	 * 取之返回第一条数据 组成的数组
	 * @param string $sql 要执行的sql语句
	 * @return array
	 */
	public function getRow($sql) //注意必须为单行数据
	{   $sql = strtolower($sql);
		$res=$this->query($sql);
		$row=mysql_fetch_array($res,MYSQL_ASSOC);
		return $row;
	}

	/**
	 * 返回所有数据
	 * @param string $sql 要执行的sql语句
	 * @return array
	 */
	public function getAll($sql)
	{
	    $sql = strtolower($sql);
		$array=array();
		$res=$this->query($sql);
		while($row=mysql_fetch_array($res,MYSQL_ASSOC)){
			$array[]=$row;
		}
		return $array;
	}

	/**
	 * 取得上一步 INSERT 操作产生的 ID
	 */
	public function insertId()
	{
		return mysql_insert_id($this->link);
	}
}





?>