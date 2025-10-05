<?php
declare(strict_types = 1);
namespace Slothsoft\Lang;

abstract class Translator {
    
    public static function getTranslator($sourceLang, $targetLang) {
        $sourceLang = trim($sourceLang);
        if (strlen($sourceLang) < 2) {
            throw new \Exception('source language not set!');
        }
        $sourceLang = strtoupper($sourceLang[0]) . strtolower($sourceLang[1]);
        
        $targetLang = trim($targetLang);
        if (strlen($targetLang) < 2) {
            throw new \Exception('target language not set!');
        }
        $targetLang = strtoupper($targetLang[0]) . strtolower($targetLang[1]);
        
        $class = __CLASS__ . $sourceLang . $targetLang;
        
        return new $class();
    }
    
    protected $otions = [];
    
    public function setOptions(array $options) {
        foreach (array_keys($this->options) as $key) {
            if (isset($options[$key])) {
                $this->options[$key] = $options[$key];
            }
        }
    }
    
    abstract public function translateWord($word, &$nexWord);
    
    abstract protected function getTranslationURL($letters, $pageNo);
    
    public function createTranslation(array $wordList) {
        return new Translation($this, $wordList);
    }
}