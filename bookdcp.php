<?php
/**
 * COPS (Calibre OPDS PHP Server) book share script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Apollo Wayne <http://wayshine.us>
 *
 * This script is used to provide a detail page of book for sharing in Google+.
 */

require_once ("config.php");
require_once ("book.php");

$book = Book::getBookById($_GET["id"]);
$authors = $book->getAuthors ();
$tags = $book->getTags ();
$serie = $book->getSerie ();
$book->getLinkArray ();
 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
window.location.href="http://ebooks.wayshine.us/index.php?page=4&s=<?php echo $book->id ?>";
</script>
</head>
<body>
<div class="bookpopup">
    <div class="booke">
        <div class="cover">
            <img src="<?php echo str_replace(array('%2F','%3A'),array('/',':'), rawurlencode ($book->getFilePath ("jpg"))) ?>" alt="Wayne's Ebooks - " />
        </div>
		<!-- Place this tag in your head or just before your close body tag. -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

<!-- Place this tag where you want the share button to render. -->
<div class="g-plus" data-action="share" data-href="http://ebooks.wayshine.us/bookdetail.php?id=<?php echo $book->id ?>"></div>
        <div class="entryTitle"><?php echo htmlspecialchars ($book->title) ?></div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?>:</span>
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
    </div>
    <div class="clearer" />
    <hr />
    <div class="content" style="max-width:700px;"><?php echo $book->getComment (false) ?></div>
    <hr />
</div>
</body>
</html>