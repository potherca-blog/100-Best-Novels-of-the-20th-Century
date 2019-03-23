<?php

class NovelList
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\

    protected $m_sUser = '';
    protected $m_aUserList = [];
    protected $m_aBoardList = [];
    protected $m_aReaderList = [];
    protected $m_aGutenbergList = [];
    protected $m_aCombinedList = [];

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\

    protected function getBoardList()
    {
        if (empty($this->m_aBoardList)) {
            $this->m_aBoardList = $this->retrieveList('Board');
        }

        return $this->m_aBoardList;
    }

    protected function getCombinedList()
    {
        if (empty($this->m_aCombinedList)) {
            $this->buildCombinedList();
        }

        return $this->m_aCombinedList;
    }

    protected function getUserList()
    {
        if (empty($this->m_aUserList)) {
            $this->m_aUserList = $this->retrieveList('User');
        }

        return $this->m_aUserList;
    }

    protected function getReaderList()
    {
        if (empty($this->m_aReaderList)) {
            $this->m_aReaderList = $this->retrieveList('Reader');
        }
        return $this->m_aReaderList;
    }

    protected function getGutenbergList()
    {
        if (empty($this->m_aGutenbergList)) {
            $this->m_aGutenbergList = $this->retrieveList('Gutenberg');
        }

        return $this->m_aGutenbergList;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public function __construct($p_sUser = '', array $p_aCensorList = [])
    {
        $this->m_sUser = (string) $p_sUser;
        $this->m_aCensorList = $p_aCensorList;
    }

    public function htmlList()
    {
        return $this->buildHtmlList();
    }

    public function readAnyBooks()
    {
        return count($this->getUserList()) > 0;
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    protected function buildHtmlList(){
        $sContent = '<li>';

        $iPreviousScore = null;

        foreach ($this->getCombinedList() as $t_sBook => $t_iScore) {
            if ($iPreviousScore && $t_iScore !== $iPreviousScore) {
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
        $aFileContent = [];

        $sListDirectory = realpath('../lists/').'/';

        switch($p_sType) {
            case 'User':
                $sFile = $sListDirectory . 'users/' . $this->m_sUser . '.txt';
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

        if (isset($sFile)) {
            $aFileContent = $this->retrieveContentFromFile($sFile);
        }

        return $aFileContent;
    }

    protected function retrieveContentFromFile($sFile)
    {
        $aFileContent = [];

        if (is_readable($sFile)) {
            $aFileContent = file($sFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        return $aFileContent;
    }

    protected function reCalc(Array $p_aSubject)
    {
        foreach ($p_aSubject as $t_iIndex => $t_sBook) {
            if ($this->shouldCensor($t_sBook) === false) {
                $iScore = 100 - (int) $t_iIndex;

                if (isset($this->m_aCombinedList[$t_sBook])) {
                    $this->m_aCombinedList[$t_sBook] = $this->m_aCombinedList[$t_sBook] + $iScore;
                } else {
                    $this->m_aCombinedList[$t_sBook] = $iScore;
                }
            }
        }
    }

    protected function availableFromGutenburg($t_sBook)
    {
        $bAvailable = false;

        static $sFileContent;

        if (! isset($sFileContent)) {
            $sFileContent = implode("\n", $this->getGutenbergList());
        }

        list($sNovel, $sAuthor) = explode(' by ', $t_sBook);

        $iMatch = preg_match_all(
            '#' . $sNovel . ', by ' . $sAuthor . '#i',
            $sFileContent,
            $aMatches
        );

        if ($iMatch > 0) {
            $bAvailable = true;
        }

        return $bAvailable;
    }

    private function shouldCensor($p_sBook)
    {
        list(, $sAuthor) = explode(' by ', $p_sBook, 2);

        return in_array($sAuthor, $this->m_aCensorList);
    }
}

/*EOF*/
