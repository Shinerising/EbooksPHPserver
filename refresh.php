<?php
	set_time_limit (24 * 60 * 60);      
	$destination_folder = $config['local_directory'];      
	$url = $config['calibre_directory'] . 'metadata.db';         
	$newfname = $destination_folder . basename($url); 
	$m1 = md5_file($url);
	$m2 = md5_file($newfname);
	if($m1 == $m2){
	}
	else{
	$file = fopen ($url, "rb");         
	if ($file) {         
		$newf = fopen ($newfname, "wb");         
		if ($newf)         
		while(!feof($file)) {         
			fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );         
		}         
	}         
	if ($file) {         
		fclose($file);         
	}         
	if ($newf) {         
		fclose($newf);         
	}
	}	
?>