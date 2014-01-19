<!DOCTYPE html>
<?
require('../lib/class.NovelList.php');
$oNovelList = new NovelList();
if(isset($_GET['user'])){
    $oNovelList->setUser($_GET['user']);
}

?>
<html>
<head>
    <meta charset="utf-8" />
    <title>100 Best Novels of the 20th Century</title>
    <link rel="stylesheet" type="text/css" href="http://pother.ca/CssBase/css/ribbon.css"/>
    <link rel="stylesheet" type="text/css" href="base.css"/>
</head>

<body>
    <h1>100 Best Novels of the 20th Century</h1>
    <div class="page">
        <p>
            The following list combines both the Readers and the Board list of the
            <a href="http://www.modernlibrary.com/top-100/100-best-novels/">Modern
            Library top-100-best-novels lists</a>, re-indexed by combining the
            position of books on both lists.  Because of this, several novels may
            occupy the same space. When this occurs, the novels are not displayed
            in any particular order.
        </p>
        <p>
            All novels have been color coded to state in which list they originally appeared:
        </p>
        <ul>
            <li class="boardreaders"><span class="novel">Appears in both the Board and the Readers List</span></li>
            <li class="board"><span class="novel">Appears only in the Board List</span></li>
            <li class="readers"><span class="novel">Appears only in the Readers List</span></li>
        </ul>

        <p>
            Books that are available for free from <a href="//www.gutenberg.org"
            >Project Gutenberg</a> have been marked in bold.
        </p>

        <ol>
            <?=  $oNovelList->htmlList() ?>
        </ol>

    </div>
    <div class="legend">
        <h2>Novels</h2>
        <ul>
            <li id="both">appearing in both lists</li>
            <li id="total">Total</li>
            <li id="gutenberg_total">
                <hr/>
                available from <a href="//www.gutenberg.org">Project Gutenberg</a>
            </li>
        </ul>

        <?if($oNovelList->readAnyBooks() === true):?>
            <h3>Novels I have Read</h3>
            <ul>
                <li class="boardreaders" id="read_both"><span class="novel">appearing on both list</span> </li>
                <li class="board" id="read_board"><span class="novel">appearing on the board list</span> </li>
                <li class="readers" id="read_readers"><span class="novel">appearing on the readers list</span> </li>
                <li id="read_total">Total</li>
            </ul>
        <?endif?>
    </div>
    <div class="banner banner-left banner-fixed">
        <a href="https://github.com/potherca/100-Best-Novels-of-the-20th-Century/">See the source code</a>
    </div>
</body>
</html>