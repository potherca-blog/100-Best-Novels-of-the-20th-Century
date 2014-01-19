<?

class NovelList
{
//////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected $m_sUser = '';
    protected $m_aUserList = array();
    protected $m_aBoardList = array();
    protected $m_aReaderList = array();
    protected $m_aGutenbergList = array();
    protected $m_aCombinedList = array();

////////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @return string
     */
    public function getUser()
    {
        return $this->m_sUser;
    }

    /**
     * @param string $p_sUser
     */
    public function setUser($p_sUser)
    {
        $this->m_sUser = (string) $p_sUser;
    }

    protected function setBoardList($p_aBoardList)
    {
        $this->m_aBoardList = $p_aBoardList;
    }

    protected function getBoardList()
    {
        if(empty($this->m_aBoardList))
        {
            $this->m_aBoardList = $this->retrieveList('Board');
        }
        return $this->m_aBoardList;
    }

    protected function setCombinedList($p_aCombinedList)
    {
        $this->m_aCombinedList = $p_aCombinedList;
    }

    protected function getCombinedList()
    {
        if(empty($this->m_aCombinedList))
        {
            $this->buildCombinedList();
        }
        return $this->m_aCombinedList;
    }

    protected function setUserList($p_aUserList)
    {
        $this->m_aUserList = $p_aUserList;
    }

    protected function getUserList()
    {
        if(empty($this->m_aUserList))
        {
            $this->m_aUserList = $this->retrieveList('User');
        }
        return $this->m_aUserList;
    }

    protected function setReaderList($p_aReaderList)
    {
        $this->m_aReaderList = $p_aReaderList;
    }

    protected function getReaderList()
    {
        if(empty($this->m_aReaderList))
        {
            $this->m_aReaderList = $this->retrieveList('Reader');
        }
        return $this->m_aReaderList;
    }

    protected function setGutenbergList($p_aGutenbergList)
    {
        $this->m_aGutenbergList = $p_aGutenbergList;
    }

    protected function getGutenbergList()
    {
        if(empty($this->m_aGutenbergList))
        {
            $this->m_aGutenbergList = $this->retrieveList('Gutenberg');
        }
        return $this->m_aGutenbergList;
    }

////////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function htmlList()
    {
        return $this->buildHtmlList();
    }

    public function readAnyBooks()
    {
        $bReadAnyBooks =false;

        $oSelf = self::getInstance();

        $aReadList = $oSelf->getUserList();

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

        if (in_array($t_sBook, $this->getUserList())) {
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

        $sListDirectory = realpath('../lists/').'/';

        switch($p_sType) {
            case 'User':
                $sUser = $this->getUser();
                $sFile = $sListDirectory . 'users/' . $sUser . '.txt';
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