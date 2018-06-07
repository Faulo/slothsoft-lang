<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

use Slothsoft\Core\DOMHelper;
use DOMDocument;

class Translation
{

    const ELE_ROOT = 'translator';

    const ELE_TEXT = 'translation';

    const ELE_WORD = 'word';

    const ATTR_WORDS = 'text';

    const ATTR_SYLLABLECOUNT = 'syllables';

    protected $sentenceStack = [];

    protected $ownerTranslator;

    public function __construct(Translator $translator, array $sentence)
    {
        $this->ownerTranslator = $translator;
        // $this->sentenceStack = $sentence;
        foreach ($sentence as $wordList) {
            $this->sentenceStack[] = $wordList['latin'];
        }
    }

    public function hasWords()
    {
        return (bool) count($this->sentenceStack);
    }

    public function nextWord()
    {
        $ret = null;
        if (count($this->sentenceStack)) {
            $word = array_shift($this->sentenceStack);
            $nextWord = null;
            $ret = $this->ownerTranslator->translateWord($word, $nextWord);
            if ($nextWord !== null) {
                array_unshift($this->sentenceStack, $nextWord);
            }
        }
        return $ret;
    }

    public function asArray()
    {
        $ret = [];
        while ($this->hasWords()) {
            if ($word = $this->nextWord()) {
                $ret[] = $word;
            }
        }
        return $ret;
    }

    public function asNode(DOMDocument $dataDoc)
    {
        
        // my_dump($translationList);
        $dom = new DOMHelper();
        
        $retNode = $dataDoc->createElement('translator');
        $translationList = $this->asArray();
        $translationNode = $dataDoc->createElement('translation');
        foreach ($translationList as $translation) {
            $wordNode = $dataDoc->createElement('word');
            $wordNode->setAttribute('name', $translation['word']);
            $wordNode->setAttribute('syllables', $translation['syllables']);
            
            foreach ($translation['translation'] as $kana => $kanjiList) {
                $kanaNode = $dataDoc->createElement('kana');
                if (strlen($kana)) {
                    $kanaNode->setAttribute('name', $kana);
                }
                if ($uri = $this->ownerTranslator->getPlayerURI($kana, '')) {
                    $kanaNode->setAttribute('player-uri', $uri);
                }
                foreach ($kanjiList as $kanji => $meaningList) {
                    $kanjiNode = $dataDoc->createElement('kanji');
                    if (strlen($kanji)) {
                        $kanjiNode->setAttribute('name', $kanji);
                    }
                    if ($uri = $this->ownerTranslator->getPlayerURI($kana, $kanji)) {
                        $kanjiNode->setAttribute('player-uri', $uri);
                    }
                    foreach ($meaningList as $meaning) {
                        $englishNode = $dataDoc->createElement('english');
                        $englishNode->appendChild($dom->parse($meaning, $dataDoc));
                        $kanjiNode->appendChild($englishNode);
                    }
                    
                    $kanaNode->appendChild($kanjiNode);
                }
                
                $wordNode->appendChild($kanaNode);
            }
            
            $translationNode->appendChild($wordNode);
        }
        $retNode->appendChild($translationNode);
        
        return $retNode;
        
        // return $this->ownerTranslator->createTranslationElement($dataDoc, $this->asArray());
    }

    public function asStream()
    {
        return new TranslatorStream($this);
    }
}