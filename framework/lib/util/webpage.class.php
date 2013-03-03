<?php
function show_msg($msg,$url) 
{
  echo '<meta content="text/html; charset=utf-8" http-equiv="content-type">';
  echo "<script language='javascript'>"; 
  echo "alert('$msg');"; 
  echo "document.location='{$url}';"; 
  echo "</script>";
}
function redirect($url) 
{
  echo '<meta content="text/html; charset=utf-8" http-equiv="content-type">';
  echo "<script language='javascript'>"; 
  echo "document.location='{$url}';"; 
  echo "</script>";
}

?>