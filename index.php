<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sastien Lucas <sebastien@slucas.fr>
 *
 */

    require_once ("config.php");
	require_once ("refresh.php");
    require_once ("base.php");
    require_once ("author.php");
    require_once ("serie.php");
    require_once ("tag.php");
    require_once ("book.php");
    
    header ("Content-Type:text/html; charset=utf-8");
    $page = getURLParam ("page", Base::PAGE_INDEX);
    $query = getURLParam ("query");
    $qid = getURLParam ("id");
	$qs = getURLParam ("s", "0");
    $n = getURLParam ("n", "1");
    
    $currentPage = Page::getPage ($page, $qid, $query, $n);
    $currentPage->InitializeContent (); 
	
	if($currentPage->title==$config['cops_title_default'])
	{
	$ogtitle=$currentPage->title;
	$ogdes="My Ebook Cloud Library.";
	}
	else if($currentPage->book)
	{
	$ogtitle=$config['cops_title_default']." - 《".htmlspecialchars($currentPage->book->title)."》";
	foreach($currentPage->book->getAuthors() as $author)
	{
	$ogtitle=$ogtitle." ".htmlspecialchars ($author->name);
	}
	$ogdes=$currentPage->book->getComment (false);
	if(mb_strlen($ogdes,'utf-8')>100)$ogdes=mb_substr($ogdes,0,100,'utf-8')."...";
	}
	else
	{
	$ogtitle=$config['cops_title_default']." - ".htmlspecialchars ($currentPage->title);
	$ogdes="My Ebook Cloud Library.";
	}

/* Test to see if pages are opened on an Eink screen 
 * First test Kindle or Kobo Touch */

	if (preg_match("/(Kobo Touch|Kindle\/3.0)/", $_SERVER['HTTP_USER_AGENT'])) {
		$isEink = 1;

/* Test Sony PRS-T1 Ereader. 
   HTTP_USER_AGENT = "Mozilla/5.0 (Linux; U; en-us; EBRD1101; EXT) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"

*/
	
	} else if (preg_match("/EBRD1101/i", $_SERVER['HTTP_USER_AGENT'])) {
		$isEink = 1;
	
/* No Eink screens found */
	} else {
		$isEink = 0;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="imagetoolbar" content="no" />
	<meta name="viewport" content="width=400, initial-scale=0.85, user-scalable=no" />
    <title><?php echo htmlspecialchars ($currentPage->title) ?></title>
	<meta name="title" property="og:title" content="<?php echo $ogtitle ?>" />
	<meta name="description" property="og:description" content="Wayne's Ebooks" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.lazyload.min.js"></script>
    <script type="text/javascript" src="<?php echo getUrlWithVersion("js/jquery.sortElements.js") ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo getUrlWithVersion("style.css") ?>" media="screen" />
    <script type="text/javascript">
        $(document).ready(function() {
            
            $("#sort").click(function(){
                $('.book').sortElements(function(a, b){
                    var test = 1;
                    if ($("#sortorder").val() == "desc")
                    {
                        test = -1;
                    }
                    return $(a).find ("." + $("#sortchoice").val()).text() > $(b).find ("." + $("#sortchoice").val()).text() ? test : -test;
                });
                $("#search").slideUp();
            });
            
            $("#searchImage").click(function(){
                if ($("#search").is(":hidden")) {
                    $("#search").slideDown("slow");
                } else {
                    $("#search").slideUp();
                }
            });
        });
		
		function readbook(s1,s2)
		{
			$('#readbutton').text("Please waiting...");
			$.get(s1, function(data) {
				document.location.href=s2;
				$('#readbutton').text("Redirecting...");
			});
		}
<?php
    if ($currentPage->isPaginated ()) {
        $prevLink = $currentPage->getPrevLink ();
        $nextLink = $currentPage->getNextLink ();
?>
        $(document).keydown(function(e){
<?php
        if (!is_null ($prevLink)) {
            echo "if (e.keyCode == 37) {\$(location).attr('href','" . $prevLink->hrefXhtml () . "');}"; 
        }
        if (!is_null ($nextLink)) {
            echo "if (e.keyCode == 39) {\$(location).attr('href','" . $nextLink->hrefXhtml () . "');}"; 
        }

?>
        });
<?php
    }
?> 
    </script>
</head>
<body itemscope itemtype="http://schema.org/Books">
<div id="loading">
  <p><img src="images/ajax-loader.gif" alt="waiting" /> Please Wait</p>
</div>

<div class="container">
    <div class="head">
        <div class="headleft">
            <a href="<?php echo $_SERVER["SCRIPT_NAME"] ?>">
                <img src="<?php echo getUrlWithVersion("images/home.png") ?>" alt="Home" />
            </a>
        </div>
        <div class="headright">
            <img id="searchImage" src="<?php echo getUrlWithVersion("images/setting64.png") ?>" alt="Settings and menu" />
        </div>
        <div class="headcenter">
            <p><?php echo htmlspecialchars ($currentPage->title) ?></p>
        </div>
    </div>
    <div class="clearer"></div>
    <div class="menu">
        <div id="search" class="search">
            <form action="index.php?page=9" method="get">
                <input type="text" name="query" style=" width: 184px; height: 24px; border-radius: 10px; border: none; "></input>
                <input type="hidden" name="page" value="9"></input>
                <input type="image" src="images/search32.png" alt="Search"></input>
            </form>
            <form action="index.php?page=9" method="get">
                <select id="sortchoice" style=" height: 24px; width: 85px; border-radius: 10px; ">
                    <option value="st"><?php echo localize("bookword.title") ?></option>
                    <option value="sa"><?php echo localize("authors.title") ?></option>
                    <option value="ss"><?php echo localize("series.title") ?></option>
                    <option value="sp"><?php echo localize("content.published") ?></option>
                </select>
                <select id="sortorder" style=" height: 24px; width: 85px; border-radius: 10px; ">
                    <option value="asc">Asc</option>
                    <option value="desc">Desc</option>
                </select> 
                <img id="sort" src="images/sort32.png" alt="Sort" />
            </form>
        </div>
    </div>
    <div class="clearer"></div>
    <div id="content" style="display: none;"></div>
		<?php
			if($currentPage->book){
			$book = $currentPage->book;
			$authors = $book->getAuthors ();
			$tags = $book->getTags ();
			$serie = $book->getSerie ();
			$book->getLinkArray ();
		?>
	<div class="bookpopup">
    <div class="detailarea">
        <div class="detailcover">
            <img itemprop="image" class="detailimg" src="fetch.php?id=<?php echo $book->id ?>&width=288" alt="cover" />
        </div>
		<div class="detail">
		<div class="booktitle"><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
<?php
            foreach ($book->getDatas() as $data)
            {
                if($data->format=="EPUB"){
?>
					<div class='buttonEffect pad6'><a href='#' id='readbutton' onclick='javascript:readbook("<?php echo $data->getReadLink(0) ?>","<?php echo $data->getReadLink(1) ?>")'>Read Ebook Online</a></div>
<?php
				}
            }
?>
        </div>
        <div class="entrySection">
			<span>Download </span>
<?php
            foreach ($book->getDatas() as $data)
            {
?>    
                <div class="buttonEffect pad6"><a target="_blank" href="<?php echo $data->getHtmlLink () ?>"><?php echo $data->format ?></a></div>
<?php
            }
?>
        </div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?> </span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($authors as $author) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $author->getUri ()) ?>"><?php echo htmlspecialchars ($author->name) ?></a>
<?php
            }
?>
            </div>
        </div>
        <div class="entrySection">
            <span><?php echo localize("tags.title") ?> </span>
            <div class="buttonEffect pad6">
<?php
            $i = 0;
            foreach ($tags as $tag) {
                if ($i > 0) echo ", ";
?>
                <a href="index.php<?php echo str_replace ("&", "&amp;", $tag->getUri ()) ?>"><?php echo htmlspecialchars ($tag->name) ?></a>
<?php
            }
?>
            </div>
        </div>
<?php
        if (!is_null ($serie))
        {
?>
        <div class="entrySection">
            <div class="buttonEffect pad6">
                <a href="index.php<?php echo str_replace ("&", "&amp;", $serie->getUri ()) ?>"><?php echo localize("series.title") ?></a>
            </div>
            <?php echo str_format (localize ("content.series.data"), $book->seriesIndex, htmlspecialchars ($serie->name)) ?>
        </div>
<?php
        }
?>
		<div class="entrySection">
			<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
			<div class="g-plus" data-action="share" data-width="130"></div>
		</div>
		</div>
		<div></div>
    </div>
		<div class="summary" itemprop="description">
		<h2>Summary:</h2>
		<?php echo $book->getComment (false) ?>
		</div>
    <div class="clearer" />
</div>
		<?
			}
		?>
    <div class="entries">
        <?php
            foreach ($currentPage->entryArray as $entry) {
                if (get_class ($entry) != "EntryBook") {
        ?>
        <a href="<?php echo $entry->linkArray[0]->hrefXhtml () ?>" class="entry">
            <div class="entryTitle"><?php echo htmlspecialchars ($entry->title) ?></div>
            <div class="entryContent"><?php echo htmlspecialchars ($entry->content) ?></div>
        <?php
            foreach ($entry->linkArray as $link) {
        ?>
            <a href="<?php echo $link->hrefXhtml () ?>" class="navigation">nav</a>
        <?php
            }
        ?>
        </a>
        <?php
                }
                else
                {
        ?>
        <div class="book">
			<div class="back"></div>
            <div class="bookcover">
            <?php
                if ($entry->book->hasCover) {
            ?>
                <a rel="group" class="fancycover" href="index.php?page=13&id=<?php echo $entry->book->id ?>"><img class="coverimg" src="<?php echo $entry->getCoverThumbnail () ?>" alt="cover" /></a>
            <?php
                }
            ?>
            </div>
            <a class="bookdetail" href="index.php?page=13&id=<?php echo $entry->book->id ?>">
                <div class="bookTitle st"><?php echo htmlspecialchars ($entry->title) ?></div>
                <div class="bookContent sa"><?php echo htmlspecialchars ($entry->book->getAuthorsName ()) ?></div>
			</a>
			<div class="download">
            <?php
                $i = 0;
                foreach ($config['cops_prefered_format'] as $format)
                {
                    if ($i == 2) { break; }
					if($data[$i] = $entry->book->getDataFormat ($format)){
					$fmat[$i] = $format;
					$i++;
					}
				}
				if($i==1)echo '<div class="button buttonEffect"><a target="_blank" href="'.$data[0]->getHtmlLink ().'">'.$fmat[0].'</a></div>';
				else{
				echo '<div class="button buttonEffect" style="float:left"><a target="_blank" href="'.$data[0]->getHtmlLink ().'">'.$fmat[0].'</a></div>';
				echo '<div class="button buttonEffect" style="float:right"><a target="_blank" href="'.$data[1]->getHtmlLink ().'">'.$fmat[1].'</a></div>';
				}
            ?>    
                
            </div>
        </div>
        <?php
                }
        ?>
        <div class="clearer"></div>
        <?php
            }
        ?>
    </div>
    <div class="foot">
		<div class="footleft"></div>
        <div class="footright">
            <a class="fancyabout" target="_blank" href="about.html"><img src="<?php echo getUrlWithVersion("images/info.png") ?>" alt="Home" /></a>
        </div>
<?php
    if ($currentPage->isPaginated ()) {
?> 

        <div class="footcenter">
        <?php
            if (!is_null ($prevLink)) {
        ?>
        <a href="<?php echo $prevLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/previous.png") ?>" alt="Previous" /></a>
        <?php
            }
        ?>
        <p><?php echo "&nbsp;" . $currentPage->n . " / " . $currentPage->getMaxPage () . "&nbsp;" ?></p>
        <?php
            if (!is_null ($nextLink)) {
        ?>
        <a href="<?php echo $nextLink->hrefXhtml () ?>" ><img src="<?php echo getUrlWithVersion("images/next.png") ?>" alt="Next" /></a>
        <?php
            }
        ?>
        </div>
<?php
    }
?>
    </div>
</div>
</body>
</html>
