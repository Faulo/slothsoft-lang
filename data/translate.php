<?php
namespace Slothsoft\Farah;

use Slothsoft\Lang\Translator;

// return \Storage::loadExternalDocument("http://jisho.org/search/tomodachi %23words?page=1");
$req = $this->httpRequest->getInputJSON();
// $req = json_decode('{"source":"ja","target":"en","sentence":[{"latin":["to","mo","da","chi","u","mi"],"hiragana":["と","も","だ","ち","う","み"],"katakana":["ト","モ","ダ","チ","ウ","ミ"]}],"options":{"commonOnly":true}}', true);

$sentence = isset($req['sentence']) ? (array) $req['sentence'] : [];

$options = isset($req['options']) ? (array) $req['options'] : [];

$sourceLang = isset($req['source']) ? (string) $req['source'] : '';

$targetLang = isset($req['target']) ? (string) $req['target'] : '';

$translator = Translator::getTranslator($sourceLang, $targetLang);
$translator->setOptions($options);
$translation = $translator->createTranslation($sentence);

// my_dump($translation->asArray());
return $translation->asNode($dataDoc);
//return $translation->asStream();