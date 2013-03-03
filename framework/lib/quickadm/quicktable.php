<?php
 include('quickadm_cfg.php');
 require_once("quickadm_db.php");


class QuickTable
{
     public $queryurl = 'framework/lib/quickadm/quickadm_crud.php?q=1';
     public $addurl  = '?module=admin&action=addForm';
	 public $posaddturl  = 'framework/lib/quickadm/quickadm_crud.php?q=3&oper=add';
     public $postmodurl  = 'framework/lib/quickadm/quickadm_crud.php?q=3&oper=edit';
	 ///the json jqgid
	 private $jsona;
	 private $json;
	 private $tablename;
	 
	 private $rebuild = false;

     private $rowCount = 0;
     public function QuickTable($json)
    {
	    $this->json = $json;
		$this->jsona = json_decode($json,true);
	
	     $this -> tablename = $this->jsona["table"];

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
	public function setRebuild()
    {
	     return $this->rebuild = true;
	}
	public function jugetable()
	{
	  if($this->isTableExsit())
		{
			if($this->rebuild)
			{
				$sql = "drop table " . $this->tablename;
				$this -> db ->query($sql);
				$this->createTable();
			}
		}
		else
		{
		    $this->createTable();
			//echo "not Exsit";
		}
    }
		private function createTable()
		{
		   $sql = "create table " . $this->tablename . " (id int auto_increment , ";
		   foreach($this->jsona["data"] as $key=>$field)
		   {
			 $sql .=  " ". $field["name"] . " ";
			 if($field["type"] == "int")
			 {
				$sql .=  " ". $field["type"] .  "(11) ";
			 }
			 else
			 {
				$sql .=  " ". $field["type"] .  " ";
			 }
			 
			 $sql .=  ",";
		   }
		   $sql .=" PRIMARY KEY  (id)) ENGINE=MyISAM;";
		   
		   $this -> db ->query($sql);
		}	
		private function isTableExsit()
		{
			$row=$this -> db ->getAll('SHOW TABLES;');

			//var_dump($row);
			foreach($row as $key=>$field)
		   {
		     //var_dump($field);
		     if(in_array(strtolower($this->tablename),array_values($field)))
			{
              return true;
			}
			
		   }
		   return false;
		}	
     public function display($type, $jsonPara)
     {
	    $this->jugetable();
		
         switch( $type )
             {
         case "QueryTable":
             $this->getQueryTableHtml($jsonPara );
             break;
         case "AddForm":
             $this->getAddFormHtml($jsonPara );
             break;
         case "ModifyForm":
             $this->getModifyFormHtml($jsonPara );
             break;
         default:
             $this->getQueryTableHtml($jsonPara );
             }
   
         return $this->result;
    }

     private function getQueryTableHtml($jsonPara )
         {
             $tableHeader = $this->getHeaderHtml(  );
             $tableBody = $this->getBodyHtml(  );
             $tableFooter = $this->getFooterHtml(  );
             $this->result =  $tableHeader .  $tableBody .    $tableFooter ;
         }
     private function getHeaderHtml(  )
         {
             $header = <<<EOT
<table cellspacing="1" cellpadding="0" class="list" style="font-size:12">
		<tbody><tr class="trover trout">
			<th width="6%"><label for="chkall"><input type="checkbox" id="chkall" name="chkall" onclick="checkAll();"></label></th>
EOT;
             	foreach($this->jsona["data"] as $key=>$field)
                {
                    $col =  sprintf(<<<EOT
					<th >%s</th>
EOT
                    , $field["desc"]);
                     $header .= $col;
                }
             $header .= <<<EOT
			 <th >操作</th>
</tr>
EOT;
             return $header;
         }
	private function getOutKeyValue($outfieldname, $outtable, $value)
	{
		 	$sql = "select ".$outfieldname." from " . $outtable . " where id=" . $value;
			$thethingrow = $this -> db -> getRow($sql);
			return $thethingrow[$outfieldname];
	}
     private function getBodyHtml(  )
     {
	 			$theInField = "";
			$intable = "";
			$infield = "";
	 		if(isset($this->jsona["intable"]))
		 {
			$intable = "&intable=";
			$infield = "&infield=";

		 
			foreach($this->jsona["intable"] as $key=>$row)
			{
				$intable .= $row["name"] . ",";
				$infield .= $row["infield"] . ",";
				$theInField  = $row["infield"];
			}
			$intable = substr($intable,0,-1);
			$infield = substr($infield,0,-1);
		}
		
         $body = <<<EOT

EOT;
         $tr = "";
             $sql = "select id ";
			 $where = "";
             	foreach($this->jsona["data"] as $key=>$field)
                {
                     $sql .= "," . $field["name"];
					if(("outkey" == $field["edittype"]) && (isset($_REQUEST['filterid'])) && ($_REQUEST['filterid'] !=-1))
					{
						$where = " where " . $field["name"] . " = " . $_REQUEST['filterid'];
					}
                }
				if($this->tablename != 'tbl_thingsv2')
                {$sql .= " from " . $this->tablename . $where . " order by id desc";}
				else
				{$sql .= " from " . $this->tablename . $where . " order by 	position, id desc";}
                $rows=$this -> db ->getAll($sql);
             	foreach($rows as $key=>$row)
                {
                    $this->rowCount ++;
					$theInFieldValue="";
					if($theInField !="")
					{
						$theInFieldValue=$row[$theInField] ;
					}
                    $tr = sprintf(<<<EOT
		<tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td><input type="checkbox" rel="del_chk" name="check" value="%s"><input type="hidden" name="theInField" id="%s" value='%s'/></td>
EOT
                    , $row[ "id" ],$theInField,$theInFieldValue);
	               foreach($this->jsona["data"] as $key=>$field)
                   {
						if("file" == $field["edittype"])
						{
							$td =  sprintf(<<<EOT
<td><image src="upload/%s" style="width:40px;height:40px;"></td>
EOT
                       , $row[$field["name"]]);
					   
						}
						elseif("outkey" == $field["edittype"])
						{
							$td =  sprintf(<<<EOT
<td>%s</td>
EOT
                       , $this->getOutKeyValue($field[ "outfieldname" ], $field[ "outtable" ], $row[$field["name"]]));
						}
						elseif("textarea" == $field["edittype"])
						{
						$content = substr( $row[$field["name"]],0, 80);
					   $content=str_replace('<','&lt;',$content);
                       $content=str_replace('>','&gt;',$content);
                       $td =  sprintf(<<<EOT
<td>%s</td>
EOT
                       , $content);
						}
						elseif("option" == $field["edittype"])
						{
						if($row[$field["name"]] == '0')
						{
						    $cellvalue='未付款';
						}
						else
						{
							$cellvalue='已付款';
						}
                       $td =  sprintf(<<<EOT
<td>%s</td>
EOT
                       , $cellvalue);
						}
						else
						{
						
                       $content = substr( $row[$field["name"]],0, 80);
					   $content=str_replace('<','&lt;',$content);
                       $content=str_replace('>','&gt;',$content);
                       $td =  sprintf(<<<EOT
<td>%s</td>
EOT
                       , $content);
					   }
                        $tr .= $td;
                   }
				   $theInFieldValue="";
				   if($theInField != "")
				   {
					  $theInFieldValue = $row[ $theInField ];
				   }
				   
                   $tr .= sprintf(<<<EOT
<td><a href="?module=admin&action=modForm&id=%s&tableid=%s">修改</a> | <a href="framework/lib/quickadm/quickadm_crud.php?q=3&fk=id&oper=del&id=%s&tn=%s&tableid=%s%s%s&%s=%s">删除</a></td>				   
</tr>
EOT
					, $row[ "id" ], $_REQUEST['tableid'], $row[ "id" ],$this->tablename,$_REQUEST['tableid'],$intable,$infield,$theInField,$theInFieldValue);
                   $body .= $tr;
                }
         $body .= "";
         return $body;
     }
     private function getFooterHtml(  )
     {   
	     $this->addurl .= '&tableid=' . $_REQUEST['tableid'];

		 
         $fieldCount = count($this->jsona["data"]);
             $footer = sprintf(<<<EOT
        <tr class="trover trout">
		    <td style="text-align:left;padding-left:10px;padding-right:10px;" colspan="20"><input type="submit" value="删除所选" id="delall" > 
			<input type=button value="增加" onclick="window.location.href='%s'" style="width:70px;">
			<div style="float:right;">总行数: %s</div>
			</td>
		</tr>
	</tbody>
</table>
EOT
             ,$this->addurl, $this->rowCount);
             return $footer;
         }
    private function getAddFormHtml( $jsonPara )
       {
		$this->posaddturl .= '&tableid='. $_REQUEST['tableid'] . '&tn=' . $this->tablename . '&fk=id&fd=id';

		
            $form =  sprintf(<<<EOT
<table cellspacing="1" cellpadding="0" class="list" style="font-size:12" id="addform">
		<tbody>
		    <tr style="background: none repeat scroll 0 0 #BDBDBD;color: #FFFFFF;font-weight: bold;" >
			<td class="td_left1" colspan="2">%s</td> </tr>
EOT
,$this->jsona["tabledesc"]
);
             foreach($this->jsona["data"] as $key=>$field)
             {
			     $this->posaddturl .=','.$field[ "name" ];
				 
                 $fieldType = $field["edittype"];
                 $fieldHtml ="";
                 switch( $fieldType )
                     {
                 case "text":
                     $fieldHtml= $this->getTextFieldHtml($field,"");
                     break;
                 case "file":
                     $fieldHtml =  $this->getFileFieldHtml($field,"");
                     break;
			     case "textarea":
					$fieldHtml = $this->getTextAreaFieldHtml($field,"");
					break;
				case "date":
					$fieldHtml = $this->getDateFieldHtml($field,"");
					break;					
			     case "fulltextarea":
					$fieldHtml = $this->getFullTextAreaFieldHtml($field,"");
					break;		
			     case "outkey":
					$fieldHtml = $this->getSelectFieldHtml($field,"");
					break;
			     case "option":
					$fieldHtml = $this->getOptionFieldHtml($field,"");
					break;
											
                 default:
                    $fieldHtml =  $this->getTextFieldHtml($field,"");
                     }
                 $form .= $fieldHtml;
             }
		if(isset($this->jsona["intable"]))
		 {
			$intable = "&intable=";
			$infield = "&infield=";
		 
			foreach($this->jsona["intable"] as $key=>$row)
			{
				$intable .= $row["name"] . ",";
				$infield .= $row["infield"] . ",";
			}
			$intable = substr($intable,0,-1);
			$infield = substr($infield,0,-1);
			$this->posaddturl .= $intable;
			$this->posaddturl .= $infield;
			

		}
			 $form = '<form id="form" name="form" method="post" action="'. $this->posaddturl .'">' . $form;
			 $form .= sprintf(<<<EOT
			 <tr>
			 <td class="td_left1" colspan="2"><button type="submit" style="width:60px;float:left;margin-left:10px;">确定</button></td> </tr>
EOT
);			 
			 $form .= '</tbody></table>';
             $this->result = $form;

         }
     private function getTextAreaFieldHtml( $field,$value)
         {
		 $value=str_replace('&lt;','<',$value);
		      $value=str_replace('&gt;','>',$value);
			  $value=stripcslashes($value);
			  
              $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td class=""><input style="height:300px;width:500px;overflow-y: auto;" type="textarea" name="%s" id="%s" value='%s'/></td></tr>
           
EOT
              , $field[ "desc" ],$field[ "name" ],$field[ "name" ], $value);
              return $fieldHtml;
         }		
	private function getOptionFieldHtml( $field,$value)
         {

			  
              $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td class="" style="text-align:left;">
			<select name="%s" id="%s" >
<option value="0">未付款</option>
<option value="1">已付款</option>
</select>
<script type="text/javascript">
$("#%s option[value='%d']").attr("selected", true); 
</script>
			</td></tr>
           
EOT
              , $field[ "desc" ],$field[ "name" ],$field[ "name" ],$field[ "name" ], $value);
              return $fieldHtml;
         }	
		private function getFullTextAreaFieldHtml( $field,$value)
         {
		 $value=str_replace('&lt;','<',$value);
		      $value=str_replace('&gt;','>',$value);
			  $value=stripcslashes($value);
			  
	  include_once 'framework/lib/fck/fckeditor.php';
      $oFCKeditor = new FCKeditor('FCKeditor1');
      $oFCKeditor->BasePath = 'framework/lib/fck/' ;
      $oFCKeditor->InstanceName = $field[ "name" ] ;    //相对应的textarea的名字，是用于提交吧
      $oFCKeditor->ToolbarSet = 'Basic' ;    //工具栏的名字
      $oFCKeditor->Width = '900' ;
      $oFCKeditor->Height = '350' ;
      $oFCKeditor->Value = $value;	
      $fck = $oFCKeditor->CreateHtml();	  
       $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td style="text-align:left;">%s</td></tr>
EOT
, $field["desc"],$fck);
              return $fieldHtml;
         }		 
     private function getTextFieldHtml( $field,$value)
         {
			  $value=str_replace('&lt;','<',$value);
		      $value=str_replace('&gt;','>',$value);
			  $value=stripcslashes($value);
              $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td class=""><input type="text" name="%s" id="%s" value='%s'/></td></tr>
           
EOT
              , $field[ "desc" ],$field[ "name" ],$field[ "name" ], $value);
              return $fieldHtml;
         }
     private function getDateFieldHtml( $field,$value)
         {
              $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td class=""><input class="Wdate" type="text" name="%s" id="%s" value='%s' onfocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});"/>
			</td></tr>
           
EOT
              , $field[ "desc" ],$field[ "name" ],$field[ "name" ], $value,$field[ "name" ]);
              return $fieldHtml;
         }		 
     private function getSelectFieldHtml( $field,$value)
         {
		 	$sql = "select distinct id,".$field[ "outfieldname" ]." from " . $field[ "outtable" ];
			$thethingrow = $this -> db -> getAll($sql);
			$control =  sprintf(<<<EOT
			<select name="%s" id="%s">
EOT
              , $field[ "name" ], $field[ "name" ]);
			foreach ($thethingrow as $k => $row)
			{
				$option = sprintf(<<<EOT
				<option value="%d">%s</option>
EOT
              , $row[ "id" ], $row[$field[ "outfieldname" ]]);
				$control .= $option;
			}
			$control .= " </select>";
              $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'" >
			<td class="td_left1">%s</td> <td class="" style="text-align:left;">%s</td></tr>
<script type="text/javascript">
$("#%s option[value='%d']").attr("selected", true); 
</script>           
EOT
              , $field[ "desc" ], $control, $field[ "name" ], $value);
			  
              return $fieldHtml;
         }		 
     private function getFileFieldHtml( $field ,$value)
         {
		     $value=str_replace('&lt;','<',$value);
		      $value=str_replace('&gt;','>',$value);
			  $value=stripcslashes($value);
			  $theinfo ="";
			  if(isset($field[ "theinfo" ]))
			  {
				$theinfo = $field[ "theinfo" ];
			  }
			  
             $fieldHtml =  sprintf(<<<EOT
       <tr class="trover trout" onMouseOver="this.className='trover'" onMouseOut="this.className='trout'">
			<td class="td_left1">%s</td> <td class="">
			<input type="file" name="%s_fileinput" id="%s_fileinput" onchange="UploadFile(this);"/>
           <input type="hidden" name="%s" id="%s" value='%s'/>
           <img id='%s_img'  src='upload/%s' style='margin-top:9px;width:40px;height:40px;float:left;'><span style="display:inline-block;float:left;margin-left:20px;margin-top:10px;">%s</span>
			</td></tr>

EOT
              , $field[ "desc" ],$field[ "name" ] ,$field[ "name" ],$field[ "name" ],$field[ "name" ],$value, $field[ "name" ],$value,$theinfo);

              return $fieldHtml;
         }
     private function getModifyFormHtml( $jsonPara )
     {
          	$this->postmodurl .= '&tableid='. $_REQUEST['tableid'] . '&tn=' . $this->tablename . '&fk=id&id=' .$_REQUEST['id']. '&fd=id';
            $form =  sprintf(<<<EOT
<table cellspacing="1" cellpadding="0" class="list" style="font-size:12" id="addform">
		<tbody>
		    <tr style="background: none repeat scroll 0 0 #BDBDBD;color: #FFFFFF;font-weight: bold;" >
			<td class="td_left1" colspan="2">%s</td> </tr>
EOT
,$this->jsona["tabledesc"]
);

			 $sql = "select * from " .$this->tablename. " where id=" . $_REQUEST['id'];
		     $thethingrow = $this -> db -> getRow($sql);
		
             foreach($this->jsona["data"] as $key=>$field)
             {
			     $this->postmodurl .=','.$field[ "name" ];
				 
                 $fieldType = $field["edittype"];
                 $fieldHtml ="";
                 switch( $fieldType )
                     {
                 case "text":
                     $fieldHtml= $this->getTextFieldHtml($field, $thethingrow[$field[ "name" ]]);
                     break;
                 case "file":
                     $fieldHtml =  $this->getFileFieldHtml($field, $thethingrow[$field[ "name" ]]);
                     break;
				case "textarea":
					$fieldHtml = $this->getTextAreaFieldHtml($field,$thethingrow[$field[ "name" ]]);
					break;
				case "date":
					$fieldHtml = $this->getDateFieldHtml($field,$thethingrow[$field[ "name" ]]);
					break;	
			     case "fulltextarea":
					$fieldHtml = $this->getFullTextAreaFieldHtml($field,$thethingrow[$field[ "name" ]]);
					break;	
			     case "outkey":
					$fieldHtml = $this->getSelectFieldHtml($field, $thethingrow[$field[ "name" ]]);
					break;
			     case "option":
					$fieldHtml = $this->getOptionFieldHtml($field, $thethingrow[$field[ "name" ]]);
					break;
                 default:
                   $fieldHtml =  $this->getTextFieldHtml($field, $thethingrow[$field[ "name" ]]);
                     }
                 $form .= $fieldHtml;
             }
			 $form = '<form id="form" name="form" method="post" action="'. $this->postmodurl .'">' . $form;
			 $form .= sprintf(<<<EOT
			 <tr>
			 <td class="td_left1" colspan="2"><button type="submit" style="width:60px;float:left;margin-left:10px;">确定</button></td> </tr>
EOT
);			 
			 $form .= '</tbody></table>';			 
             $this->result = $form;   
     }
		
}
    

     ?>
