<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>100 Best Novels of the 20th Century</title>
    <link rel="stylesheet" type="text/css" href="base.css"/>
</head>

<body>
    <h1>100 Best Novels of the 20th Century</h1>

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
        <?=  NovelList::htmlList() ?>
    </ol>

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

    <?if(NovelList::readAnyBooks() === true):?>
    <h3>Novels I have Read</h3>
    <ul>
        <li class="boardreaders" id="read_both"><span class="novel">appearing on both list</span> </li>
        <li class="board" id="read_board"><span class="novel">appearing on the board list</span> </li>
        <li class="readers" id="read_readers"><span class="novel">appearing on the readers list</span> </li>
        <li id="read_total">Total</li>
    </ul>
    <?endif?>
</div>

</body>
</html>
<?
class NovelList
{
//////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected $m_aReadList     = array();
    protected $m_aBoardList    = array();
    protected $m_aReaderList   = array();
    protected $m_aGutenbergList   = array();
    protected $m_aCombinedList = array();

////////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function setBoardList($p_aBoardList)
    {
        $this->m_aBoardList = $p_aBoardList;
    }

    public function getBoardList()
    {
        if(empty($this->m_aBoardList))
        {
            $this->m_aBoardList = $this->retrieveList('Board');
        }
        return $this->m_aBoardList;
    }

    public function setCombinedList($p_aCombinedList)
    {
        $this->m_aCombinedList = $p_aCombinedList;
    }

    public function getCombinedList()
    {
        if(empty($this->m_aCombinedList))
        {
            $this->buildCombinedList();
        }
        return $this->m_aCombinedList;
    }

    public function setReadList($p_aReadList)
    {
        $this->m_aReadList = $p_aReadList;
    }

    public function getReadList()
    {
        if(empty($this->m_aReadList))
        {
            $this->m_aReadList = $this->retrieveList('Read');
        }
        return $this->m_aReadList;
    }

    public function setReaderList($p_aReaderList)
    {
        $this->m_aReaderList = $p_aReaderList;
    }

    public function getReaderList()
    {
        if(empty($this->m_aReaderList))
        {
            $this->m_aReaderList = $this->retrieveList('Reader');
        }
        return $this->m_aReaderList;
    }

    public function setGutenbergList($p_aGutenbergList)
    {
        $this->m_aGutenbergList = $p_aGutenbergList;
    }

    public function getGutenbergList()
    {
        if(empty($this->m_aGutenbergList))
        {
            $this->m_aGutenbergList = $this->retrieveList('Gutenberg');
        }
        return $this->m_aGutenbergList;
    }

////////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    static public function htmlList()
    {
        $oSelf = self::getInstance();

        return $oSelf->buildHtmlList();
    }

    public static function readAnyBooks()
    {
        $bReadAnyBooks =false;

        $oSelf = self::getInstance();

        $aReadList = $oSelf->getReadList();

        if(count($aReadList) > 0){
            $bReadAnyBooks = true;
        }

        return $bReadAnyBooks;
    }

//////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return NovelList
     */
    protected static function getInstance()
    {
        static $oSelf;

        if(!isset($oSelf)){
            $oSelf = new self();
        }

        return $oSelf;
    }

    protected function buildHtmlList(){
        $sContent = '<li>';

        $iPreviousScore = null;

        foreach($this->getCombinedList() as $t_sBook => $t_iScore) {
            if($iPreviousScore && $t_iScore !== $iPreviousScore) {
                $sContent .='</li>' . "\n" . '        <li>';
            }

            list($sNovel, $sAuthor) = explode(' by ', $t_sBook);

            $sClasses = $this->buildHtmlClass($t_sBook);

            $bAvailableFromGutenburg = $this->availableFromGutenburg($t_sBook);

            $sContent .=
                '<div class="' . $sClasses . '">'
                . ($bAvailableFromGutenburg
                    ? '<a href="//www.gutenberg.org/ebooks/search/?query='
                      . $t_sBook
                      . '">'
                    :''
                )
                . '<span class="novel">' . $sNovel . '</span> by '
                . '<span class="author">' . $sAuthor . '</span>'
                . ($bAvailableFromGutenburg
                    ?'</a>'
                    :''
                )
                    . '</div>'
            ;

            $iPreviousScore = $t_iScore;
        }

        $sContent .= '</li>' . "\n";

        return $sContent;
    }

    protected function buildHtmlClass($t_sBook)
    {
        $sList = '';

        if (in_array($t_sBook, $this->getReadList())) {
            $sList .= 'read ';
        }

        if (in_array($t_sBook, $this->getBoardList())) {
            $sList .= 'board';
        }

        if (in_array($t_sBook, $this->getReaderList())) {
            $sList .= 'readers';
        }

        if ($this->availableFromGutenburg($t_sBook) === true) {
            $sList .= ' gutenberg';
        }

        $sList = trim($sList);

        return $sList;
    }

    protected function buildCombinedList()
    {
        $this->reCalc($this->getBoardList());
        $this->reCalc($this->getReaderList());

        arsort($this->m_aCombinedList);
    }

    protected function retrieveList($p_sType)
    {
        $aFileContent = array();

        $sListDirectory = 'lists/';

        switch($p_sType) {
            case 'Read':
                $sFile = $p_sType . 'List.txt';
            break;


            case 'Gutenberg':
                $sFile = $sListDirectory . 'GUTINDEX.ALL.txt';
            break;


            case 'Board':
            case 'Reader':
                $sFile = $sListDirectory . $p_sType . 'List.txt';
            break;


            default:
                throw new InvalidArgumentException('Unknown list type given: "' . $p_sType. '"');
            break;
        }

        if(isset($sFile)){
            $aFileContent = $this->retrieveContentFromFile($sFile);
        }

        return $aFileContent;
    }

    protected function retrieveContentFromFile($sFile)
    {
        $aFileContent = array();

        if(is_readable($sFile)) {
            $aFileContent = file($sFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        return $aFileContent;
    }

    protected function reCalc(Array $p_aSubject)
    {
        foreach ($p_aSubject as $t_iIndex => $t_sBook) {
            $iScore = 100 - (int) $t_iIndex;

            if (isset($this->m_aCombinedList[$t_sBook])) {
                $this->m_aCombinedList[$t_sBook] = $this->m_aCombinedList[$t_sBook] + $iScore;
            }
            else
            {
                $this->m_aCombinedList[$t_sBook] = $iScore;
            }
        }
    }

    protected function availableFromGutenburg($t_sBook)
    {
        $bAvailable = false;

        static $sFileContent;

        if(!isset($sFileContent)){
            $sFileContent = implode("\n", $this->getGutenbergList());
        }

        list($sNovel, $sAuthor) = explode(' by ', $t_sBook);

        $iMatch = preg_match_all(
            '#' . $sNovel . ', by ' . $sAuthor . '#i',
            $sFileContent,
            $aMatches
        );

        if($iMatch > 0) {
            $bAvailable = true;
        }

        return $bAvailable;
    }
}

#EOF
