<?php
require_once('framework/lib/quickadm/quickadm_json_widget.php');
require_once('common.php');
require_once('framework/lib/util/webpage.class.php');
require_once('framework/lib/quickadm/quickadm_cfg.php');
require_once("framework/lib/quickadm/quickadm_db.php");

class cart_goods {
	var  $goodsID;
	var  $goodsName;
	var  $theimage;
    var  $price;
	var  $size;
    var  $total_count;
	var  $total_price;	

	function cart_goods($goodsID, $size)
	{
		$this->goodsID = $goodsID;
		$this->size = $size;
		$this->init();
		$this->total_count = 0;
		$this->total_price = 0;
	}
	
	function init()
	{
		$db = getdb();
		$sql = "select * from Store_OnlineStore where id=" . $this->goodsID;
		$goodsrow =  $db->getRow($sql);
		$this->price = $goodsrow['price'];
		$this->goodsName = $goodsrow['thename'];
		$this->theimage = $goodsrow['theimage'];
	}
	
	function addBuyNumber($addNum)
	{
		$this->total_count += $addNum;
		$this->total_price = $this->total_count * $this->price;
	}
}
?>	 
