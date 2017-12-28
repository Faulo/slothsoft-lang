<?php 
// © 2012 Daniel Schulz
namespace Slothsoft\Lang;

use Slothsoft\Farah\HTTPDocument;
use Slothsoft\Core\Storage;
use Slothsoft\Core\Game\Dice;
use DOMElement;
use DOMXPath;
use Exception;

/*
 * <vocabulary>
 * <group>
 * <vocable>
 * <word xml:lang="de-de" name="" note="" player-uri=""/>
 * <word xml:lang="ja-ja" name="" note="" player-uri=""/>
 * </vocable>
 * </group>
 * </vocabulary>
 * //
 */

// http://www.csse.monash.edu.au/~jwb/audiock.swf?u=kana=%25E3%2581%2594%26kanji=%25E8%25AA%259E
// http://assets.languagepod101.com/dictionary/japanese/audiomp3.php?kana=%E3%81%94&kanji=%E8%AA%9E
class Vocabulary
{

    const TEST_TYPE_CHOICE = 'choice';

    const TEST_TYPE_TYPING = 'typing';

    const TEST_TYPE_CLICK = 'click';

    const ELE_ROOT = 'vocabulary';

    const ELE_GROUP = 'group';

    const ELE_VOCABLE = 'vocable';

    const ELE_REPOSITORY = 'repository';

    const ELE_WORD = 'word';

    const ELE_READING = 'reading';

    const ELE_SPELLING = 'radical';

    const ATTR_ID = 'id';

    const ATTR_NAME = 'name';

    const ATTR_NOTE = 'note';

    const ATTR_PLAYER = 'player-uri';

    const ATTR_TYPE = 'type';

    const ATTR_LANG = 'xml:lang';

    protected $dataPath;

    protected $dataDoc;

    public $commonWords = true;

    protected $currentTime;

    protected $currentDay;

    protected $wordList = [];

    protected $wordFilter = null;

    protected $progressList = [];

    protected $dateList = [];

    protected $firstLang;

    protected $secondLang;

    public function __construct(DOMXPath $dataPath)
    {
        $this->dataPath = $dataPath;
        $this->dataDoc = $this->dataPath->document;
    }

    public function setTime($time)
    {
        $this->currentTime = $time;
        $this->currentDay = date(DATE_DATE, $this->currentTime);
    }

    public function loadTable(DOMXPath $xpath, DOMElement $tableNode)
    {
        $retNode = $this->dataDoc->createElement(self::ELE_ROOT);
        $this->firstLang = $xpath->evaluate('string(.//*[@xml:lang][1]/@xml:lang)', $tableNode);
        $this->secondLang = $xpath->evaluate('string(.//*[@xml:lang][2]/@xml:lang)', $tableNode);
        
        $groupList = $xpath->evaluate('html:tbody | self::*[html:tr]', $tableNode);
        foreach ($groupList as $groupNode) {
            $rootNode = $this->dataDoc->createElement(self::ELE_GROUP);
            if ($groupNode->hasAttribute(self::ATTR_NAME)) {
                $rootNode->setAttribute(self::ATTR_NAME, $groupNode->getAttribute(self::ATTR_NAME));
                $rootNode->setAttribute(self::ATTR_ID, preg_replace('/[^\w]/u', '', $groupNode->getAttribute(self::ATTR_NAME)));
            }
            $rowList = $xpath->evaluate('html:tr[html:td]', $groupNode);
            foreach ($rowList as $rowNode) {
                if ($wordNode = $this->loadRow($xpath, $rowNode)) {
                    $rootNode->appendChild($wordNode);
                }
            }
            $retNode->appendChild($rootNode);
        }
        $this->calcProgress();
        return $retNode;
    }

    public function loadRow(DOMXPath $xpath, DOMElement $rowNode)
    {
        $exprList = [
            [
                'name' => 'normalize-space(html:td[1])',
                'note' => 'normalize-space(html:td[2])',
                'lang' => sprintf('"%s"', $this->firstLang),
                'player' => 'concat(@audio, html:td[1]/@audio)'
            ],
            [
                'name' => 'normalize-space(html:td[3])',
                'note' => 'normalize-space(html:td[4])',
                'lang' => sprintf('"%s"', $this->secondLang),
                'player' => 'string(html:td[3]/@audio)'
            ]
        ];
        
        $wordNode = $this->dataDoc->createElement(self::ELE_VOCABLE);
        foreach ($exprList as $expr) {
            $data = [];
            foreach ($expr as $key => $val) {
                $data[$key] = trim($xpath->evaluate($val, $rowNode));
            }
            $wordNode->appendChild($this->loadWord($data));
        }
        return $wordNode;
    }

    public function loadWord(array $data)
    {
        $data['name'] = $this->arrayExplode($data['name']);
        $data['note'] = $this->arrayExplode($data['note']);
        // $data['note'] = trim(reset($data['note']));
        if (! $data['name']) {
            $data['name'] = $data['note'];
            $data['note'] = [];
        }
        
        switch ($data['lang']) {
            case 'ja-jp':
                $data['player'] = TranslatorJaEn::downloadPlayerURI($data['player']);
                if (! strlen($data['player'])) {
                    // $data['player'] = TranslatorJaEn::getPlayerURI($data['note'], $data['name']);
                    $data['player'] = TranslatorJaEn::getPlayerURI(reset($data['note']), reset($data['name']));
                }
                $data['name'] = implode('　／　', $data['name']);
                $data['note'] = implode('　／　', $data['note']);
                break;
            default:
                $data['name'] = implode('; ', $data['name']);
                $data['note'] = implode('; ', $data['note']);
                break;
        }
        if ($data['player'] === '?') {
            $data['player'] = '';
        }
        
        $retNode = $this->dataDoc->createElement(self::ELE_WORD);
        $retNode->setAttribute(self::ATTR_NAME, $data['name']);
        $retNode->setAttribute(self::ATTR_NOTE, $data['note']);
        $retNode->setAttribute(self::ATTR_PLAYER, $data['player']);
        $retNode->setAttribute(self::ATTR_LANG, $data['lang']);
        // echo sprintf('%s:%s-%s', $data['lang'], $data['name'], $data['note']) . PHP_EOL . md5(sprintf('%s:%s-%s', $data['lang'], $data['name'], $data['note'])) . PHP_EOL;
        $id = $this->buildIdString($data['lang'], $data['name'], $data['note']);
        $retNode->setAttribute(self::ATTR_ID, $id);
        $retNode->setIdAttribute(self::ATTR_ID, true);
        if (isset($data['reading'])) {
            foreach ($data['reading'] as $type => $readList) {
                foreach ($readList as $read) {
                    $node = $this->dataDoc->createElement(self::ELE_READING);
                    $node->setAttribute(self::ATTR_TYPE, $type);
                    $node->setAttribute(self::ATTR_NAME, $read);
                    $retNode->appendChild($node);
                }
            }
        }
        if (isset($data['spelling'])) {
            foreach ($data['spelling'] as $spell) {
                $node = $this->dataDoc->createElement(self::ELE_SPELLING);
                $node->setAttribute(self::ATTR_NAME, $spell);
                $retNode->appendChild($node);
            }
        }
        $this->wordList[$id] = $retNode;
        return $retNode;
    }

    public function hasVocable($lang, $name, $note)
    {
        $id = $this->buildIdString($lang, $name, $note);
        return isset($this->wordList[$id]);
    }

    public function addVocable(DOMElement $tbodyNode, array $wordList)
    {
        foreach ($wordList as $word) {
            foreach ($word as &$val) {
                $val = trim($val);
            }
            unset($val);
            if (! ($word['name'] or $word['note'])) {
                return false;
            }
            if ($this->hasVocable($word['lang'], $word['name'], $word['note'])) {
                throw new Exception(sprintf('Word %s (%s) is already in the list! oAo', $word['name'], $word['note']));
            }
        }
        $doc = $tbodyNode->ownerDocument;
        $parentNode = $doc->createElementNS(HTTPDocument::NS_HTML, 'tr');
        foreach ($wordList as $word) {
            $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'td');
            $node->appendChild($doc->createTextNode($word['name']));
            $node->setAttribute('audio', $word['audio']);
            $parentNode->appendChild($node);
            $node = $doc->createElementNS(HTTPDocument::NS_HTML, 'td');
            $node->appendChild($doc->createTextNode($word['note']));
            $parentNode->appendChild($node);
        }
        $textNode = $tbodyNode->firstChild->cloneNode(true);
        $tbodyNode->insertBefore($textNode, $tbodyNode->lastChild);
        $tbodyNode->insertBefore($parentNode, $tbodyNode->lastChild);
    }

    public function loadKanjiDocument(DOMXPath $xpath)
    {
        $retNode = $this->dataDoc->createElement(self::ELE_ROOT);
        $groupNode = $this->dataDoc->createElement(self::ELE_GROUP);
        $groupNode->setAttribute(self::ATTR_NAME, 'kanji');
        $retNode->appendChild($groupNode);
        
        $tmpList = $xpath->evaluate('.//html:table[@border]//html:tr[html:td]');
        
        $exprList = [
            [
                'name' => 'normalize-space(html:td[1])',
                'note' => '""',
                'on' => 'normalize-space(html:td[2])',
                'kun' => 'normalize-space(html:td[3])',
                'lang' => '"ja-jp"',
                'player' => '"?"'
            ],
            [
                'name' => 'string(html:td[4])',
                'note' => '""',
                'lang' => '"en-us"',
                'player' => '"?"'
            ]
        ];
        
        foreach ($tmpList as $rowNode) {
            $vocabNode = $this->dataDoc->createElement(self::ELE_VOCABLE);
            foreach ($exprList as $i => $expr) {
                $data = [];
                foreach ($expr as $key => $val) {
                    $data[$key] = trim($xpath->evaluate($val, $rowNode));
                }
                $data['reading'] = [];
                if (isset($data['on'])) {
                    $data['reading']['on'] = $this->kanjiParseReading($data['on']);
                }
                if (isset($data['kun'])) {
                    $data['reading']['kun'] = $this->kanjiParseReading($data['kun']);
                }
                if (! $i) {
                    if ($this->wordFilter and strpos($this->wordFilter, $data['name']) === false) {
                        continue 2;
                    }
                }
                $data['name'] = $this->kanjiParseTranslation($data['name']);
                $data['name'] = implode('; ', $data['name']);
                if (strlen($data['name'])) {
                    $vocabNode->appendChild($this->loadWord($data));
                }
            }
            $groupNode->appendChild($vocabNode);
        }
        $this->calcProgress();
        return $retNode;
    }

    public function loadKanjiText($text)
    {
        // $kanjiUri = 'http://jisho.org/kanji/details/';
        $kanjiUri = 'http://jisho.org/search/';
        $retNode = $this->dataDoc->createElement(self::ELE_ROOT);
        $retNode->setAttribute('kanji-uri', $kanjiUri);
        $groupNode = $this->dataDoc->createElement(self::ELE_GROUP);
        $groupNode->setAttribute(self::ATTR_NAME, 'kanji');
        $retNode->appendChild($groupNode);
        $exprList = [
            [
                'name' => 'normalize-space(.//*[@class="character"])',
                'note' => '""',
                'reading' => 'normalize-space(.//*[@class = "kanji-details__main-readings"])',
                'on' => '""', // './/*[@class = "dictionary_entry kun_yomi"]//html:a',
                'kun' => '""', // './/*[@class = "dictionary_entry on_yomi"]//html:a',
                               // 'spelling' => 'normalize-space(.//html:div[@class = "connections"])',
                'lang' => '"ja-jp"',
                'player' => '"?"'
            ],
            [
                'name' => 'normalize-space(.//*[@class = "kanji-details__main-meanings"])',
                'note' => '""',
                'lang' => '"en-us"',
                'player' => '"?"'
            ]
        ];
        if (preg_match_all('/[\x{4E00}-\x{9FBF}]/u', $text, $matches)) {
            $matches = array_unique($matches[0]);
            sort($matches);
            
            $kanjiList = array_chunk($matches, 20);
            // my_dump($matches);die();
            foreach ($kanjiList as $kanji) {
                $kanji[] = '#kanji';
                $uri = $kanjiUri . urlencode(implode(' ', $kanji));
                
                if ($xpath = Storage::loadExternalXPath($uri, TIME_MONTH)) {
                    $tmpList = $xpath->evaluate('.//*[@class = "kanji_result clearfix" or @class = "kanji details"]');
                    
                    if (! $tmpList->length) {
                        throw new Exception(sprintf('Kanji URL "%s" does not contain kanji', $uri));
                    }
                    foreach ($tmpList as $rowNode) {
                        $vocabNode = $this->dataDoc->createElement(self::ELE_VOCABLE);
                        foreach ($exprList as $i => $expr) {
                            $data = [];
                            foreach ($expr as $key => $val) {
                                $data[$key] = trim($xpath->evaluate($val, $rowNode));
                            }
                            if (isset($data['reading'])) {
                                if (preg_match('/kun:([^o]+)on:([^J]+)/ui', $data['reading'], $match)) {
                                    $data['kun'] = str_replace('、', ' ', $match[1]);
                                    $data['on'] = str_replace('、', ' ', $match[2]);
                                }
                            }
                            $data['reading'] = [];
                            if (isset($data['kun'])) {
                                $data['reading']['kun'] = $this->kanjiParseReading($data['kun']);
                            }
                            if (isset($data['on'])) {
                                $data['reading']['on'] = $this->kanjiParseReading($data['on']);
                            }
                            if (isset($data['spelling'])) {
                                $data['spelling'] = $this->kanjiParseSpelling($data['spelling']);
                            }
                            if (! $i) {
                                if ($this->wordFilter and strpos($this->wordFilter, $data['name']) === false) {
                                    continue 2;
                                }
                            }
                            $valList = explode(';', $data['name']);
                            $tmpList = [];
                            foreach ($valList as $val) {
                                $val = trim($val);
                                if (strlen($val)) {
                                    $tmpList[] = $val;
                                }
                            }
                            $data['name'] = implode('; ', $tmpList);
                            // my_dump($data);
                            if (strlen($data['name'])) {
                                $vocabNode->appendChild($this->loadWord($data));
                            }
                        }
                        $groupNode->appendChild($vocabNode);
                    }
                } else {
                    throw new Exception(sprintf('Kanji URL "%s" not available', $uri));
                }
            }
        }
        
        $this->calcProgress();
        return $retNode;
    }

    public function output()
    {
        output($this->dataDoc);
    }

    public function getDocument()
    {
        return $this->dataDoc;
    }

    public function setWordFilter($text = null)
    {
        $this->wordFilter = $text;
    }

    public function setProgressList(array $list)
    {
        $this->progressList = $list;
        $this->calcProgress();
    }

    public function getProgressList()
    {
        return $this->progressList;
    }

    public function setDateList(array $list)
    {
        $this->dateList = $list;
    }

    public function getDateList()
    {
        return $this->dateList;
    }

    protected function calcProgress()
    {
        foreach ($this->wordList as $id => $wordNode) {
            if (isset($this->progressList[$id])) {
                $wrong = $this->progressList[$id][0];
                $correct = $this->progressList[$id][1];
            } else {
                $wrong = 0;
                $correct = 0;
            }
            $wordNode->setAttribute('user-wrong', $wrong);
            $wordNode->setAttribute('user-correct', $correct);
        }
    }

    public function generateTestResult($language, array $testList)
    {
        $retNode = $this->dataDoc->createElement('testResult');
        $retNode->setAttribute(self::ATTR_LANG, $language);
        foreach ($testList as $questionId => $answerId) {
            if (isset($this->wordList[$questionId])) {
                $correct = 0;
                $questionNode = $this->wordList[$questionId];
                $questionParentNode = $questionNode->parentNode;
                $answerNode = null;
                if (isset($this->wordList[$answerId])) {
                    $answerNode = $this->wordList[$answerId];
                    $answerParentNode = $answerNode->parentNode;
                    $correct = (int) ($questionParentNode === $answerParentNode);
                }
                $vocabNode = $questionParentNode->cloneNode(true);
                $vocabNode->setAttribute('test-correct', $correct);
                if ($answerNode) {
                    $inputNode = $answerNode->cloneNode(true);
                    $inputNode->setAttribute('test-input', '');
                    $vocabNode->appendChild($inputNode);
                }
                $retNode->appendChild($vocabNode);
                
                if (! isset($this->progressList[$questionId])) {
                    $this->progressList[$questionId] = [
                        0,
                        0
                    ];
                }
                $this->progressList[$questionId][$correct] ++;
                
                if (! isset($this->dateList[$this->currentDay])) {
                    $this->dateList[$this->currentDay] = [
                        0,
                        0
                    ];
                }
                $this->dateList[$this->currentDay][$correct] ++;
            }
        }
        $this->calcProgress();
        return $retNode;
    }

    public function generateTest($language, $questionCount = null, $questionType = null, $selectCount = 6)
    {
        if (! $questionType) {
            $questionType = self::TEST_TYPE_CHOICE;
        }
        $languageQuery = sprintf('word[lang("%s")]', $language);
        $translationExpr = sprintf('word[not(lang("%s"))]', $language);
        
        $languageList = [];
        $selectionList = [];
        
        $userAvg = 0;
        $userCount = 0;
        foreach ($this->wordList as $id => $wordNode) {
            if ($this->dataPath->evaluate(sprintf('boolean(self::%s)', $languageQuery), $wordNode)) {
                $languageList[] = $wordNode;
                if (isset($this->progressList[$id])) {
                    $userAvg -= $this->progressList[$id][0];
                    $userAvg += $this->progressList[$id][1];
                }
                $userCount ++;
            } else {
                $selectionList[] = $wordNode->parentNode;
            }
        }
        if ($userCount) {
            $userAvg /= $userCount;
        }
        if (! $questionCount) {
            /*
             * switch ($questionType) {
             * case self::TEST_TYPE_CHOICE:
             * $questionCount = (int) sqrt(count($languageList)) + 1;
             * if ($questionCount > 33) {
             * $questionCount = 33;
             * }
             * break;
             * case self::TEST_TYPE_TYPING:
             * $questionCount = (int) sqrt(count($languageList)) + 1;
             * if ($questionCount > 33) {
             * $questionCount = 33;
             * }
             * $questionCount *= 2;
             * break;
             * case self::TEST_TYPE_CLICK:
             * $questionCount = (int) sqrt(count($languageList)) + 1;
             * if ($questionCount > 33) {
             * $questionCount = 33;
             * }
             * $questionCount *= 2;
             * break;
             * }
             * //
             */
            $questionCount = (int) (count($languageList) / 20);
            if ($questionCount > 99) {
                $questionCount = 99;
            }
        }
        
        $wordDice = new Dice(6, 3);
        $wordChance = 16;
        
        $wordList = [];
        while (count($wordList) < $questionCount) {
            if ($wordNode = $this->arrayRandomElement($languageList, $wordList)) {
                $wordId = $wordNode->getAttribute(self::ATTR_ID);
                $baseChance = $wordChance;
                $baseChance -= $userAvg;
                if (isset($this->progressList[$wordId])) {
                    $baseChance -= $this->progressList[$wordId][0];
                    $baseChance += $this->progressList[$wordId][1];
                }
                $baseChance = $wordDice->enclose($baseChance);
                // printf('%2d: %s%s', $baseChance, $wordId, PHP_EOL);
                if ($wordDice->rollHigher($baseChance)) {
                    $wordList[$wordId] = $wordNode;
                }
            } else {
                break;
            }
        }
        $randomList = [];
        $staticList = [];
        
        switch ($questionType) {
            case self::TEST_TYPE_CHOICE:
            case self::TEST_TYPE_CLICK:
                $selectDice = new Dice(100, 1);
                $selectChance = 100;
                
                foreach ($wordList as $wordId => $wordNode) {
                    $vocabNode = $wordNode->parentNode;
                    $groupNode = $vocabNode->parentNode;
                    // $vocabCount = $this->dataPath->evaluate(sprintf('count(%s)', self::ELE_VOCABLE), $groupNode);
                    // $selectImpossible = ($vocabCount < $selectCount);
                    $selectList = [
                        $vocabNode
                    ];
                    $spellingList = [];
                    $nodeList = $this->dataPath->evaluate(sprintf('.//%s', self::ELE_SPELLING), $vocabNode);
                    foreach ($nodeList as $node) {
                        $spellingList[] = $node->getAttribute(self::ATTR_NAME);
                    }
                    if ($spellingList) {
                        $groupNode = null;
                    }
                    while (count($selectList) < $selectCount) {
                        if ($newWord = $this->arrayRandomElement($selectionList, $selectList)) {
                            $baseChance = $selectChance;
                            if ($newWord->parentNode === $groupNode) {
                                $baseChance = 0;
                            }
                            foreach ($spellingList as $spelling) {
                                if ($this->dataPath->evaluate(sprintf('boolean(.//%s[@name="%s"])', self::ELE_SPELLING, $spelling), $newWord)) {
                                    $baseChance -= 20;
                                }
                            }
                            // printf('%2d: %s <> %s%s', $baseChance, $wordId, $newWord->firstChild->getAttribute('id'), PHP_EOL);
                            $baseChance = $selectDice->enclose($baseChance);
                            if ($selectDice->rollHigher($baseChance)) {
                                $selectList[] = $newWord;
                            }
                        } else {
                            break;
                        }
                    }
                    $shuffleList = [
                        $wordNode
                    ];
                    // my_dump($wordNode->getAttribute('xml:lang'));
                    foreach ($selectList as $selectNode) {
                        $nodeList = $this->dataPath->evaluate($translationExpr, $selectNode);
                        foreach ($nodeList as $node) {
                            $shuffleList[] = $node->cloneNode(true);
                        }
                    }
                    $randomList[] = $this->arrayShuffle($shuffleList);
                }
                break;
            case self::TEST_TYPE_TYPING:
                foreach ($selectionList as $selectNode) {
                    $nodeList = $this->dataPath->evaluate($translationExpr, $selectNode);
                    foreach ($nodeList as $node) {
                        $staticList[] = $node->cloneNode(true);
                    }
                }
                foreach ($wordList as $wordId => $wordNode) {
                    $vocabNode = $wordNode->parentNode;
                    $groupNode = $vocabNode->parentNode;
                    $randomList[] = [
                        $wordNode
                    ];
                }
                break;
        }
        
        $retNode = $this->dataDoc->createElement('test');
        $retNode->setAttribute(self::ATTR_TYPE, $questionType);
        $retNode->setAttribute(self::ATTR_LANG, $language);
        $parentNode = $this->dataDoc->createElement(self::ELE_REPOSITORY);
        foreach ($staticList as $node) {
            $parentNode->appendChild($node);
        }
        $retNode->appendChild($parentNode);
        foreach ($randomList as $nodeList) {
            $parentNode = $this->dataDoc->createElement(self::ELE_VOCABLE);
            foreach ($nodeList as $node) {
                $parentNode->appendChild($node);
            }
            $retNode->appendChild($parentNode);
        }
        return $retNode;
    }

    public function generateTestDates()
    {
        $retNode = $this->dataDoc->createElement('testDates');
        $startDay = 1;
        $startTime = mktime(0, 0, 0, 1, $startDay, 2013);
        $endTime = $this->currentTime;
        $endDay = $this->currentDay;
        $max = 0;
        for ($time = $startTime; $time < $endTime; $startDay ++, $time = mktime(0, 0, 0, 1, $startDay, 2013)) {
            $day = date(DATE_DATE, $time);
            $node = $this->dataDoc->createElement('day');
            if ($day === $endDay) {
                $node->setAttribute('now', '');
            }
            $node->setAttribute('date-stamp', $time);
            $node->setAttribute('date-time', $day);
            if (isset($this->dateList[$day])) {
                $wrong = $this->dateList[$day][0];
                $correct = $this->dateList[$day][1];
                $node->setAttribute('user-wrong', $wrong);
                $node->setAttribute('user-correct', $correct);
                $node->setAttribute('user-text', sprintf('%d/%d', $correct, $wrong + $correct));
                if ($max < array_sum($this->dateList[$day])) {
                    $max = array_sum($this->dateList[$day]);
                }
            }
            $retNode->appendChild($node);
        }
        $retNode->setAttribute('user-max', $max);
        $retNode->setAttribute('user-height', 100 + 100 * (int) ($max / 100));
        if (! $max) {
            $retNode = null;
        }
        return $retNode;
    }

    protected function buildIdString($lang, $name, $note)
    {
        return str_replace([
            '"',
            '[',
            ']'
        ], '', sprintf('%s:%s-%s', $lang, $name, $note));
    }

    protected function kanjiParseReading($read)
    {
        $retList = [];
        if (strlen($read)) {
            // $read = str_replace('.', ' ', $read);
            $tmpList = explode(' ', $read);
            foreach ($tmpList as $val) {
                $val = trim($val);
                if (strlen($val)) {
                    $retList[] = $val;
                }
            }
        }
        return $retList;
    }

    protected function kanjiParseSpelling($spell)
    {
        $retList = [];
        if (preg_match('/Parts: ([^A-Z]+)/', $spell, $match)) {
            $spell = $match[1];
            $tmpList = preg_split('//u', $spell, - 1, PREG_SPLIT_NO_EMPTY);
            foreach ($tmpList as $val) {
                $retList[] = $val;
            }
        }
        return $retList;
    }

    protected function kanjiParseTranslation($read)
    {
        $retList = [];
        if (strlen($read)) {
            $tmpList = explode(',', $read);
            foreach ($tmpList as $val) {
                $val = trim($val);
                if (strlen($val)) {
                    $retList[] = $val;
                }
            }
        }
        return $retList;
    }

    protected function arrayExplode($str)
    {
        if (! is_array($str)) {
            $str = strlen($str) ? explode('; ', str_replace([
                '/',
                '、',
                '・'
            ], '; ', $str)) : [];
        }
        return $str;
        
        $explodeList = [
            '/',
            '、',
            '・'
        ];
        if (is_array($str)) {
            $str = implode('; ', $str);
        }
        $retList = [
            $str
        ];
        foreach ($explodeList as $ex) {
            $tmpList = [];
            foreach ($retList as $kana) {
                $tmpList = array_merge($tmpList, explode($ex, $kana));
            }
            $retList = $tmpList;
        }
        return $retList;
    }

    protected function arrayRandomElement(array $randomArr, array $excludeList = [])
    {
        $ret = null;
        $randomCount = count($randomArr);
        if ($randomCount > count($excludeList)) {
            $randomCount --;
            do {
                $ret = $randomArr[rand(0, $randomCount)];
            } while (in_array($ret, $excludeList, true));
        }
        return $ret;
    }

    protected function arrayShuffle(array $list)
    {
        shuffle($list);
        return $list;
    }
}
