<?php
 include('quickadm_cfg.php');
 require_once("quickadm_db.php");

class QuickGrid
{
     public $fieldDisplays		= array();
     public $queryurl = 'framework/lib/quickadm/quickadm_crud.php?q=1';
	 public $editurl  = 'framework/lib/quickadm/quickadm_crud.php?q=3';
     public $db = null;
	 public $title			="";
	 public $pageRow		= 10;
	 public $sql            = "";
     public $formPrimkey			= "";
	 public $result            = "";
	 
	 public $funcs = array();
	 public $initFuncs = array();
	 
	 private $varName ="";
	 private $RK="";
	 private $RKT="";
	 
	 private $fieldAttr = array();
	 
     public function QuickGrid($varname, $sql, $title, $formPrimkey)
    {
	     $this->varName =$varname;
	     $this -> sql = $sql;
		 $this -> title = $title;
		 $this -> formPrimkey = $formPrimkey;

         $this -> create_time = time();
        
		if( empty($host) ) $dbhost = $GLOBALS['datagrid']['db']['host'];
		if( empty($user) ) $dbuser = $GLOBALS['datagrid']['db']['user'];
		if( empty($password) ) $dbpassword = $GLOBALS['datagrid']['db']['passwd'];
		if( empty($database) ) $database = $GLOBALS['datagrid']['db']['dbname'];
		if( empty($charset) ) $charset = $GLOBALS['datagrid']['db']['charset'];
		
         if(!class_exists("PDO")){
             $this -> db = new MysqlDB('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
             }else{
             $this -> db = new MysqlPDO('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
             }
         }
		 public function setRK($RK,$RKT)
		 {
		   $this->RK = $RK;
		   $this->RKT = $RKT;
		 }
		 
		 public function setFieldAttr($fieldName, $attr)
		 {
		   if(!isset($this->fieldAttr[$fieldName]))
		   {
		       $this->fieldAttr[$fieldName] = array();
		   }
		   $this->fieldAttr[$fieldName][]=$attr;
		 }
     public function getVarName()
    {
	     return $this->varName;
         foreach ($GLOBALS as $key => $value){
		     echo $key;
			 echo "<br/>";
             if ($value instanceof QuickGrid){
                 if($value -> create_time == $this -> create_time){
                     return $key;
                     }
                 }
             }
        
         }
     public function display()
    {
         $this -> this_var_name = $this -> getVarName();
		 $this -> this_var_name_page = $this -> this_var_name . '_page';
         // echo $this->getVarName();
         $this->getFileds();
         
         require('quickadm_jqgrid.php');

         return $this->result;
    }
    public function addFunction($func)
	{
	   $this->funcs[] = $func;
	}
    public function addInitFunction($dialog, $func)
	{
		if(!isset($this->initFuncs[$dialog]))
		{
		    $this->initFuncs[$dialog] = array();
		}	
	    $this->initFuncs[$dialog][] = $func;
	}	
     public function getFileds()
    {
         list($temp_sql) = explode('where', $this -> sql);
         $sql = "EXPLAIN " . $temp_sql;
         $explain_row = $this -> db -> getAll($sql);
         foreach ($explain_row as $k => $row)
         {
             $tablName = $row['table'];
             $tableNames[] = $tablName;
             $sql_temp = "SHOW FULL COLUMNS FROM " . $tablName;
             $fieldInfo[$tablName] = $this -> db -> getAll($sql_temp);
            
          }

 		$fields_arr = array();
		$fieldKeys  = array();
		$fieldComments = array();
		$fieldTypes = array();
	    $this->fieldInfo   = $fieldInfo;
		$this->tableNames  = $tableNames;
		
		foreach ( $fieldInfo as $key => $tableFields ) {
			foreach ($tableFields as $key => $fieldRow) {
				$field_					= trim($fieldRow['Field']);
				
				$fields_arr[]			= $field_;
				$fieldComments[$field_] = $fieldRow['Comment'];
				$fieldTypes[$field_]	= $fieldRow['Type'];

			}
		}
		
		$this->fieldKeys     = $fieldKeys;
		$this->fieldComments = $fieldComments;
		$this->fieldTypes=$fieldTypes;
		$this->fields=$fields_arr;
		$fieldDisplaysOrg = array_combine ( $fields_arr, $fields_arr );
		$this->fieldDisplays = array_merge($fieldDisplaysOrg,$this->fieldDisplays);
		
		$this->queryurl .= '&tn=' . $this->tableNames[0];
		$this->queryurl .= '&fk=' . $this->formPrimkey;
		$this->queryurl .= '&fd=' . implode(",",$this->fields);
		$this->queryurl .= '&rk=' . $this->RK;
		$this->queryurl .= '&rkt=' . $this->RKT;
		
		$this->editurl .= '&tn=' . $this->tableNames[0];
		$this->editurl .= '&fk=' . $this->formPrimkey;
		$this->editurl .= '&fd=' .  implode(",",$this->fields);
        }
         
}
    

     ?>
