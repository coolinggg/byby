<?php


$temp = sprintf(<<<EOT
<table id="%s"></table>
<div id="%s"></div>
<script type="text/javascript">
jQuery("#%s").jqGrid({
   	url:"%s",
	datatype: "json",
    colNames:[
EOT
,$this->this_var_name,$this->this_var_name_page,$this->this_var_name,$this->queryurl);
$this->result .=$temp;

foreach ( $this->fieldDisplays as $key => $name ) 
{	
$temp = sprintf("'%s',",$name);
$this->result.=$temp;
}

$this->result.="
],
colModel:[";

foreach ($this ->fields as $key => $name ) 
{	
    $otherAttr="";
    if(isset($this->fieldAttr[$name]))
    {

	    foreach ($this->fieldAttr[$name] as $key => $attr )
	    {
		   $otherAttr .= ",". $attr;

	    }

	}
   if($name != $this->formPrimkey)
   {
   $temp = sprintf( <<<EOT
   {name:"%s",index:"%s", width:"100",editable:true,editoptions:{size:10}
EOT

,$name,$name);
   $this->result.=$temp . $otherAttr . "},";
   }
   else
   {
   $temp = sprintf( <<<EOT
   {name:"%s",index:"%s", width:"100",editable:false, hidden:true,editoptions:{size:10}
EOT
,$name,$name);
   $this->result.=$temp . $otherAttr . "},";
   }
}

$this->result.="],";
foreach ($this ->funcs as $key => $func ) 
{
$temp = sprintf(<<<EOT
%s,
EOT
,$func);
$this->result.=$temp;
}
$temp = sprintf( <<<EOT
   	rowNum:10,
   	rowList:[],
   	pager: "#%s",
   	sortname: "id",
    viewrecords: true,
    sortorder: "desc",

    editurl:"%s",
    multiselect: true,
	height:"100%%",
    autowidth: true
});

function processAddEdit(response){
var success =true;
var message ="";
var json = eval("("+ response.responseText + ")");
//jQuery("debug").innerText = response.responseText;
   success =json.success;
   message =json.errors;

var new_id ="1";
return [success,message,new_id];
}

jQuery("#%s").jqGrid("navGrid","#%s",
EOT
,$this->this_var_name_page,$this->editurl,$this->this_var_name,$this->this_var_name_page);
$this->result.=$temp;
$this->result.= <<<EOT
{},                                   
{height:280,afterSubmit:processAddEdit,closeAfterAdd:false,closeAfterEdit:false,reloadAfterSubmit:true, top:100,left:300
EOT;
if(isset($this->initFuncs['edit']))
{
foreach ($this ->initFuncs['edit'] as $key => $func ) 
{
$temp = sprintf(<<<EOT
,%s
EOT
,$func);
$this->result.=$temp;
}}
$this->result.=<<<EOT
},
{height:280,afterSubmit:processAddEdit,closeAfterAdd:false,closeAfterEdit:false,reloadAfterSubmit:true, top:100,left:300
EOT;
if(isset($this->initFuncs['add']))
{
foreach ($this ->initFuncs['add'] as $key => $func ) 
{
$temp = sprintf(<<<EOT
,%s
EOT
,$func);
$this->result.=$temp;
}}
$this->result.=<<<EOT
}, 
{reloadAfterSubmit:true
EOT;
if(isset($this->initFuncs['del']))
{
foreach ($this ->initFuncs['del'] as $key => $func ) 
{
$temp = sprintf(<<<EOT
,%s
EOT
,$func);
$this->result.=$temp;
}}
$this->result.=<<<EOT
},            
{multipleSearch:true,closeOnEscape:true,closeAfterSearch:true
EOT;
if(isset($this->initFuncs['search']))
{
foreach ($this ->initFuncs['search'] as $key => $func ) 
{
$temp = sprintf(<<<EOT
,%s
EOT
,$func);
$this->result.=$temp;
}}
$temp = sprintf(<<<EOT

}                                  
);
//,afterSubmit:processAddEdit
jQuery("#%s").jqGrid('navGrid','hideCol',"id");
</script>
EOT
,$this->this_var_name);
$this->result.=$temp;



?>