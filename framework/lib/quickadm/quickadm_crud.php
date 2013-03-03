<?php
include("quickadm_cfg.php");
include "../util/webpage.class.php";
error_reporting(0);

$filename = getcwd()."/longtext.txt";
$handle = fopen ($filename, "a");
fwrite ($handle, "\r\n");
//fwrite ($handle, json_encode($_REQUEST));

$examp = $_REQUEST["q"]; //query number

$page = $_REQUEST['page']; // get the requested page
$limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
$sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
$sord = $_REQUEST['sord']; // get the direction

$tableName =  $sql = strtolower($_REQUEST['tn']);
$tableFK   =  $_REQUEST['fk'];

$intables =  array(); 
if(isset($_REQUEST['intable']))
{
$intables = explode(",",$_REQUEST['intable']);
}
$infields =  array(); 
if(isset($_REQUEST['infield']))
{
$infields = explode(",",$_REQUEST['infield']);
}

if(isset($_REQUEST['detail']))
{
  $detailids = $_REQUEST['detail'];
}
$tableFields = explode(",",$_REQUEST['fd']);

$masterid ='';
isset($_REQUEST['id']) && $masterid = $_REQUEST['id'];

if(!$sidx) $sidx =1;

$totalrows = isset($_REQUEST['totalrows']) ? $_REQUEST['totalrows']: false;
if($totalrows) {$limit = $totalrows;}


$wh = "";
$searchOn = Strip($_REQUEST['_search']);
if($searchOn=='true') {
	$searchstr = Strip($_REQUEST['filters']);
	//$wh= constructWhere($searchstr);
	$jsona = json_decode($searchstr,true);
	$wh =  " AND ".getStringForGroup($jsona);
	var_dump($jsona);
	echo "<br/>";
	echo $searchstr;
}



function constructWhere($s){
    $qwery = "";
	//['eq','ne','lt','le','gt','ge','bw','bn','in','ni','ew','en','cn','nc']
    $qopers = array(
				  'eq'=>" = ",
				  'ne'=>" <> ",
				  'lt'=>" < ",
				  'le'=>" <= ",
				  'gt'=>" > ",
				  'ge'=>" >= ",
				  'bw'=>" LIKE ",
				  'bn'=>" NOT LIKE ",
				  'in'=>" IN ",
				  'ni'=>" NOT IN ",
				  'ew'=>" LIKE ",
				  'en'=>" NOT LIKE ",
				  'cn'=>" LIKE " ,
				  'nc'=>" NOT LIKE " );
    if ($s) {
        $jsona = json_decode($s,true);
        if(is_array($jsona)){
			$gopr = $jsona['groupOp'];
			$rules = $jsona['rules'];
            $i =0;
            foreach($rules as $key=>$val) {
                $field = $val['field'];
                $op = $val['op'];
                $v = $val['data'];
				if($v && $op) {
	                $i++;
					// ToSql in this case is absolutley needed
					$v = ToSql($field,$op,$v);
					if ($i == 1) $qwery = " AND ";
					else $qwery .= " " .$gopr." ";
					switch ($op) {
						// in need other thing
					    case 'in' :
					    case 'ni' :
					        $qwery .= $field.$qopers[$op]." (".$v.")";
					        break;
						default:
					        $qwery .= $field.$qopers[$op].$v;
					}
				}
            }
        }
    }
    return $qwery;
}


	function getStringForGroup( $group )
	{
		$i_='';
		$sopt = array('eq' => "=",'ne' => "<>",'lt' => "<",'le' => "<=",'gt' => ">",'ge' => ">=",'bw'=>" {$i_}LIKE ",'bn'=>" NOT {$i_}LIKE ",'in'=>' IN ','ni'=> ' NOT IN','ew'=>" {$i_}LIKE ",'en'=>" NOT {$i_}LIKE ",'cn'=>" {$i_}LIKE ",'nc'=>" NOT {$i_}LIKE ", 'nu'=>'IS NULL', 'nn'=>'IS NOT NULL');
		$s = "(";
		if( isset ($group['groups']) && is_array($group['groups']) && count($group['groups']) >0 )
		{
			for($j=0; $j<count($group['groups']);$j++ )
			{
				if(strlen($s) > 1 ) {
					$s .= " ".$group['groupOp']." ";
				}
				try {
					$dat = getStringForGroup($group['groups'][$j]);
					$s .= $dat;
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}
		if (isset($group['rules']) && count($group['rules'])>0 ) {
			try{
				foreach($group['rules'] as $key=>$val) {
					if (strlen($s) > 1) {
						$s .= " ".$group['groupOp']." ";
					}
					$field = $val['field'];
					$op = $val['op'];
					$v = $val['data'];
					if( $op ) {
						switch ($op)
						{
							case 'bw':
							case 'bn':
								$s .= $field.' '.$sopt[$op]."'$v%'";
								break;
							case 'ew':
							case 'en':
								$s .= $field.' '.$sopt[$op]."'%$v'";
								break;
							case 'cn':
							case 'nc':
								$s .= $field.' '.$sopt[$op]."'%$v%'";
								break;
							case 'in':
							case 'ni':
								$s .= $field.' '.$sopt[$op]."( '$v' )";
								break;
							case 'nu':
							case 'nn':
								$s .= $field.' '.$sopt[$op]." ";
								break;
							default :
								$s .= $field.' '.$sopt[$op]." '$v' ";
								break;
						}
					}
				}
			} catch (Exception $e) 	{
				echo $e->getMessage();
			}
		}
		$s .= ")";
		if ($s == "()") {
			//return array("",$prm); // ignore groups that don't have rules
			return " 1=1 ";
		} else {
			return $s;;
		}
	}


function ToSql ($field, $oper, $val) {
	// we need here more advanced checking using the type of the field - i.e. integer, string, float
	switch ($field) {
		case 'id':
			return intval($val);
			break;
		case 'amount':
		case 'tax':
		case 'total':
			return floatval($val);
			break;
		default :
			//mysql_real_escape_string is better
			if($oper=='bw' || $oper=='bn') return "'" . addslashes($val) . "%'";
			else if ($oper=='ew' || $oper=='en') return "'%" . addcslashes($val) . "'";
			else if ($oper=='cn' || $oper=='nc') return "'%" . addslashes($val) . "%'";
			else return "'" . addslashes($val) . "'";
	}
}



		
$db = mysql_connect(
$GLOBALS['datagrid']['db']['host'], 
$GLOBALS['datagrid']['db']['user'], 
$GLOBALS['datagrid']['db']['passwd'],
$GLOBALS['datagrid']['db']['charset'])
or die("Connection Error: " . mysql_error());

mysql_select_db($GLOBALS['datagrid']['db']['dbname']) or die("Error conecting to db.");
mysql_query( "SET NAMES '{$GLOBALS['datagrid']['db']['charset']}';", $db );
if(isset($detailids ))
{
  $wh .= " and (".$_REQUEST['rk'].") in (select ".$_REQUEST['rk']." from ".$_REQUEST['rkt']." where id in (". $detailids . ")) ";
}
$intableSQL = array();
$intableDelSQL = array();
switch ($examp) {
	
    case 1:

		$result = mysql_query("SELECT COUNT(*) AS count FROM ".$tableName." WHERE 1=1 ".$wh);
		
		$row = mysql_fetch_array($result,MYSQL_ASSOC);
		$count = $row['count'];
		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		} else {
			$total_pages = 0;
		}
        if ($page > $total_pages) $page=$total_pages;
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start = 0;
        $detailfilter = '';
        if($masterid != '')
        {
        	$detailfilter = ' and '.$tableFK.' in (' . $masterid . ')  ';
        }
		

		$fieldSql = '';
		foreach ( $tableFields as $key => $tableField )
		{
		  $fieldSql .= $tableField . ',';
		}
		$fieldSql = substr($fieldSql,0,-1);
		
        $SQL = "SELECT  ".$fieldSql."  FROM ".$tableName." WHERE 1=1 ". $detailfilter .$wh." ORDER BY ".$sidx." ". $sord." LIMIT ".$start." , ".$limit;
        fwrite ($handle, $SQL);
		$result = mysql_query( $SQL ) or die("Couldnt execute query.".mysql_error());
        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $count;
        $i=0;
		while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			      $responce->rows[$i]['id']=$row[$tableFK];
				  $resultArray = '';
		          foreach ( $tableFields as $key => $tableField )
		          {
		                   $resultArray[] = $row[$tableField];
		          }
			      $responce->rows[$i]['cell']=$resultArray;

            $i++;
		} 
        echo json_encode($responce);
           
        break;

        case 3:
         $SQL = "";
         if($_REQUEST['oper'] == "add")
         {
		 		$fieldSql = '';
		 		foreach ( $tableFields as $key => $tableField )
		 		{
				    if($tableField != $tableFK)
		 		    {$fieldSql .= $tableField . ',';}
		 		}
		 		$fieldSql = substr($fieldSql,0,-1);
		
		 		$valueSql = '';
		 		foreach ( $tableFields as $key => $tableField )
		 		{
				   if($tableField != $tableFK)
		 		   {$valueSql .= "'". $_REQUEST[$tableField] . "',";}
		 		}
		        $valueSql = substr($valueSql,0,-1);
				
         	$SQL = "insert into ".$tableName."(".$fieldSql.") values(" . $valueSql . ")";
			
			
			if(count($intables) > 0 )
			{
			    foreach ( $intables as $key => $intable )
				{
					$intableInsertSQL = "insert into " . strtolower($intable) . "(" .strtolower($infields["$key"]). ") values('" . $_REQUEST[$infields["$key"]] . "')"; 
					$intableDeleteSQL = "delete from " . strtolower($intable) . " where " .strtolower($infields["$key"]). " = '" . $_REQUEST[$infields["$key"]] . "'";
					$intableSQL[] = $intableInsertSQL;
					$intableDelSQL[] = $intableDeleteSQL;
					//echo $intableInsertSQL;
				}
			}
			

         }
         else if($_REQUEST['oper'] == "edit")
         {
		 		$updateSql = '';
		 		foreach ( $tableFields as $key => $tableField )
		 		{
				    if($tableField != $tableFK)
		 		    {$updateSql .= $tableField . "='" . $_REQUEST[$tableField] . "',";}
		 		}
				$updateSql = substr($updateSql,0,-1);
         	$SQL = "update  ".$tableName." set " . $updateSql . " where ".$tableFK." ='" . $_REQUEST['id'] . "'";
         } 
         else if($_REQUEST['oper'] == "del")
         {
         	$SQL = "delete  from ".strtolower($tableName)." where ".strtolower($tableFK)." in (" . $_REQUEST['id'] . ")";
			if(count($intables) > 0 )
			{
			
			    foreach ( $intables as $key => $intable )
				{
					$intableDeleteSQL = "delete from " . strtolower($intable) . " where " .strtolower($infields["$key"]). " in (" . Strip($_REQUEST['inFieldValues']) . ")";
					//echo $intableDeleteSQL;
					//die();
					$intableDelSQL[] = $intableDeleteSQL;
				}
			}
	
         } 
         if($SQL != "")
         {
         	         	
  
                    fwrite ($handle, $SQL);
                    $ret = mysql_query($SQL);
					if($ret)
					{
						if(15 == $_REQUEST['tableid'])
						{
							$intableSQL[] = "insert into trend_mediadetail(theindex,theimage) values(" . mysql_insert_id($db) . ",'" . $_REQUEST['theimages'] . "')";
						}
												
						foreach ( $intableDelSQL as $key => $sql )
						{
							mysql_query($sql);
						}
						foreach ( $intableSQL as $key => $sql )
						{
							mysql_query($sql);
						}
					}
					$redirecturl = $GLOBALS['config']['ROOTURL'] . "?module=admin";
					
					if(isset($_REQUEST['tableid']) && $_REQUEST['tableid'] !=-1)
					{
					    if(31 == $_REQUEST['tableid']) $_REQUEST['tableid']=10;
						if(34 == $_REQUEST['tableid']) $_REQUEST['tableid']=11;
						$redirecturl .="&action=subadmin" ."&tableid=" . $_REQUEST['tableid'];
					}
					
                    if($ret)
                    {
					    show_msg("操作成功。",$redirecturl);
                    	}
                    else
                    {
					  show_msg("操作失败。",$redirecturl);
                    }
        }
        break;
}
mysql_close($db);

function Strip($value)
{
	if(get_magic_quotes_gpc() != 0)
  	{
    	if(is_array($value))  
			if ( array_is_associative($value) )
			{
				foreach( $value as $k=>$v)
					$tmp_val[$k] = stripslashes($v);
				$value = $tmp_val; 
			}				
			else  
				for($j = 0; $j < sizeof($value); $j++)
        			$value[$j] = stripslashes($value[$j]);
		else
			$value = stripslashes($value);
	}
	return $value;
}
function array_is_associative ($array)
{
    if ( is_array($array) && ! empty($array) )
    {
        for ( $iterator = count($array) - 1; $iterator; $iterator-- )
        {
            if ( ! array_key_exists($iterator, $array) ) { return true; }
        }
        return ! array_key_exists(0, $array);
    }
    return false;
}
?>
