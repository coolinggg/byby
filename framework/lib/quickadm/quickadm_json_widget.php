<?php
 include('quickadm_cfg.php');
 require_once("quickadm_db.php");


class QuickJsonGrid
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
	 
	 ///the json jqgid
	 private $jsona;
	 private $json;
	 private $tablename;
	 private $displaynames='';
	 private $modelstring='';
     private $rebuild = false;

     private $fileFilds = array(  );
     private $imageJS="";
     public function QuickJsonGrid($varname, $json)
    {
	    $this->json = $json;
		$this->jsona = json_decode($json,true);
	
	     $this->varName =$varname;
	     $this -> tablename = $this->jsona["table"];

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

     public function getVarName()
    {
	     return $this->varName;
	}
	public function setRebuild()
    {
	     return $this->rebuild = true;
	}
	
     public function display()
    {
         $this -> this_var_name = $this -> getVarName();
         $this -> this_var_name_page = $this -> this_var_name . '_page';

         $this->getFileds();
                  $this->imageJS = $this->getImageJS(  ) ;
        // echo $this->imagesJS;

         require('quickadm_json_jqgrid.php');

         return $this->result;
    }
	
     public function getFileds()
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
		foreach($this->jsona["data"] as $key=>$field)
		{
		  //var_dump($field);
		  $this->fields[] = $field["name"];
		  $this->fieldDisplays[]= $field["desc"];
          $this->displaynames .=  "'". $field["desc"] . "',";
          if( $field[ "edittype" ] == "file")
          {
              $this->fileFilds[] = $field["name"];
          }
		}
		$this->displaynames = substr($this->displaynames,0,-1);
		
		$this->queryurl .= '&tn=' . $this->jsona["table"];
		$this->queryurl .= '&fk=' . 'id';
		$this->queryurl .= '&fd=' . implode(",",$this->fields);
		
		$this->editurl .= '&tn=' . $this->jsona["table"];
		$this->editurl .= '&fk=' . 'id';
		$this->editurl .= '&fd=' .  implode(",",$this->fields);
		
        $this->modelstring = json_encode($this->jsona["data"]);
        $this->modelstring = str_replace("\"fileupload\"", "fileupload", $this->modelstring);
        $this->modelstring = str_replace("\"fileuploadvalue\"", "fileuploadvalue", $this->modelstring);

		//echo $this->modelstring ;
        }
		
		private function isTableExsit()
		{
			$row=$this -> db ->getAll('SHOW TABLES;');

			//var_dump($row);
			foreach($row as $key=>$field)
		   {
		     if(in_array($this->tablename,array_values($field)))
			{
              return true;
			}
			
		   }
		   return false;
		}
		
		private function createTable()
		{
		   $sql = "create table " . $this->tablename . " (";
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
			 
			 if($field["name"] == 'id')
			 {
				$sql .=  " auto_increment ";
			 }
			 $sql .=  ",";
		   }
		   $sql .=" PRIMARY KEY  (id)) ENGINE=MyISAM;";
		   
		   $this -> db ->query($sql);
		}

        private function getImageJS(  )
{ 
    if( count( $this->fileFilds ) == 0)
    {
            return "";
    }
    $header = sprintf(<<<EOT
onInitializeForm : function(formid)
{
$.extend({
hello:function(){alert('hello');}
});
jQuery.extend({
	

    createUploadIframe: function(id, uri)
	{
			//create frame
            var frameId = 'jUploadFrame' + id;
            var iframeHtml = '<iframe id="' + frameId + '" name="' + frameId + '" style="position:absolute; top:-9999px; left:-9999px"';
			if(window.ActiveXObject)
			{
                if(typeof uri== 'boolean'){
					iframeHtml += ' src="' + 'javascript:false' + '"';

                }
                else if(typeof uri== 'string'){
					iframeHtml += ' src="' + uri + '"';

                }	
			}
			iframeHtml += ' />';
			jQuery(iframeHtml).appendTo(document.body);

            return jQuery('#' + frameId).get(0);			
    },
    createUploadForm: function(id, fileElementId, data)
	{
		//create form	
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var form = jQuery('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
		if(data)
		{
			for(var i in data)
			{
				jQuery('<input type="hidden" name="' + i + '" value="' + data[i] + '" />').appendTo(form);
			}			
		}		
		var oldElement = jQuery('#' + fileElementId);
		var newElement = jQuery(oldElement).clone();
		jQuery(oldElement).attr('id', fileId);
		jQuery(oldElement).before(newElement);
		jQuery(oldElement).appendTo(form);


		
		//set attributes
		jQuery(form).css('position', 'absolute');
		jQuery(form).css('top', '-1200px');
		jQuery(form).css('left', '-1200px');
		jQuery(form).appendTo('body');		
		return form;
    },

    ajaxFileUpload: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()        
		var form = jQuery.createUploadForm(id, s.fileElementId, (typeof(s.data)=='undefined'?false:s.data));
		var io = jQuery.createUploadIframe(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;		
        // Watch for a new set of requests
        if ( s.global && ! jQuery.active++ )
		{
			jQuery.event.trigger( "ajaxStart" );
		}            
        var requestDone = false;
        // Create the request object
        var xml = {}   
        if ( s.global )
            jQuery.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function(isTimeout)
		{			
			var io = document.getElementById(frameId);
            try 
			{				
				if(io.contentWindow)
				{
					 xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
                	 xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;
					 
				}else if(io.contentDocument)
				{
					 xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
                	xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}						
            }catch(e)
			{
				jQuery.handleError(s, xml, null, e);
			}
            if ( xml || isTimeout == "timeout") 
			{				
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if ( status != "error" )
					{
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = jQuery.uploadHttpData( xml, s.dataType );    
                        // If a local callback was specified, fire it and pass it the data
                        if ( s.success )
                            s.success( data, status );
    
                        // Fire the global callback
                        if( s.global )
                            jQuery.event.trigger( "ajaxSuccess", [xml, s] );
                    } else
                        jQuery.handleError(s, xml, status);
                } catch(e) 
				{
                    status = "error";
                    jQuery.handleError(s, xml, status, e);
                }

                // The request was completed
                if( s.global )
                    jQuery.event.trigger( "ajaxComplete", [xml, s] );

                // Handle the global AJAX counter
                if ( s.global && ! --jQuery.active )
                    jQuery.event.trigger( "ajaxStop" );

                // Process result
                if ( s.complete )
                    s.complete(xml, status);

                jQuery(io).unbind()

                setTimeout(function()
									{	try 
										{
											jQuery(io).remove();
											jQuery(form).remove();	
											
										} catch(e) 
										{
											jQuery.handleError(s, xml, null, e);
										}									

									}, 100)

                xml = null

            }
        }
        // Timeout checker
        if ( s.timeout > 0 ) 
		{
            setTimeout(function(){
                // Check to see if the request is still happening
                if( !requestDone ) uploadCallback( "timeout" );
            }, s.timeout);
        }
        try 
		{

			var form = jQuery('#' + formId);
			jQuery(form).attr('action', s.url);
			jQuery(form).attr('method', 'POST');
			jQuery(form).attr('target', frameId);
            if(form.encoding)
			{
				jQuery(form).attr('encoding', 'multipart/form-data');      			
            }
            else
			{	
				jQuery(form).attr('enctype', 'multipart/form-data');			
            }			
            jQuery(form).submit();

        } catch(e) 
		{			
            handleError(s, xml, null, e);
        }
		
		jQuery('#' + frameId).load(uploadCallback	);
        return {abort: function () {}};	

    },

    uploadHttpData: function( r, type ) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if ( type == "script" )
            jQuery.globalEval( data );
        // Get the JavaScript object, if JSON is used.
        if ( type == "json" )
            eval( "data = " + data );
        // evaluate scripts within html
        if ( type == "html" )
            jQuery("<div>").html(data).evalScripts();

        return data;
    },
    handleError: function( s, xhr, status, e ) 		{
// If a local callback was specified, fire it
		if ( s.error ) {
			s.error.call( s.context || s, xhr, status, e );
		}

		// Fire the global callback
		if ( s.global ) {
			(s.context ? jQuery(s.context) : jQuery.event).trigger( "ajaxError", [xhr, s, e] );
		}
	}
});


$(formid).attr("method","POST");
$(formid).attr("action","");
$(formid).attr("enctype","multipart/form-data");
EOT
);
    $content = "";
    foreach ($this ->fileFilds as $key => $field ) 
    {
$temp = sprintf(<<<EOT
$("<button class='buttonupload' id='buttonUpload%s'>上传</button>").insertAfter("#%s",formid);
$("<span id='uploadstatus%s'></span>").insertAfter("#buttonUpload%s",formid);

$("<br/><img id='loading' src='statics/images/loading.gif' style='display:none;'>").insertAfter("#buttonUpload%s",formid);
$("#buttonUpload%s",formid).click(function(){
		$("#loading")
		.ajaxStart(function(){
			$(this).show();
		})
		.ajaxComplete(function(){
			$(this).hide();
        });

    $.ajaxFileUpload({
        url:'framework/lib/util/doajaxfileupload.php?fileid=%s',
        secureuri:false,
        fileElementId:'%s',
        dataType: 'json',
        success: function (data, status) {
            if(typeof(data.error) != 'undefined')
            {
                if(data.error != '')
                {
                    alert(data.error);
                }else{
                    var msg= '成功上传文件：' + data.msg;
                    //$("#%s").val('44');
                   
                   $("#uploadstatus%s").text(data.msg);
                    alert(msg);
                }
            }
        },
        error: function (data, status, e)
        {
            alert(e);
        }
    });
return false;


});
EOT
,$field,$field,$field,$field,$field,$field,$field,$field,$field,$field);
$content .= $temp;
    }

    return $header . $content . "}";
}

}
    

     ?>
