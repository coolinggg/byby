<?php

    class Excel_CSV
    {
        private $lines = array();
	    public function addRow ($row)
        {
		   $this->lines[] = $row;
        }
	    public function generateCSV ($filename = 'csv-export.csv')
        {
		    $fp = fopen($filename, 'a');
			foreach ($this->lines as $num => $row) 
			{
			    foreach ($row as $i => $v) 
				{
				     //$row[$i] = mb_convert_encoding($v,"UTF-8","gb2312");
				    $row[$i] = iconv('utf-8', 'GB2312', $v);
				}
				fputcsv($fp, $row);
			}
			fclose($fp);
			echo "window.location =\"/".$filename."\";"; 
		}

        public function parserCSV ($filename)
        {
		    $lines = array();
			$file = fopen($filename,'r');
			while($csv_line=fgetcsv($file))
			{
			    foreach($csv_line as $k=>$v)
				{
				  $v=iconv('GB2312', 'utf-8', $v);
				  $csv_line[$k]=$v;
				}
				$lines[]=$csv_line;
			}
			fclose($file);
			return $lines;
		}
	}
?>