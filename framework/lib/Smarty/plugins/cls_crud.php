<?php
/**
 * 自动化数据表操作类
 * @example
 * <code>
 * $db  = cls_crud::factory(array('table'=>'article'), array(), true);
 * $data = $db->get_block_list(array('category_id' => 3), 9);
 * //var_dump($data['data']);
 *
 * $data = $db->get_list(array('title', 'id', 'time'), array('category_id'=>$cid), 'time', 1, $_GET['page'], 2, 1);
 * //var_dump($data['data']); var_dump($data['page']);
 *
 * </code>
 * @author 小蔡	<cyy0523xc@gmail.com>
 * @version 1.2
 */
include 'cls_page.php';

class cls_crud
{
    /**
     * 类对象（通常只会被实例化一次）
     * @var object
     */
    private static $_instance = null;

    /**
     * 数据表名
     * @var string
     */
    private $table = '';

    /**
     * 可以join多个表
     * @var array
     */
    private $join_table = array();

    /**
     * 主键名
     * @var string
     */
    private $primary = 'id';

    private $link;
    private $query_id;


    /**
     * 分页对象
     * @var array
     */
    public $page = null;

    /**
     * 工厂函数
     * @param array $config		配置变量（最基本要配置数据表名）
     * @param array $database	数据库链接参数
     * @param bool  $is_show_page	是否需要显示分页链接
     * @param bool  $is_single		是否单例模式（非单例模式会强制实例化为一个新对象）
     */
    public function cls_crud($config, $database=array(), $is_show_page = false, $is_single = true) 
        {
            $this->connect($database);
            $this->page = new cls_page();
            $this->config($config);
        }
    public static function factory($config, $database=array(), $is_show_page = false, $is_single = true)
    {
        if ($is_single)
        {
            //单例模式
            if (null === self::$_instance)
            {
                self::$_instance = new self();
                self::$_instance->connect($database);
                self::$_instance->page = new cls_page();
            }

            self::$_instance->config($config);
            return self::$_instance;
        }
        else
        {
            //强制实例化成一个新的对象
            $ins = new self();
            $ins->connect($database);
            $ins->page = new cls_page();
            $ins->config($config);

            return $ins;
        }
    }

    /**
     * 设置数据表(例如：在操作的过程中需要改变数据表，就可以使用此方法)
     * @param string $table
     */
    public function set_table($table)
    {
        $this->table = $table;
    }

	/**
     * 连接数据库+选择数据库
     * @param array $database 配置变量（db_host, db_user, db_pass, db_charset, db_name）
     * @return void
     */
	private function connect($database = array())
	{
		if(empty($database))
		{
		    //如果配置变量为空，则直接读取配置文件
            include 'framework/lib/quickadm/quickadm_cfg.php';
            $database = $GLOBALS['datagrid']['db'];
		}

		try
		{
			$this->link = mysql_connect($database['host'],$database['user'],$database['passwd']);
			if($this->server_info() > '5.0')
			{
				mysql_query("SET sql_mode=''",$this->link);
				$charset = str_replace('-', '',strtolower($database['charset']));
				mysql_query("SET character_set_connection=".$charset.", character_set_results=".$charset.", character_set_client=binary",$this->link);
			}
			elseif($this->server_info() > '4.1')
			{
				$charset = str_replace('-', '',strtolower($database['charset']));
				mysql_query("SET character_set_connection=".$charset.", character_set_results=".$charset.", character_set_client=binary",$this->link);
			}

			mysql_select_db($database['dbname'],$this->link);
		}
		catch (cls_dbexception $e)
		{
			die($e->getError());
		}
	}

	/**
	 * 向数据库查询sql语句
	 *
	 * @deprecated INSERT UPDATE DELETE
	 * @param  string $sql
	 * @return bool
	 */
	public function query($sql)
	{
		try
		{
			$this->query_id = mysql_query($sql,$this->link);
			return $this->query_id;
		}
		catch (cls_dbexception $e)
		{
			die($e->getError());
		}
	}

    /**
     * 读取一条记录
     * @param string $id		主键
     * @param string $fields	获取字段
     * @return array
     */
    public function read($id, $fields='*')
    {
        $sql = "SELECT {$fields} FROM `{$this->table}` WHERE `{$this->primary}`='{$id}'";
        $this->query($sql);
        return $this->fetch_one();
    }

    /**
     * 插入一条记录
     * @param array $array	数组
     * @return bool
     */
    public function insert($array)
    {
        $fields = array();
        $values = array();
        foreach($array as $f => $v)
        {
            $fields[] = "`{$f}`";
            $values[] = "'".mysql_real_escape_string($v)."'";
        }
        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT INTO `{$this->table}`({$fields}) VALUES({$values})";
        return $this->query($sql);
    }

    /**
     * 更新一条记录
     * @param int   $id		主键
     * @param array $array	数据数组
     */
    public function update($id, $array)
    {
       $values = array();
        foreach($array as $f => $v)
        {
            $values[] = "`{$f}`='".mysql_real_escape_string($v)."'";
        }
        $values = implode(',', $values);

        $sql = "UPDATE `{$this->table}` SET {$values} WHERE `{$this->primary}`='{$id}' LIMIT 1";
        return $this->query($sql);
    }

    /**
     * 删除一条记录
     * @param int $id	主键
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primary}`='{$id}' LIMIT 1";
        return $this->query($sql);
    }

    /**
     * 增加left join数据（根据主键关联）
     * <code>
     * 参数：$wheres = array('id'=>23, 'NOT(`name` IS NULL)')
     * ==>   `id`='23' AND NOT(`name` IS NULL)
     * </code>
     * @param string		$table
     * @param string 		$join_field
     * @param string|array 	$fields
     * @param array 		$wheres
     */
    public function add_join_table($table, $join_field = '', $fields='*', $wheres=array())
    {
        $this->join_table[$table] = array(
            'fields' => '*'==$fields && '`'.implode('`,`', $fields) . '`',
            'join_field' => ($join_field or $this->primary),
            'where' => $this->set_where($wheres),
        );
    }

    /**
     * 获取分页列表的数据
     * @example
     * <code>
     * 参数：$wheres = array('id'=>23, 'NOT(`name` IS NULL)')
     * ==>   `id`='23' AND NOT(`name` IS NULL)
     * </code>
     * @param string|array	$fields	需要读取的字段（注：如果是字符串，则需要对字段名加上“``”标识）
     * @param array  $wheres	where条件数组，如果下标是数字，则直接加入条件，否则组合成:`{下标}`='{值}'这样的条件。最后用and链接
     * @param string $order		排序字段
     * @param int    $desc		是否是降序
     * @param int    $offset	偏移量
     * @param int    $limit		读取记录数
     * @param int    $return_total	是否返回满足条件的记录总数，默认为0，需要显示分页时可以设置为1.(如果需要获取分页代码，此值必须为1)
     * @return array
     */
    public function get_list($fields="*", $wheres=array(), $order='', $desc=1, $page=1, $limit=20, $return_total=0)
    {
        //处理需要读取的字段
        if(is_array($fields) && !empty($fields))
        {
            $fields = '`'.implode('`,`', $fields) . '`';
        }

        //处理where条件
        $where = $this->set_where($wheres);

        //处理orderby
        $orderby = '';
        if(!empty($order))
        {
            $orderby = "ORDER BY `{$order}` " . (1===$desc ? 'DESC' : 'ASC');
            $this->primary!=$order && $orderby .= ", `{$this->primary}` DESC";
        }

        $data = array();
        //读取数据
        $this->page->set_page($page, $limit);
        $sql="";
        if( $where=='' )
        { 
            $sql = "SELECT {$fields} FROM `{$this->table}` {$orderby} LIMIT {$this->page->offset}, {$this->page->limit}";
        }
        else
         {
            $sql = "SELECT {$fields} FROM `{$this->table}` WHERE {$where} {$orderby} LIMIT {$this->page->offset}, {$this->page->limit}";          
         }
		 $sql = strtolower($sql);
        $this->query($sql);
		
        //var_dump( $sql );
       // var_dump( $where );
        $data['data'] = $this->fetch_all();

        if($return_total)
        {
            //返回记录总数（分页）
            $sql = "SELECT count(*) FROM `{$this->table}` WHERE {$where}";
            $this->query($sql);
            $total = $this->fetch_one();
            $this->page->set_total(current($total));

            $data['page'] = $this->page->get_html();
        }

        //left join数据
        if(!empty($data['data']) && !empty($this->join_table))
        {
            //获取数据中的id列表
            $id_list = array();
            foreach ($data['data'] as $rec)
            {
                $id_list[] = $rec[$this->primary];
            }
            $id_list = "('".implode("','", $id_list)."')";
            $count = count($data);

            //获取left join表的数据
            foreach ($this->join_table as $table => $params)
            {
                $_data = array();
                $params['where'] = "`{$params['join_field']}` IN{$id_list}";
                $sql = "SELECT {$params['fields']} FROM `{$params['table']}` WHERE {$params['where']}";
                $this->query($sql);
                $_data = $this->fetch_all();

                //合并数据
                if ($_data)
                {
                    //转化为以链接键为下标的数组
                    $_data2 = array();
                    foreach ($_data as $rec)
                    {
                        $_data2[$params['join_field']] = $rec;
                    }

                    foreach ($data['data'] as $key=>$val)
                    {
                        //根据主键关联：$val[$this->primary]  =》 当前记录的主键值
                        if (isset($_data2[$val[$this->primary]]))
                        {
                            $data['data'][$key] = array_merge($val, $_data2[$val[$this->primary]]);
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 获取一个块的数据（不需要分页）
     * @param array 		$wheres	查询条件
     * @param int 			$limit	结果条数
     * @param string|array 	$fields	查询字段
     * @param string 		$order	排序字段（默认为id倒序）
     * @param int   		$desc	是否是降序
     */
    public function get_block_list($wheres, $limit, $fields="*", $order='', $desc = 1)
    {
        empty($order) && $order = $this->primary;
        $data = $this->get_list($fields, $wheres, $order, $desc, 0, $limit);
        return $data[ "data" ];
    }

	/**
	 * 返回单条记录数据
	 * @deprecated   MYSQL_ASSOC==1 MYSQL_NUM==2 MYSQL_BOTH==3
	 * @param  int   $result_type
	 * @return array
	 */
	public function fetch_one($result_type = 1)
	{
		return mysql_fetch_array($this->query_id,MYSQL_ASSOC);
	}

	/**
	 * 返回多条记录数据..
	 * @deprecated    MYSQL_ASSOC==1 MYSQL_NUM==2 MYSQL_BOTH==3
	 * @param   int   $result_type
	 * @return  array
	 */
	public function fetch_all($result_type = MYSQL_ASSOC)
	{
		$row_array = array();
		while($row = mysql_fetch_array($this->query_id,MYSQL_ASSOC))
		{
			$row_array[] = $row;
		}

		return $row_array;
	}

/****************  以下函数为私有  **********************/

    /**
     * 私有构造函数（单例模式）
     * @param
     */
      /**
     * 配置
     * @param array $config		配置变量
     */
    private function config($config)
    {
        if(!empty($config))
        {
            foreach($config as $cf => $val)
            {
                $this->$cf = $val;
            }
        }
    }

	/**
     * 返回数据库版本
     * @return string
     */
	private function server_info()
	{
		return mysql_get_server_info();
	}

	/**
	 * 处理where条件
	 * @param array|int $wheres
	 * @param string	$search_key	搜索字段
	 * @return string
	 */
	private function set_where($wheres = array())
	{
        if(!empty($wheres) && is_array($wheres))
        {
            $where = array();
            foreach($wheres as $f => $v)
            {
                if(is_numeric($f))
                {
                    $where[] = $v;
                }
                else
                {
                    $where[] = "`{$f}`='".mysql_real_escape_string($v)."'";
                }
            }
            return implode(' AND ', $where);
        }
        elseif(is_int($wheres))
        {
            return "`{$this->primary}`='{$wheres}'";
        }
        else
        {
            return '1';
        }
	}

}
