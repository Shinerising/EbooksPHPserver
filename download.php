<?php
	
	require_once ("config.php");
	
	$url=$_SERVER[REQUEST_URI];
	$urlarray=explode('name=',$url);
	$name=$urlarray[1];
	$n=$_GET['n'];
	$filename = $config['download_directory'].$name;
	
    $fname = basename($filename);
	
	try
	{
	set_time_limit (24 * 60 * 60);      
	$destination_folder = 'file/';           
	$newfname = $destination_folder . $fname; 
	$m1 = md5_file($filename);
	$m2 = md5_file($newfname);
	if($m1 == $m2){
	}
	else{
	$file = fopen ($filename, "rb");         
	if ($file) {         
		$newf = fopen ($newfname, "wb");         
		if ($newf)         
		while(!feof($file)) {         
			fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );         
		}         
	}         
	if ($file)fclose($file);
	if ($newf)fclose($newf);
	}
	
	if($n==0){
	header("content-type: application/force-download");
	header("content-disposition: attachment; filename=".$fname);
	readfile($newfname);
	}
	else if($n==1){
	if (file_exists($newfname)) {  
    header('Content-Description: File Transfer');  
    header('Content-Type: application/octet-stream');  
    header('Content-Disposition: attachment; filename='.$fname);  
    header('Content-Transfer-Encoding: binary');  
    header('Expires: 0');  
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');  
    header('Pragma: public');  
    ob_clean();  
    flush();  
    readfile($newfname);  
    exit;  
	}
	}
	else if($n==2){
	$zip = new ZipArchive;
	$res = $zip->open($newfname);
	if ($res === TRUE && !is_dir('books/'.$m2)) {
	$zip->extractTo('books/'.$m2);
	$zip->close();
	echo "$m2";
	} else if(is_dir('books/'.$m2)){
	echo "$m2";
	} else {
	echo "0";
	}
	}
	else if($n==-1){
	echo '{"name":"'.$name.'","url":"'.$filename.'","new url":"'.$newfname.'"}';
	}
	
	}
	catch(Exception $e)
	{
	
	echo "Error!<br>";
	echo $filename." can not download.<br>";
	echo $e;
	
	}

?>