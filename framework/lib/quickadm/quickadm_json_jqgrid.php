<?php   
$temp = sprintf(<<<EOT
<table id="%s"></table>
<div id="%s"></div>
<script src="statics/js/ajaxfileupload.js" type="text/javascript"></script>

  <script type="text/javascript">
$.extend( { 
hello:function() { alert('hello');
 }
 } );

</script>


<script type="text/javascript">
function UploadFile(){

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


 $.ajaxFileUpload({
        url:'framework/lib/util/doajaxfileupload.php?fileid=imgFile',
        secureuri:false,
        fileElementId:'imgFile',
        dataType: 'json',
        success: function (data, status) {
            if(typeof(data.error) != 'undefined')
            {
                if(data.error != '')
                {
                    alert(data.error);
                }else{
                    var msg= '成功上传文件：' + data.msg;
                    $("#fileName").val(data.newfile);
                    $("#uploadimage").attr("src", "upload/" + data.newfile);
                    $("#uploadimage").show();
                    //$("#imgFile").hide();
                    alert(msg);
                }
            }
        },
        error: function (data, status, e)
        {
            alert(e);
        }
    });

}
function fileupload(value, editOptions) {
var span = $("<span></span>");
var hiddenValue = $("<input>", { type: "hidden", val: value, name: "fileName", id: "fileName" });
var image = $("<img>", { name: "uploadimage", id: "uploadimage",src:'upload/'+value, style:"width:20px; height:20px" });
var ele = document.createElement("input");
ele.type = "file"
ele.id = "imgFile";
ele.name = "imgFile";
ele.onchange = UploadFile;
span.append(ele).append(hiddenValue ).append(image);
return span;
}

function fileuploadvalue(elem, sg, value)
 {
 return $(elem).find("#fileName").val();
}



jQuery("#%s").jqGrid({
   	url:"%s",
	datatype: "json",
    colNames:[
EOT
,$this->this_var_name,$this->this_var_name_page,$this->this_var_name,$this->queryurl);
$this->result .=$temp;
$this->result.=$this->displaynames;
$this->result.="
],
colModel:";

$this->result.= $this->modelstring;

$this->result.=",";
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
	height:"400px",
    autowidth: true,
	autoheight: true
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

function beforeShowFormFunc(form){
   var dlgDiv = $("#editmod%s");
        var parentDiv = dlgDiv.parent(); 
        var dlgWidth = dlgDiv.width();
        var parentWidth = parentDiv.width();
        var dlgHeight = dlgDiv.height();
        var parentHeight = parentDiv.height();

        var parentTop = parentDiv.offset().top;
        var parentLeft = parentDiv.offset().left;
        dlgDiv[0].style.top =  Math.round(  parentTop  + (parentHeight-dlgHeight)/2  ) + "px";
        dlgDiv[0].style.left = Math.round(  parentLeft + (parentWidth-dlgWidth  )/2 )  + "px";
}  

jQuery("#%s").jqGrid("navGrid","#%s",
EOT
,$this->this_var_name_page,$this->editurl,$this->this_var_name,$this->this_var_name,$this->this_var_name_page);
$this->result.=$temp;
$this->result.= <<<EOT
{},                                   
{width:"100%",height:"100%",recreateForm:true,afterSubmit:processAddEdit,closeAfterAdd:false,closeAfterEdit:false,reloadAfterSubmit:true, top:100,left:300
EOT;
$this->result .= ",beforeShowForm:beforeShowFormFunc";

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
if( $this->imageJS != "" )
    {
        $this->result.= "," . $this->imageJS;
    }
$this->result.=<<<EOT
},
{width:"100%",height:"100%",recreateForm:true,afterSubmit:processAddEdit,closeAfterAdd:false,closeAfterEdit:false,reloadAfterSubmit:true, top:100,left:300
,beforeShowForm:beforeShowFormFunc
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
if( $this->imageJS != "" )
    {
        $this->result.= "," . $this->imageJS;
    }

$this->result.=<<<EOT
}, 
{reloadAfterSubmit:true,width:"100%",height:"100%"
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
{multipleSearch:true,closeOnEscape:true,closeAfterSearch:true,beforeShowForm:beforeShowFormFunc,width:"100%",height:"100%"
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

jQuery("#%s").jqGrid('navGrid','hideCol',"id");
</script>
EOT
,$this->this_var_name,$this->this_var_name);
$this->result.=$temp;



?>
