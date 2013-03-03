<?php
/**
*公司：新派网络 www.0769xp.com
*部门：新派营销 www.xp06.com
*项目：xp06(CMS)
*文件： rss_class 生成RSS订阅
**/
define("TIME_ZONE","");
define("FEEDCREATOR_VERSION", "www.0769xp.com");//您的网址
class FeedItem extends HtmlDescribable {
    var $title, $description, $link;
    var $author, $authorEmail, $image, $category, $comments, $guid, $source, $creator;
    var $date;
    var $additionalElements = Array();
}



class FeedImage extends HtmlDescribable {
    var $title, $url, $link;
    var $width, $height, $description;
}



class HtmlDescribable {
    var $descriptionHtmlSyndicated;
    var $descriptionTruncSize;

    function getDescription() {
        $descriptionField = new FeedHtmlField($this->description);
        $descriptionField->syndicateHtml = $this->descriptionHtmlSyndicated;
        $descriptionField->truncSize = $this->descriptionTruncSize;
        return $descriptionField->output();
    }

}


class FeedHtmlField {
    var $rawFieldContent;
    var $truncSize, $syndicateHtml;
    function FeedHtmlField($parFieldContent) {
        if ($parFieldContent) {
            $this->rawFieldContent = $parFieldContent;
        }
    }
    function output() {
        if (!$this->rawFieldContent) {
            $result = "";
        }    elseif ($this->syndicateHtml) {
            $result = "<![CDATA[".$this->rawFieldContent."]]>";
        } else {
            if ($this->truncSize and is_int($this->truncSize)) {
                $result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent),$this->truncSize);
            } else {
                $result = htmlspecialchars($this->rawFieldContent);
            }
        }
        return $result;
    }

}


class UniversalFeedCreator extends FeedCreator {
    var $_feed;
    
    function _setFormat($format) {
        switch (strtoupper($format)) {
            
            case "2.0":
                // fall through
            case "RSS2.0":
                $this->_feed = new RSSCreator20();
                break;
            
            case "0.91":
                // fall through
            case "RSS0.91":
                $this->_feed = new RSSCreator091();
                break;
            
            default:
                $this->_feed = new RSSCreator091();
                break;
        }
        
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            // prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself
            if (!in_array($key, array("_feed", "contentType", "encoding"))) {
                $this->_feed->{$key} = $this->{$key};
            }
        }
    }
    
    function createFeed($format = "RSS0.91") {
        $this->_setFormat($format);
        return $this->_feed->createFeed();
    }
    
    
    function saveFeed($format="RSS0.91", $filename="", $displayContents=true) {
        $this->_setFormat($format);
        $this->_feed->saveFeed($filename, $displayContents);
    }


   function useCached($format="RSS0.91", $filename="", $timeout=3600) {
      $this->_setFormat($format);
      $this->_feed->useCached($filename, $timeout);
   }

}


class FeedCreator extends HtmlDescribable {
    var $title, $description, $link;
    var $syndicationURL, $image, $language, $copyright, $pubDate, $lastBuildDate, $editor, $editorEmail, $webmaster, $category, $docs, $ttl, $rating, $skipHours, $skipDays;
    var $xslStyleSheet = "";
	var $cssStyleSheet  = "";
    var $items = Array();
    var $contentType = "application/xml";
    var $encoding = "utf-8";
    var $additionalElements = Array();
  
    function addItem($item) {
        $this->items[] = $item;
    }

     function clearItem2Null() {
        $this->items = array();
    }    
    

    function iTrunc($string, $length) {
        if (strlen($string)<=$length) {
            return $string;
        }
        
        $pos = strrpos($string,".");
        if ($pos>=$length-4) {
            $string = substr($string,0,$length-4);
            $pos = strrpos($string,".");
        }
        if ($pos>=$length*0.4) {
            return substr($string,0,$pos+1)." ...";
        }
        
        $pos = strrpos($string," ");
        if ($pos>=$length-4) {
            $string = substr($string,0,$length-4);
            $pos = strrpos($string," ");
        }
        if ($pos>=$length*0.4) {
            return substr($string,0,$pos)." ...";
        }
        
        return substr($string,0,$length-4)." ...";
            
    }
    
    
    function _createGeneratorComment() {
        return "<!-- generator=\"".FEEDCREATOR_VERSION."\" -->\n";
    }
    
    function _createAdditionalElements($elements, $indentString="") {
        $ae = "";
        if (is_array($elements)) {
            foreach($elements AS $key => $value) {
                $ae.= $indentString."<$key>$value</$key>\n";
            }
        }
        return $ae;
    }
    
    function _createStylesheetReferences() {
        $xml = "";
        if ($this->cssStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->cssStyleSheet."\" type=\"text/css\"?>\n";
        if ($this->xslStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->xslStyleSheet."\" type=\"text/xsl\"?>\n";
        return $xml;
    }
    
    function createFeed() {
    }
    

    function _generateFilename() {
        $fileInfo = pathinfo($_SERVER["PHP_SELF"]);
        return substr($fileInfo["basename"],0,-(strlen($fileInfo["extension"])+1)).".xml";
    }
    
    

    function _redirect($filename) {

        Header("Content-Type: ".$this->contentType."; charset=".$this->encoding."; filename=".basename($filename));
        Header("Content-Disposition: inline; filename=".basename($filename));
        readfile($filename, "r");
        die();
    }
    

    function useCached($filename="", $timeout=3600) {
        $this->_timeout = $timeout;
        if ($filename=="") {
            $filename = $this->_generateFilename();
        }
        if (file_exists($filename) AND (time()-filemtime($filename) < $timeout)) {
            $this->_redirect($filename);
        }
    }
    
    

    function saveFeed($filename="", $displayContents=true) {
        if ($filename=="") {
            $filename = $this->_generateFilename();
        }
        $feedFile = fopen($filename, "w+");
        if ($feedFile) {
            fputs($feedFile,$this->createFeed());
            fclose($feedFile);
            if ($displayContents) {
                $this->_redirect($filename);
            }
        } else {
            echo "<br /><b>Error creating feed file, please check write permissions.</b><br />";
        }
    }
    
}



class FeedDate {
    var $unix;
    function FeedDate($dateString="") {
        if ($dateString=="") $dateString = date("r");
        
        if (is_integer($dateString)) {
            $this->unix = $dateString;
            return;
        }
        if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$dateString,$matches)) {
            $months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
            $this->unix = mktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);
            if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
                $tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
            } else {
                if (strlen($matches[7])==1) {
                    $oneHour = 3600;
                    $ord = ord($matches[7]);
                    if ($ord < ord("M")) {
                        $tzOffset = (ord("A") - $ord - 1) * $oneHour;
                    } elseif ($ord >= ord("M") AND $matches[7]!="Z") {
                        $tzOffset = ($ord - ord("M")) * $oneHour;
                    } elseif ($matches[7]=="Z") {
                        $tzOffset = 0;
                    }
                }
                switch ($matches[7]) {
                    case "UT":
                    case "GMT":    $tzOffset = 0;
                }
            }
            $this->unix += $tzOffset;
            return;
        }
        if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$dateString,$matches)) {
            $this->unix = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
            if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
                $tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
            } else {
                if ($matches[7]=="Z") {
                    $tzOffset = 0;
                }
            }
            $this->unix += $tzOffset;
            return;
        }
        $this->unix = 0;
    }


    function rfc822() {
        $date = gmdate("Y-m-d H:i:s", $this->unix);
        if (TIME_ZONE!="") $date .= " ".str_replace(":","",TIME_ZONE);
        return $date;
    }
    

    function iso8601() {
        $date = gmdate("Y-m-d H:i:s",$this->unix);
        $date = substr($date,0,22) . ':' . substr($date,-2);
        if (TIME_ZONE!="") $date = str_replace("+00:00",TIME_ZONE,$date);
        return $date;
    }
    
    function unix() {
        return $this->unix;
    }
}



class RSSCreator10 extends FeedCreator {
    function createFeed() {     
        $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
        //$feed.= $this->_createGeneratorComment();
        if ($this->cssStyleSheet=="") {
            $cssStyleSheet = "http://www.w3.org/2000/08/w3c-synd/style.css";
        }
        $feed.= $this->_createStylesheetReferences();
        $feed.= "<rdf:RDF\n";
        $feed.= "    xmlns=\"http://purl.org/rss/1.0/\"\n";
        $feed.= "    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n"; 
        $feed.= "    xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\"\n";
        $feed.= "    xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
        $feed.= "    <channel rdf:about=\"".$this->syndicationURL."\">\n";
        $feed.= "        <title>".htmlspecialchars($this->title)."</title>\n";
        $feed.= "        <description>".htmlspecialchars($this->description)."</description>\n";
        $feed.= "        <link>".$this->link."</link>\n";
        if ($this->image!=null) {
            $feed.= "        <image rdf:resource=\"".$this->image->url."\" />\n";
        }
        $now = new FeedDate();
        $feed.= "       <dc:date>".htmlspecialchars($now->iso8601())."</dc:date>\n";
        $feed.= "        <items>\n";
        $feed.= "            <rdf:Seq>\n";
        for ($i=0;$i<count($this->items);$i++) {
            $feed.= "                <rdf:li rdf:resource=\"".htmlspecialchars($this->items[$i]->link)."\"/>\n";
        }
        $feed.= "            </rdf:Seq>\n";
        $feed.= "        </items>\n";
        $feed.= "    </channel>\n";
        if ($this->image!=null) {
            $feed.= "    <image rdf:about=\"".$this->image->url."\">\n";
            $feed.= "        <title>".$this->image->title."</title>\n";
            $feed.= "        <link>".$this->image->link."</link>\n";
            $feed.= "        <url>".$this->image->url."</url>\n";
            $feed.= "    </image>\n";
        }
        $feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
        
        for ($i=0;$i<count($this->items);$i++) {
            $feed.= "    <item rdf:about=\"".htmlspecialchars($this->items[$i]->link)."\">\n";
            //$feed.= "        <dc:type>Posting</dc:type>\n";
            $feed.= "        <dc:format>text/html</dc:format>\n";
            if ($this->items[$i]->date!=null) {
                $itemDate = new FeedDate($this->items[$i]->date);
                $feed.= "        <dc:date>".htmlspecialchars($itemDate->iso8601())."</dc:date>\n";
            }
            if ($this->items[$i]->source!="") {
                $feed.= "        <dc:source>".htmlspecialchars($this->items[$i]->source)."</dc:source>\n";
            }
            if ($this->items[$i]->author!="") {
                $feed.= "        <dc:creator>".htmlspecialchars($this->items[$i]->author)."</dc:creator>\n";
            }
            $feed.= "        <title>".htmlspecialchars(strip_tags(strtr($this->items[$i]->title,"\n\r","  ")))."</title>\n";
            $feed.= "        <link>".htmlspecialchars($this->items[$i]->link)."</link>\n";
            $feed.= "        <description>".htmlspecialchars($this->items[$i]->description)."</description>\n";
            $feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            $feed.= "    </item>\n";
        }
        $feed.= "</rdf:RDF>\n";
        return $feed;
    }
}



class RSSCreator091 extends FeedCreator {
    var $RSSVersion;

    function RSSCreator091() {
        $this->_setRSSVersion("0.91");
        $this->contentType = "application/rss+xml";
    }
    

    function _setRSSVersion($version) {
        $this->RSSVersion = $version;
    }


    function createFeed() {
        $feed = "";
        //$feed.= $this->_createGeneratorComment();
        $feed.= $this->_createStylesheetReferences();
        $feed.= "<rss version=\"".$this->RSSVersion."\">\n"; 
        $feed.= "    <channel>\n";
        $feed.= "        <title>".FeedCreator::iTrunc(htmlspecialchars($this->title),100)."</title>\n";
        $this->descriptionTruncSize = 500;
        $feed.= "        <description>".$this->getDescription()."</description>\n";
        $feed.= "        <link>".$this->link."</link>\n";
        $now = new FeedDate();
        $feed.= "        <lastBuildDate>".htmlspecialchars($now->rfc822())."</lastBuildDate>\n";
        $feed.= "        <generator>".FEEDCREATOR_VERSION."</generator>\n";

        if ($this->image!=null) {
            $feed.= "        <image>\n";
            $feed.= "            <url>".$this->image->url."</url>\n"; 
            $feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars($this->image->title),100)."</title>\n"; 
            $feed.= "            <link>".$this->image->link."</link>\n";
            if ($this->image->width!="") {
                $feed.= "            <width>".$this->image->width."</width>\n";
            }
            if ($this->image->height!="") {
                $feed.= "            <height>".$this->image->height."</height>\n";
            }
            if ($this->image->description!="") {
                $feed.= "            <description>".$this->image->getDescription()."</description>\n";
            }
            $feed.= "        </image>\n";
        }
        if ($this->language!="") {
            $feed.= "        <language>".$this->language."</language>\n";
        }
        if ($this->copyright!="") {
            $feed.= "        <copyright>".FeedCreator::iTrunc(htmlspecialchars($this->copyright),100)."</copyright>\n";
        }
        if ($this->editor!="") {
            $feed.= "        <managingEditor>".FeedCreator::iTrunc(htmlspecialchars($this->editor),100)."</managingEditor>\n";
        }
        if ($this->webmaster!="") {
            $feed.= "        <webMaster>".FeedCreator::iTrunc(htmlspecialchars($this->webmaster),100)."</webMaster>\n";
        }
        if ($this->pubDate!="") {
            $pubDate = new FeedDate($this->pubDate);
            $feed.= "        <pubDate>".htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
        }
        if ($this->category!="") {
            $feed.= "        <category>".htmlspecialchars($this->category)."</category>\n";
        }
        if ($this->docs!="") {
            $feed.= "        <docs>".FeedCreator::iTrunc(htmlspecialchars($this->docs),500)."</docs>\n";
        }
        if ($this->ttl!="") {
            $feed.= "        <ttl>".htmlspecialchars($this->ttl)."</ttl>\n";
        }
        if ($this->rating!="") {
            $feed.= "        <rating>".FeedCreator::iTrunc(htmlspecialchars($this->rating),500)."</rating>\n";
        }
        if ($this->skipHours!="") {
            $feed.= "        <skipHours>".htmlspecialchars($this->skipHours)."</skipHours>\n";
        }
        if ($this->skipDays!="") {
            $feed.= "        <skipDays>".htmlspecialchars($this->skipDays)."</skipDays>\n";
        }
        $feed.= $this->_createAdditionalElements($this->additionalElements, "    ");

        for ($i=0;$i<count($this->items);$i++) {
            $feed.= "        <item>\n";
            $feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)),100)."</title>\n";
            $feed.= "            <link>".htmlspecialchars($this->items[$i]->link)."</link>\n";
            $feed.= "            <description>".$this->items[$i]->getDescription()."</description>\n";
            
            if ($this->items[$i]->author!="") {
                $feed.= "            <author>".htmlspecialchars($this->items[$i]->author)."</author>\n";
            }
            /*
            // on hold
            if ($this->items[$i]->source!="") {
                    $feed.= "            <source>".htmlspecialchars($this->items[$i]->source)."</source>\n";
            }
            */
            if ($this->items[$i]->category!="") {
                $feed.= "            <category>".htmlspecialchars($this->items[$i]->category)."</category>\n";
            }
            if ($this->items[$i]->comments!="") {
                $feed.= "            <comments>".htmlspecialchars($this->items[$i]->comments)."</comments>\n";
            }
            if ($this->items[$i]->date!="") {
            $itemDate = new FeedDate($this->items[$i]->date);
                $feed.= "            <pubDate>".htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
            }
            if ($this->items[$i]->guid!="") {
                $feed.= "            <guid>".htmlspecialchars($this->items[$i]->guid)."</guid>\n";
            }
            $feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            $feed.= "        </item>\n";
        }
        $feed.= "    </channel>\n";
        $feed.= "</rss>\n";
        return $feed;
    }
}


class RSSCreator20 extends RSSCreator091 {

    function RSSCreator20() {
        parent::_setRSSVersion("2.0");
    }
    
}

/** 应用实例
*require("lib/rss_class.php");
*require("lib/conn.php");

*$db=db_connect();
*$brs=mysql_query('select * from zhaobiao order by subdate desc limit 0,100',$db);

*$rss = new UniversalFeedCreator(); 
*$rss->title = "页面标题"; 
*$rss->link = "网址http://";
*$rss->description = "rss标题"; 

*while($rowbrs=mysql_fetch_array($brs)){
*$item = new FeedItem(); 
*$item->title =$rowbrs['title']; 
*$item->link = 'http://网址/文件?参数='.$rowbrs['id']); 
*$item->description =$rowbrs['mess']; 
*$rss->addItem($item);
*}
*db_close();
*$rss->saveFeed("RSS2.0", "rss.xml");
**/