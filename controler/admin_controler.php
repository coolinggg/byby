<?php
include 'framework/lib/quickadm/quickadm_json_widget.php';
include 'framework/lib/quickadm/quicktable.php';
include 'framework/lib/util/webpage.class.php';

include 'common.php';
class admin_controler extends controller{
	private $params = array();

	public function login()
	{
		     if((isset($_REQUEST["username"])) && (isset($_REQUEST["password"])))
	     {
		     $userName=$_REQUEST["username"];
             $userPassWord=$_REQUEST["password"];
			 
			 $sql = "select * from users where thename='" . $userName . "'";
		     $thethingrow = $this -> db -> getRow($sql);
		
			 
			 if (isset($thethingrow['thename']) && $thethingrow['thepasswd']== $userPassWord)
			 {
			     $_SESSION['user']['login'] = 1;
				 $_SESSION['user']['name'] = $userName;
				 redirect($GLOBALS['config']['ROOTURL'] . '?module=admin&action=subadmin&tableid=7&currcat=品牌');
		     }
			 else
			 {
			     $_SESSION['user']['login'] = 0;
				 redirect($GLOBALS['config']['ROOTURL'] . '?item=login');
			 }

		}
		else
		{
		    $_SESSION['user']['login'] = 0;
		    redirect($GLOBALS['config']['ROOTURL'] . '?item=login');
		}
		
		
	}
	public function logout()
	{
			$_SESSION['user']['login'] = 0;
			unset($_SESSION['user']['name']);
		    redirect($GLOBALS['config']['ROOTURL'] . '?item=login');
	}
	public function jugeLogin()
	{
		if($_SESSION['user']['login'] == 0)
		{
			redirect($GLOBALS['config']['ROOTURL'] . '?item=login');
		}
	}
	
	public function index()
	{
		$this->jugeLogin();
		redirect($GLOBALS['config']['ROOTURL'] . '?module=admin&action=subadmin&tableid=7&currcat=品牌');
	}
	public function admin()
     {
		$this->jugeLogin();
		$this->initNav();
		
		$this->params['level1'] = '首页';
		$this->params['level2'] = '元数据';

		$this->params['tableid'] = "-1";
		$_REQUEST['tableid'] = "-1";
	    $json=<<<EOT
		{"table":"tbl_thingsv2","tabledesc":"系统表", "description":"为你的系统定制新的模型", "data":
       [
        {"name":"theclass","index":"theclass","description":"", "type":"varchar(256)","desc":"类型","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"thethings","index":"thethings","description":"","type":"varchar(256)","desc":"对象","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"json","index":"json","type":"text","description":"","desc":"结构","width":100, "editable":true, "edittype":"textarea", "editoptions":{"rows":10,"cols":100}},
		{"name":"flag","index":"flag","type":"int","description":"","desc":"重建","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}}
   		]
		}
EOT;

		$json = Strip($json);
		$jsona = json_decode($json,true);
		if($jsona)
		{
			$testgrid2 = new QuickTable($json);
			$this->params['testtable'] = $testgrid2->display("QueryTable", "");
		}
		$this->params['currtablename'] = "tbl_thingsv2";
		
	    $this->display('new2main.html', $this->params);
	 }
	 		
    public function subadmin()
     {
		$this->jugeLogin();
	    $this->initNav();
		if(isset($_REQUEST['tableid']))
		{

			$this->getThing();
			return;
		}
	 }

	 public function getThing()
	 {
		$sql = "select * from tbl_thingsv2 where id=" . $_REQUEST['tableid'];
		$thethingrow = $this -> db -> getRow($sql);
		
		$this->params['level1'] = $thethingrow['theclass'];
		$this->params['level2'] = $thethingrow['thethings'];
		
		
		$json = Strip($thethingrow['json']);
		$jsona = json_decode($json,true);
		//echo $json;
		if($jsona)
		{
			//dddddddddddddddddddddddd
		    $this->params['tableid'] = $_REQUEST['tableid'];
			$intable = "";
			$infield = "";
		if(isset($jsona["intable"]))
		 {
			$intable = "&intable=";
			$infield = "&infield=";
			$theInField = "";
		 
			foreach($jsona["intable"] as $key=>$row)
			{
				$intable .= $row["name"] . ",";
				$infield .= $row["infield"] . ",";
				$theInField  = $row["infield"];
			}
			$intable = substr($intable,0,-1);
			$infield = substr($infield,0,-1);
			

		}
			$this->params['intable'] = $intable;
			$this->params['infield'] = $infield;
			if(isset($jsona["outdesc"]))
			{
				$this->params['filterName'] = $jsona["outdesc"];
				$sql = "select distinct id," .$jsona["outfieldname"]. " from " . $jsona["outtable"] . " order by id desc";
				$thethingrow2 = $this -> db -> getAll($sql);
				$this->params['filterValue'] = $thethingrow2;
				$this->params['fidldName'] = $jsona["outfieldname"];
				
				if(!isset($_REQUEST['filterid']))
				{
					$_REQUEST['filterid'] = -1;
				}
				$this->params['filterid'] = $_REQUEST['filterid'];
			}
			//dddddddddddddddddddddddd
			
			$testgrid2 = new QuickTable($json);
			$this->params['testtable'] = $testgrid2->display("QueryTable", "");
			if($thethingrow['flag'] !=0)
			{
				//$testgrid2->setRebuild();
				$sql = "update tbl_thingsv2 set flag=0 where id=" . $_REQUEST['tableid'];
				$this -> db -> query($sql);
			}
		}
		$this->params['currtablename'] = $jsona["table"];
		$this->params['tableid'] = $_REQUEST['tableid'];
		$this->params['json'] = $json;

	    $this->display('new2main.html', $this->params);
     }
public function addForm()
     {
		$this->jugeLogin();
		$this->initNav();
		
		$json="";
		if($_REQUEST['tableid'] == "-1")
	   {
	   $json=<<<EOT
		{"table":"tbl_thingsv2","tabledesc":"系统表", "description":"为你的系统定制新的模型", "data":
       [
        {"name":"theclass","index":"theclass","description":"", "type":"varchar(256)","desc":"类型","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"thethings","index":"thethings","description":"","type":"varchar(256)","desc":"对象","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"json","index":"json","type":"text","description":"","desc":"结构","width":100, "editable":true, "edittype":"textarea", "editoptions":{"rows":10,"cols":100}},
		{"name":"flag","index":"flag","type":"int","description":"","desc":"重建","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}}
   		]
		}
EOT;
		$this->params['level1'] = '首页';
		$this->params['level2'] = '元数据';
		$this->params['level3'] = '增加';
		}
		else
		{
		$sql = "select * from tbl_thingsv2 where id=" . $_REQUEST['tableid'];
		$thethingrow = $this -> db -> getRow($sql);
		
		$json = Strip($thethingrow['json']);
		$this->params['level1'] = $thethingrow['theclass'];
		$this->params['level2'] = $thethingrow['thethings'];
		$this->params['level3'] = '增加';
		
		}
		$jsona = json_decode($json,true);
		 	

		
		if($jsona)
		{
			$testgrid2 = new QuickTable($json);
			$this->params['testtable'] = $testgrid2->display("AddForm", "");
		}
	    $this->display('new2mainform.html', $this->params);
	 }
	 public function modForm()
     {
		$this->jugeLogin();
		$this->initNav();
		
		$json="";
		if($_REQUEST['tableid'] == "-1")
	   {
	    $json=<<<EOT
		{"table":"tbl_thingsv2","tabledesc":"系统表", "description":"为你的系统定制新的模型", "data":
       [
        {"name":"theclass","index":"theclass","description":"", "type":"varchar(256)","desc":"类型","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"thethings","index":"thethings","description":"","type":"varchar(256)","desc":"对象","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}},
		{"name":"json","index":"json","type":"text","description":"","desc":"结构","width":100, "editable":true, "edittype":"textarea", "editoptions":{"rows":10,"cols":100}},
		{"name":"flag","index":"flag","type":"int","description":"","desc":"重建","width":100, "editable":true, "edittype":"text","editoptions":{"size":10}}
   		]
		}
EOT;
		$this->params['level1'] = '首页';
		$this->params['level2'] = '元数据';
		$this->params['level3'] = '修改';
		}
		else
		{
		$sql = "select * from tbl_thingsv2 where id=" . $_REQUEST['tableid'];
		$thethingrow = $this -> db -> getRow($sql);
		
		$json = Strip($thethingrow['json']);
		$this->params['level1'] = $thethingrow['theclass'];
		$this->params['level2'] = $thethingrow['thethings'];
		$this->params['level3'] = '修改';
		
		}
		$json = Strip($json);
		$jsona = json_decode($json,true);
		if(($jsona) && ($_REQUEST['tableid'] != 41))
		{
			$testgrid2 = new QuickTable($json);
			$this->params['testtable'] = $testgrid2->display("ModifyForm", "");
		}
		if($_REQUEST['tableid'] != 41)
		{
		 $this->params['id'] = $_REQUEST['id'];
		}
		
		if($_REQUEST['tableid'] == 10)
		{
			$this->params['mainid'] = $_REQUEST['id'];
		}
		if($_REQUEST['tableid'] == 31)
		{
			$this->params['mainid'] = $_REQUEST['mainid'];
		}
		if($_REQUEST['tableid'] == 11)
		{
			$this->params['mainid'] = $_REQUEST['id'];
		}
		if($_REQUEST['tableid'] == 34)
		{
			$this->params['mainid'] = $_REQUEST['mainid'];
		}
		if($_REQUEST['tableid'] == 39)
		{
			$this->params['mainid'] = $_REQUEST['id'];
		}
		if($_REQUEST['tableid'] == 41)
		{
			$this->params['mainid'] = $_REQUEST['mainid'];
		}		
		if(($_REQUEST['tableid'] == 10) || ($_REQUEST['tableid'] == 31))
		{
			
			$this->display('new2mainform_product.html', $this->params);
		}
		elseif(($_REQUEST['tableid'] == 11) || ($_REQUEST['tableid'] == 34))
		{
			
			$this->display('new2mainform_collection.html', $this->params);
		}
		elseif(($_REQUEST['tableid'] == 39) || ($_REQUEST['tableid'] == 41))
		{
			$this->params['tableid'] = $_REQUEST['tableid'];
			$this->display('new2mainform_order.html', $this->params);
		}		
		else
		{
			$this->display('new2mainform.html', $this->params);
		}
	    
	 }
	 

	 public function initNav()
	 {
		if(isset($_REQUEST['tableid']) && $_REQUEST['tableid']== -1)
		{
			$this->params['currcat'] = "元数据";
		}
		elseif(isset($_REQUEST['tableid']) && $_REQUEST['tableid']>= 0)
		{
			$sql = "select theclass from tbl_thingsv2 where id = " .$_REQUEST['tableid'];
			$thethings = $this -> db -> getAll($sql);
			$this->params['currcat'] = $thethings[0]["theclass"];
		}
		else
		{
			$this->params['currcat'] = "品牌";
		}
	    $categry = array();
		$things = array();
		$sql = "select distinct theclass,position from tbl_thingsv2 order by position desc";
		$theclasses = $this -> db -> getAll($sql);
		foreach ($theclasses as $k => $row)
        {
			$categryName = $row['theclass'];
			$categry[]=$categryName;
			$things[''.$categryName]= array();
			
			$sql = "select id, thethings from tbl_thingsv2 where theclass = '" .$row['theclass'] ."'";
			$thethings = $this -> db -> getAll($sql);
			foreach ($thethings as $thingkey => $thingsrow)
			{
				if($thingsrow["id"] == 31)
				{
					continue;
				}
				if($thingsrow["id"] == 34)
				{
					continue;
				}
				$things[''.$categryName][] = $thingsrow;

			}

	    }
		$this->params['categry'] = $categry;
		$this->params['things']  = $things;
	}
	public function mod_orderdetail()
	 {
	 	$orderdetailids = $_REQUEST["uid"];
		$goodsnums = $_REQUEST["goodsnum"];
		$good_sizes = $_REQUEST["thesize"];
		$good_prices = $_REQUEST["theprice"];

		foreach($orderdetailids as $k=>$v)
		{
		  		$sql = "update	orderdetail set theprice=".$good_prices[$k]. ", thesize='".$good_sizes[$k]."', totalcount=".$goodsnums[$k].", totalprice=" . ($good_prices[$k] * $goodsnums[$k]);
				$sql .= " where id = '" .$v. "'";
				$this -> db ->query($sql);
				echo $sql;
		}
		
		redirect($GLOBALS['config']['ROOTURL'] . '?module=admin&action=subadmin&tableid=39');
	}
}
?>	 
