<?php
	
	require_once("simple_html_dom.php");
	ini_set('memory_limit', '64M');
	
	echo "books/e50d439bfa4585ea024c3f98e0a271cc/OPS/chapter1.html";
	
	$file=file_get_html("books/e50d439bfa4585ea024c3f98e0a271cc/OPS/chapter1.html");
	//$file->clear();
	
	echo "now1";
	
	$title=$page->find("title");
	$titlestr=$title[0]->innertext;
	
	echo $titlestr;
	
?>