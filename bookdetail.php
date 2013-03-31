<?php
/**
 * COPS (Calibre OPDS PHP Server) book detail script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

require_once ("config.php");
require_once ("book.php");

$book = Book::getBookById($_GET["id"]);
$authors = $book->getAuthors ();
$tags = $book->getTags ();
$serie = $book->getSerie ();
$book->getLinkArray ();
 
?>
<div class="bookpopup">
    <div class="booke">
        <div class="detailcover">
            <img src="fetch.php?id=<?php echo $book->id ?>&amp;height=220" alt="cover" />
        </div>
	<div class="booktitle"><?php echo htmlspecialchars ($book->title) ?></div>
		<!-- Place this tag in your head or just before your close body tag. -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

<!-- Place this tag where you want the share button to render. -->
<div class="g-plus" data-action="share" data-href="http://ebooks.wayshine.us/bookdcp.php?id=<?php echo $book->id ?>"></div>
        <div class="bookdownload">
<?php
            foreach ($book->getDatas() as $data)
            {
?>    
                <div class="button buttonEffect"><a href="<?php echo $data->getHtmlLink () ?>"><?php echo $data->format ?></a></div>
<?php
            }
?>
        </div>
        <div class="entrySection">
            <span><?php echo localize("authors.title") ?></span>
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
            <span><?php echo localize("tags.title") ?></span>
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
    </div>
    <div class="clearer" />
    <hr />
    <div><?php echo localize("content.summary") ?></div>
    <div class="content" style="max-width:700px;"><?php echo $book->getComment (false) ?></div>
    <hr />
</div>