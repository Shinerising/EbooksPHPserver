<?php

	class bookinfo{
	
		public $filename;
		public $inf;
		public $readdir;
		public $infourl;
		public $xml;
		public $ncxurl;
		public $ncx;
		public $bookname;
		public $author;
		public $des;
		public $date;
		public $cssfile;
		public $coverimg;
		public $coverpage;
		public $chapterpage=array();
		public $chapternum;
		
		public function __construct($mn, $fname) {
		$this->filename=basename($fname);
		$this->inf=simplexml_load_file("books/".$mn."/META-INF/container.xml");
		foreach($this->inf->rootfiles->rootfile as $file){
			$fpath=explode('/',$file['full-path']);
			$this->readdir="books/".$mn."/".$fpath[0]."/";
			$this->infourl="books/".$mn."/".$file['full-path'];
		}
		$this->xml=simplexml_load_file($this->infourl);
		$this->bookname=$this->xml->metadata->children('dc', true)->title;
		$this->author=$this->xml->metadata->children('dc', true)->creator;
		$this->des=$this->xml->metadata->children('dc', true)->description;
		$this->date=$this->xml->metadata->children('dc', true)->date;
		foreach($this->xml->manifest->item as $item){
			switch ($item['id']){
				case "main-css" : $this->cssfile=$this->readdir.$item['href'];break;
				case "cover-image" : $this->coverimg=$this->readdir.$item['href'];break;
				case "ncx" : $this->ncxurl=$this->readdir.$item['href'];break;
				case "coverpage" : $this->coverpage=$this->readdir.$item['href'];break;
			}
		}
		if($this->ncxurl){
			$this->ncx=simplexml_load_file($this->ncxurl);
			if($this->ncx->navMap->navPoint==null)self::getnode($this->ncx->head->navMap->navPoint);
			else self::getnode($this->ncx->navMap->navPoint);
			//foreach($this->ncx->navMap->navPoint as $chpt){
				//array_push($this->chapterpage, new chapter($chpt['id'],$chpt['playOrder'],$chpt->navLabel->text,$this->readdir.$chpt->content['src']));
			//}
			$this->chapternum=count($this->chapterpage);
		}
		}
		
		public function getnode($chptnodes){
			foreach($chptnodes as $chpt){
				array_push($this->chapterpage, new chapter($chpt['id'],$chpt['playOrder'],$chpt->navLabel->text,$this->readdir.$chpt->content['src']));
				if($chpt->navPoint!=null){
					self::getnode($chpt->navPoint);
				}
			}
		}

	}
	
	class chapter{
		public $id;
		public $order;
		public $title;
		public $url;
		public function __construct($id, $order, $title, $url) {
			$this->id=$id;
			$this->order=$order;
			$this->title=$title;
			$this->url=$url;
		}
	}
	
	require_once ("config.php");
    require_once ("book.php");
    require_once ("data.php");
     
    global $config;
	
    $bookId = getURLParam ("id", NULL);
    $idData = getURLParam ("data", NULL);
    if (is_null ($bookId))
    {
        $rbook = Book::getBookByDataId($idData);
    }
    else
    {
        $rbook = Book::getBookById($bookId);
    }

	require_once("simple_html_dom.php");
	ini_set('memory_limit', '64M');
	
    header ("Content-Type:text/html; charset=utf-8");
	
	$url=$_SERVER[REQUEST_URI];
	if($bookId!=null){
	    foreach ($rbook->getDatas() as $data)
            {
                if($data->format=="EPUB"){
				$name = str_replace ("&", "&amp;", rawurlencode($data->book->relativePath."/".$data->getFilename ()));
				}
			}
	}
	else{
	$urlarray=explode('name=',$url);
	$name=$urlarray[1];
	}
	
	$chapid=$_GET['chapter'];
	
	$m=file_get_contents("http://ebooks.wayshine.us/download.php?n=2&name=$name");
	
	if($m==null){
	echo "Error!";
	}
	else {
	$book = new bookinfo(substr((string)$m,-32),$name);
	
	$chapternow=null;
	$chapterprevious=null;
	$chapternext=null;
	$currectindex=1;
	for($i=0;$book->chapterpage[$i];$i++) {
		if($book->chapterpage[$i]->id==$chapid){
			$chapternow=$book->chapterpage[$i];
			$currectindex=$i+1;
			if($i>0)$chapterprevious=$book->chapterpage[$i-1];
			if($i<$book->chapternum-1)$chapternext=$book->chapterpage[$i+1];
		}
	}
	if($chapternow==null){
	$chapternow=$book->chapterpage[0];
	if($book->chapterpage[1])$chapternext=$book->chapterpage[1];
	}
	}
	
	$page=file_get_html($chapternow->url);
		
	$title=$page->find("title");
	$titlestr=$title[0]->innertext;
	$img=$page->find("img");
	foreach($img as $image) {
		$imgsrc=$image->src;
		if(substr($imgsrc,0,2)=="..")$imgsrc=substr($imgsrc,3);
		$image->setAttribute("data-original",$book->readdir.$imgsrc);
		$image->setAttribute("class","bookimage");
		$image->src="data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==";
	}
	
	$a=$page->find("a");
	foreach($a as $at) {
		if(substr($at->href,0,4)!="http")$at->href="http://ebooks.wayshine.us/read.php?chapter=".pathinfo($at->href, PATHINFO_FILENAME)."&id=$bookId";
	}
	
	$body=$page->find("body");
	$bodystr=$body[0]->innertext;
	
	$page->clear();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=400, initial-scale=0.85, user-scalable=no" />
    <title><?php echo htmlspecialchars ($book->bookname) ?></title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
	<meta name="title" property="og:title" content="<?php echo $book->bookname ?>" />
	<meta name="coverimage" property="og:image" content="http://ebooks.wayshine.us/<?php echo $book->coverimg ?>" />
	<meta name="description" property="og:description" content="Wayne's Ebooks Online Reading" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
    <link rel="stylesheet" type="text/css" href="read.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="<?php echo $book->cssfile ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="style.css" media="screen" />
	<script type="text/javascript">
		function pagescroll(id){
			if(id==0)$("body").animate({scrollTop:0},1000);
			else $("body").animate({scrollTop:$("body").scrollTop()+id*$(window).height()},1000);
		}
	</script>
</head>
<body itemscope="body" itemtype="http://schema.org/Books" screen_capture_injected="true" id="readbody">
<div id="loading" style="display:none">
  <p><img src="images/ajax-loader.gif" alt="waiting"> Please Wait</p>
</div>
<div class="summary" itemprop="description" style="display:none"><?php echo $book->des ?></div>
<div class="container">
    <div class="head" id="aeaoofnhgocdbnbeljkmbjdmhbcokfdb-mousedown">
        <div class="headleft">
            <a href="/index.php">
                <img src="images/home.png?v=0.2.2" alt="Home">
            </a>
        </div>
        <div class="headright" onclick="javascript:showguide()">
            <img id="searchImage" src="images/list.png?v=0.2.2" alt="Settings and menu">
        </div>
        <div class="headcenter">
            <div class="titlebox"><?php echo $book->bookname ?></div>
        </div>
    </div>
	<div class="readbook">
		<div id="bookarea" onclick="javascript:pagescroll(0.8)">
			<?php
				echo $bodystr;
			?>
		</div>
		<div id="bookguide" style="display:none">
			<?php
					echo "<div class='guidehead'><b>Guide</b></div>";
				foreach($book->chapterpage as $chpt) {
					echo "<div class='sline'></div>";
					echo "<div class='guidelist'><a href='http://ebooks.wayshine.us/read.php?chapter=".(string)$chpt->id."&id=$bookId'>".(string)$chpt->title."</a></div>";
				}
			?>
		</div>
    <div class="foot" id="footbar">
		<div class="footleft"></div>
        <div class="footright">
            <a class="fancyabout" target="_blank" href="about.html"><img src="images/info.png?v=0.2.2" alt="Home"></a>
        </div>
		<div class="footcenter">
		<?php
			if($chapterprevious)echo "<a href='http://ebooks.wayshine.us/read.php?chapter=".(string)$chapterprevious->id."&id=$bookId'><img src='images/previous.png' alt='Previous' /></a>";
			echo "&nbsp;" . $currectindex . " / " . $book->chapternum . "&nbsp;";
			if($chapternext)echo "<a href='http://ebooks.wayshine.us/read.php?chapter=".(string)$chapternext->id."&id=$bookId'><img src='images/next.png' alt='Next' /></a>";
		?>
		</div>
		<div class="footback" onclick="javascript:pagescroll(0)"></div>
    </div>
</div>


</div>
<div id="ytCinemaMessage" style="display: none;"></div><div><object id="ClCache" click="sendMsg" host="" width="0" height="0"></object></div>
	<script type="text/javascript">
	
	$("img.bookimage").lazyload({effect:"fadeIn"});
		
	var guideshow = false;
	var guideheight = $('#bookguide').css('height');
	
		function showguide()
		{
		if(guideshow==false){
			$('#bookguide').css('opacity',.2);
			$('#bookguide').css('top',-48);
			$('#bookguide').css('width',28);
			$('#bookguide').css('height',8);
			$('#bookguide').show();
			$('#bookguide').animate({
				opacity: 1,
				top: '0',
				width: '300',
				height: guideheight
			}, 500, function() {
				guideshow=true;
			});
		}
		else{
			$('#bookguide').animate({
				opacity: .2,
				top: '-48',
				width: '28',
				height: '8'
			}, 500, function() {
				guideshow=false;
				$('#bookguide').hide();
			});
		}
		}
		
		var lastScrollTop = 0;
		$(window).scroll(function(event){
			var st = $(this).scrollTop();
			if (st>$("body").height()-$(this).height()-12){
				$("#footbar").removeClass("f1");
				$("#footbar").addClass("f2");
			}
			else if (st > lastScrollTop){
				$("#footbar").removeClass("f2");
				$("#footbar").addClass("f1");
			}
			else {
				$("#footbar").removeClass("f1");
				$("#footbar").addClass("f2");
			}
			lastScrollTop = st;
		});
	</script>
</body>
</html>