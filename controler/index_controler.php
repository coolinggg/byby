<?php
require_once('framework/lib/quickadm/quickadm_json_widget.php');
require_once('common.php');
require_once('framework/lib/util/webpage.class.php');
require_once('framework/lib/quickadm/quickadm_cfg.php');
require_once("framework/lib/quickadm/quickadm_db.php");
include 'cart_goods.class.php';
require_once('framework/lib/util/rss_class.php');
 
class index_controler extends controller{
    private $params = array();
    private $item = '';
	
	public function index()
     {
		
		if(isset($_REQUEST['item']))
        {
            $this->item = $_REQUEST['item'];
        }
        else
        {
            $this->item = "index";    
        }
		
		if($this->item == "designdetail")
		{
			$this->params['theindex'] = $_REQUEST['theindex'];
		}
		if($this->item == "trend_media_detail")
		{
			$this->params['theindex'] = $_REQUEST['theindex'];
		}
		if($this->item == "store_onlineshop_detail")
		{
			$this->params['theid'] = $_REQUEST['theid'];
		}
		if($this->item == "collection_detail")
		{
			$this->params['theid'] = $_REQUEST['theid'];
		}		
		if($this->item == "trend_employment")
		{
			//$this->params['subject'] = urlencode(iconv('utf-8','gbk','你好，不言不语。'));
		}	
		if($this->item == "blog_detail")
		{
			$this->params['id'] = $_REQUEST['id'];
		}
		
		//$this->params['mailtoSubject'] = urlencode(iconv('utf-8','gbk','你好，不言不语。'));
	    $this->display($this->item . '.html', $this->params);
	 }
	 
	 public function guestmsg()
     {
		if( empty($host) ) $dbhost = $GLOBALS['datagrid']['db']['host'];
		if( empty($user) ) $dbuser = $GLOBALS['datagrid']['db']['user'];
		if( empty($password) ) $dbpassword = $GLOBALS['datagrid']['db']['passwd'];
		if( empty($database) ) $database = $GLOBALS['datagrid']['db']['dbname'];
		if( empty($charset) ) $charset = $GLOBALS['datagrid']['db']['charset'];
		
	   $db = new MysqlDB('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
	   $sql = "insert into contact_guestbook(whoami,email,content) values('" .$_REQUEST['IAM']. "','". $_REQUEST['themail']. "','". $_REQUEST['thecontent'] ."')";
       $db ->query($sql);
	   show_msg("操作成功。",$GLOBALS['config']['ROOTURL'] . "?item=contact_guestbook");
	 }
     public function cartadd()
     {
	    $goodsID = $_REQUEST["goodsid"];
		$size = $_REQUEST["size"];
		$goodsNum = $_REQUEST["goodsnum"];
		$newGoods = new cart_goods($goodsID, $size);
		
		$goodkey = $goodsID +'-' + $size;
		
		$newGoods->addBuyNumber($goodsNum);
		
		if(isset($_SESSION['cart']['goods'][$goodkey]))
		{
			$oldGoods = unserialize($_SESSION['cart']['goods'][$goodkey]);
			$newGoods->addBuyNumber($oldGoods->total_count);
			$_SESSION['cart']['goods'][$goodkey]= serialize($newGoods);
		}
		else
		{
			$_SESSION['cart']['goods'][$goodkey]= serialize($newGoods);
		}
		echo '{"code":0}';
     }
     public function cartdel()
     {
	    $goodsID = $_REQUEST["goodsid"];
		$goodsize = $_REQUEST["goodsize"];
		$goodkey = $goodsID +'-' + $goodsize;
		
		unset($_SESSION['cart']['goods'][$goodkey]);
		
		show_msg("操作成功。",$GLOBALS['config']['ROOTURL'] . "?item=store_onlineshop_cart&action=cartshow");
     }	 
	 public function cartshow()
     {
	    $this->params['goods'] = array();
		$this->params['totalprice'] = 0.00;
		
		if(isset($_SESSION['cart']['goods']))
	    {
		foreach ($_SESSION['cart']['goods'] as $k => $v)
        {
		    $cartgoods = unserialize($v);
			//var_dump($cartgoods);
			$agoods = array();
			$agoods["goodsID"] = $cartgoods->goodsID;
			$agoods["goodsName"] = $cartgoods->goodsName;
			$agoods["theimage"] = $cartgoods->theimage;
			$agoods["price"] = $cartgoods->price;
			$agoods["size"] = $cartgoods->size;
			$agoods["total_count"] = $cartgoods->total_count;
			$this->params['goods'][] = $agoods;

			$this->params['totalprice'] += $cartgoods->total_price;
		}
		}
		else
		{
			$this->params['totalprice'] = 0;
		}
	    $this->display('store_onlineshop_cart.html', $this->params);
     }

	 public function blogmore()
	 {	
		$pageid = intval($_REQUEST["pageid"]);
		$offset = 2 * ($pageid - 1);
		$sql = "select * from Blog_Blog order by id desc limit " . $offset . ", 2 ";
		$blogs = $this -> db -> getAll($sql);
		$result->code = count($blogs);
		$result->data = $blogs;
		echo json_encode($result);;
	 }
	 public function store_store_detail()
	 {	
		$theid = $_REQUEST["theid"];
		if(isset($_REQUEST["pageid"]))
		{
			$pageid = intval($_REQUEST["pageid"]);
		}
		else
		{
			$pageid = 1;
		}
		if($pageid > 1)
		{
			$pageidpre = $pageid - 1;
		}
		else
		{
			$pageidpre = 1;
		}
		$sql = "select count(*) imagenumber from Store_Store_Detail where theindex=". $theid;
		$imagenumber = $this -> db -> getAll($sql);
		
		if($pageid < $imagenumber[0]["imagenumber"])
		{
			$pageidafter = $pageid + 1;
		}
		else
		{
			$pageidafter = 1;
		}
		$offset = 1 * ($pageid - 1);
		$sql = "select * from Store_Store_Detail where theindex=". $theid . " order by id desc limit " . $offset . ", 1 ";
		$storeimage = $this -> db -> getAll($sql);
		
		$this->params['storedetail'] = $storeimage[0];
		$this->params['pageidpre'] = $pageidpre;
		$this->params['pageidafter'] = $pageidafter;
		$this->params['theid'] = $theid;
		
		$this->display('store_store_detail.html', $this->params);
	 }
	 
	 public function collection_detail()
	 {	
		$theid = $_REQUEST["theid"];
	
		$sql = "select id from Museum_Collection where id < " . $theid . " order by id desc limit 1";
		$pre = $this -> db -> getAll($sql);
		

		
		if(count($pre) > 0)
		{
			$idpre = $pre[0]["id"];
		}
		else
		{
			$sql = "select max(id) id from Museum_Collection";
			$maxid = $this -> db -> getAll($sql);	
			$idpre = $maxid[0]["id"];
		}
		$sql = "select id from Museum_Collection where id > " . $theid . " order by id  limit 1";
		$next = $this -> db -> getAll($sql);		
		if(count($next) > 0)
		{
			$idafter = $next[0]["id"];
		}
		else
		{
			$idafter = $theid;
		}
		
		$this->params['idpre'] = $idpre;
		$this->params['idafter'] = $idafter;
		$this->params['theid'] = $theid;
		
		$this->display('collection_detail.html', $this->params);
	 }
	 
	 
	 public function store_onlineshop_detail()
	 {	
		$theid = $_REQUEST["theid"];
	
		$sql = "select id from Store_OnlineStore where id < " . $theid . " order by id desc limit 1";
		$pre = $this -> db -> getAll($sql);
		

		
		if(count($pre) > 0)
		{
			$idpre = $pre[0]["id"];
		}
		else
		{
			$sql = "select max(id) id from Store_OnlineStore";
			$maxid = $this -> db -> getAll($sql);	
			$idpre = $maxid[0]["id"];
		}
		$sql = "select id from Store_OnlineStore where id > " . $theid . " order by id  limit 1";
		$next = $this -> db -> getAll($sql);		
		if(count($next) > 0)
		{
			$idafter = $next[0]["id"];
		}
		else
		{
			$idafter = 1;
		}
		
		$this->params['idpre'] = $idpre;
		$this->params['idafter'] = $idafter;
		$this->params['theid'] = $theid;
		
		$this->display('store_onlineshop_detail.html', $this->params);
	 }
	 
	 public function blog_rss()
	 {	
		$sql = "select * from Blog_Blog order by id desc limit 100";
		$blogs = $this -> db -> getAll($sql);
		$rss = new UniversalFeedCreator(); 
		$rss->title = "不言不语"; 
		$rss->link = "http://heyaner.com";
		$rss->description = "欢迎来到不言不语"; 

		foreach ($blogs as $k => $v)
		{
			$item = new FeedItem(); 
			$item->title =$v['createtime']; 
			$item->link = 'http://网址/文件?参数='.$v['id']; 
			$item->description =$v['theimages']; 
			$rss->addItem($item);
		}
		$rss->saveFeed("RSS2.0", "rss.xml");
	 }
	 
	 public function mod_product_num()
	 {
		$goodsnum = $_REQUEST["goodsnum"];
		$good_uid = $_REQUEST["good_uid"];
		$good_sizes = $_REQUEST["good_size"];
		unset($_SESSION['cart']['goods']);
		foreach($good_uid as $k=>$v)
		{
			$goodsID = $v;
			$goodsNum = $goodsnum[$k];
			$good_size = $good_sizes[$k];
			$newGoods = new cart_goods($goodsID, $good_size);
			$newGoods->addBuyNumber($goodsNum);
			$goodkey = $goodsID +'-' + $good_size;
			
			$_SESSION['cart']['goods'][$goodkey]= serialize($newGoods);
		}
		
		show_msg("成功修改产品数量。",$GLOBALS['config']['ROOTURL'] . "?action=cartshow");
	 }
	 
	 public function order()
     {
	    $this->params['goods'] = array();
		$this->params['totalprice'] = 0.00;
		
		$count=0;
		if(isset($_SESSION['cart']['goods']))
	    {
		foreach ($_SESSION['cart']['goods'] as $k => $v)
        {
		
		    $cartgoods = unserialize($v);
			if($cartgoods->total_count == 0)
			{
				continue;
			}
			//var_dump($cartgoods);
			$agoods = array();
			$agoods["goodsID"] = $cartgoods->goodsID;
			$agoods["goodsName"] = $cartgoods->goodsName;
			$agoods["theimage"] = $cartgoods->theimage;
			$agoods["price"] = $cartgoods->price;
			$agoods["size"] = $cartgoods->size;
			$agoods["total_count"] = $cartgoods->total_count;
			$this->params['goods'][] = $agoods;

			$this->params['totalprice'] += $cartgoods->total_price;
			
			$count +=1;
		}
		}
		else
		{
			$this->params['totalprice'] = 0;
		}
		if($count == 0)
		{
			show_msg("购物车没有商品，请继续购物。",$GLOBALS['config']['ROOTURL'] . "?action=cartshow");
		}
		else
		{
	       $this->display('store_onlineshop_order.html', $this->params);
		}
     }
		 
	 public function orderconfirm()
     { 
	   	if( empty($host) ) $dbhost = $GLOBALS['datagrid']['db']['host'];
		if( empty($user) ) $dbuser = $GLOBALS['datagrid']['db']['user'];
		if( empty($password) ) $dbpassword = $GLOBALS['datagrid']['db']['passwd'];
		if( empty($database) ) $database = $GLOBALS['datagrid']['db']['dbname'];
		if( empty($charset) ) $charset = $GLOBALS['datagrid']['db']['charset'];
		
		
		
	$sn = date('Ymdhis');
	   $db = new MysqlDB('mysql', $dbhost, $dbuser, $dbpassword, $database, $charset);
	   $totalprice=0;
	   if(isset($_SESSION['cart']['goods']))
	    {
			foreach ($_SESSION['cart']['goods'] as $k => $v)
			{
		
				$cartgoods = unserialize($v);
				if($cartgoods->total_count == 0)
				{
					continue;
				}
				
				$sql = "insert into 	orderdetail(theindex,goodid,goodname,theimage,	theprice,	thesize,totalcount,totalprice) ";
				$sql .= "values('" .$sn. "','". $cartgoods->goodsID. "','". $cartgoods->goodsName. "','". $cartgoods->theimage. "','". $cartgoods->price. "','". $cartgoods->size. "','". $cartgoods->total_count. "','". $cartgoods->total_price. "')";
				$db ->query($sql);
				$totalprice += $cartgoods->total_price;
			}
		}
		else
		{
			return;
		}
		
	   $sql = "insert into 	ordermaininfo(theindex,whoiam,email,phone1,phone2,province,city,address, totalprice, thedate) ";
	   $sql .= "values('" .$sn. "','". $_REQUEST['name']. "','". $_REQUEST['email']."','". $_REQUEST['phone1']."','". $_REQUEST['phone2']."','". $_REQUEST['province']."','". $_REQUEST['city']."','". $_REQUEST['address']."','". $totalprice."','". date('Y-m-d') ."')";
	   
	   $this->params['sn'] =  $sn;
	   $this->params['total_price'] =  $cartgoods->total_price;
	  
       $db ->query($sql);
	   unset($_SESSION['cart']['goods']);
	   $this->display('store_onlineshop_orderinfo.html', $this->params);
	 }
	 
}
?>	 
