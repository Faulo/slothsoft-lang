<?php
namespace Slothsoft\Lang;

use DOMDocument;
use DOMXPath;
use DOMElement;
use Exception;

class GrammarJa
{

    protected $doc;

    protected $xpath;

    protected $rootNode;

    protected $conjugationTypeList;

    protected $conjugationBaseList;

    protected $conjugationExceptionList;

    public function __construct()
    {}

    public function init(DOMDocument $grammarDoc)
    {
        $this->doc = $grammarDoc;
        $this->xpath = new DOMXPath($this->doc);
        $nodeList = $this->xpath->evaluate('.//grammar');
        foreach ($nodeList as $node) {
            $this->rootNode = $node;
            break;
        }
        if (! $this->rootNode) {
            throw new Exception('Grammar Document has no grammar?! >A<');
        }
        $this->conjugationTypeList = [];
        $nodeList = $this->xpath->evaluate('.//conjugation/typeList/type', $this->rootNode);
        foreach ($nodeList as $node) {
            $this->conjugationTypeList[$node->getAttribute('suffix')] = $node->getAttribute('name');
        }
        $this->conjugationBaseList = [];
        $nodeList = $this->xpath->evaluate('.//conjugation/form[@name = "dictionary"]/suffix', $this->rootNode);
        foreach ($nodeList as $node) {
            $this->conjugationBaseList[$node->getAttribute('type')] = $node->getAttribute('name');
        }
        $this->conjugationExceptionList = [];
        $nodeList = $this->xpath->evaluate('.//conjugation/exceptionList', $this->rootNode);
        foreach ($nodeList as $node) {
            $str = $this->xpath->evaluate('normalize-space(.)', $node);
            $exceptionList = [];
            $arr = explode(' ', $str);
            foreach ($arr as $val) {
                if (strlen($val)) {
                    $exceptionList[] = $val;
                }
            }
            $this->conjugationExceptionList[$node->getAttribute('type')] = $exceptionList;
        }
        // my_dump($this->conjugationTypeList);
    }

    public function getConjugationFormList()
    {
        $ret = [];
        $nodeList = $this->xpath->evaluate('.//conjugation/form', $this->rootNode);
        foreach ($nodeList as $node) {
            $ret[] = $node->getAttribute('name');
        }
        return $ret;
    }

    // <word name="" note="" id=""/>
    public function conjugateWord(DOMElement $wordNode)
    {
        $retNode = null;
        $dataDoc = $wordNode->ownerDocument;
        
        $wordName = $wordNode->getAttribute('name');
        $wordNote = $wordNode->getAttribute('note');
        
        if ($wordType = $this->guessConjugationType($wordName, $wordNote)) {
            $retNode = $dataDoc->createElement('conjugation');
            $retNode->appendChild($wordNode->cloneNode(true));
            $retNode->setAttribute('type', $wordType);
            $suffix = $this->conjugationBaseList[$wordType];
            if (strlen($suffix)) {
                $nameBase = substr($wordName, 0, - strlen($suffix));
                $noteBase = substr($wordNote, 0, - strlen($suffix));
            } else {
                $nameBase = $wordName;
                $noteBase = $wordNote;
            }
            $retNode->setAttribute('base', $nameBase);
            
            $nameList = [];
            $noteList = [];
            
            $query = sprintf('.//conjugation/exception[@base = "%s"]/word', $nameBase);
            $nodeList = $this->xpath->evaluate($query, $this->rootNode);
            foreach ($nodeList as $node) {
                $name = $node->getAttribute('name');
                $note = $node->getAttribute('note');
                $form = $node->getAttribute('form');
                $nameList[$form] = $name;
                $noteList[$form] = $note;
            }
            $query = sprintf('.//conjugation/form/suffix[@type = "%s"]', $wordType);
            $nodeList = $this->xpath->evaluate($query, $this->rootNode);
            foreach ($nodeList as $node) {
                $name = $nameBase . $node->getAttribute('name');
                $note = $noteBase . $node->getAttribute('name');
                $form = $node->parentNode->getAttribute('name');
                if (! isset($nameList[$form])) {
                    $nameList[$form] = $name;
                    $noteList[$form] = strlen($noteBase) ? $note : '';
                }
            }
            foreach ($nameList as $form => $name) {
                $formNode = $dataDoc->createElement('word');
                $formNode->setAttribute('form', $form);
                $formNode->setAttribute('name', $name);
                $formNode->setAttribute('note', $noteList[$form]);
                
                $retNode->appendChild($formNode);
            }
        }
        
        return $retNode;
    }

    public function guessConjugationType($kanji, $kana)
    {
        $ret = null;
        $nameList = [
            $kana,
            $kanji
        ];
        foreach ($nameList as $name) {
            if (strlen($name)) {
                foreach ($this->conjugationExceptionList as $type => $exceptionList) {
                    if (in_array($name, $exceptionList)) {
                        // my_dump($name);
                        return $type;
                    }
                }
            }
        }
        foreach ($nameList as $name) {
            if (strlen($name)) {
                $name = TranslatorJaEn::toLatin($name);
                foreach ($this->conjugationTypeList as $suffix => $type) {
                    $suffix = TranslatorJaEn::toLatin($suffix);
                    if (substr($name, - strlen($suffix)) === $suffix) {
                        $ret = $type;
                    }
                }
            }
            if ($ret) {
                break;
            }
        }
        return $ret;
    }
}