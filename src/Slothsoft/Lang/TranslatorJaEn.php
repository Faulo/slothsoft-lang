<?php
// © 2012 Daniel Schulz
namespace Slothsoft\Lang;

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Farah\Session;

/*
 * <translator>
 * <translation text="[word ]+">
 * <word name="abba" syllables="2">
 * <kana name="aba">
 * <kanji name="KJ">
 * <english name="word">
 * </kanji>
 * </kana>
 * <kana name="abba">
 * <kanji name="KJ">
 * <english name="word">
 * </kanji>
 * </kana>
 * </word>
 * <word name="la" syllables="1">
 * <kana name="aba">
 * <kanji name="KJ">
 * <english name="word">
 * </kanji>
 * </kana>
 * </word>
 * <word name="ga" syllables="1">
 * <kana name="aba">
 * <kanji name="KJ">
 * <english name="word">
 * </kanji>
 * </kana>
 * </word>
 * </translation>
 * <translator>
 * //
 */
class TranslatorJaEn extends Translator
{

    protected static $hiraganaMap = [
        'あ' => 'ア',
        'ば' => 'バ',
        'びゃ' => 'ビャ',
        'ちゃ' => 'チャ',
        'だ' => 'ダ',
        'が' => 'ガ',
        'ぎゃ' => 'ギャ',
        'は' => 'ハ',
        'ひゃ' => 'ヒャ',
        'じゃ' => 'ジャ',
        'か' => 'カ',
        'きゃ' => 'キャ',
        'ま' => 'マ',
        'みゃ' => 'ミャ',
        'な' => 'ナ',
        'にゃ' => 'ニャ',
        'ぱ' => 'パ',
        'ぴゃ' => 'ピャ',
        'ら' => 'ラ',
        'りゃ' => 'リャ',
        'さ' => 'サ',
        'しゃ' => 'シャ',
        'た' => 'タ',
        'わ' => 'ワ',
        'や' => 'ヤ',
        'ざ' => 'ザ',
        'べ' => 'ベ',
        'で' => 'デ',
        'え' => 'エ',
        'げ' => 'ゲ',
        'へ' => 'ヘ',
        'ひぇ' => 'ヒェ',
        'け' => 'ケ',
        'め' => 'メ',
        'ね' => 'ネ',
        'ぺ' => 'ペ',
        'れ' => 'レ',
        'せ' => 'セ',
        'て' => 'テ',
        'ゑ' => 'ヱ',
        'ぜ' => 'ゼ',
        'び' => 'ビ',
        'ち' => 'チ',
        'ぎ' => 'ギ',
        'ひ' => 'ヒ',
        'い' => 'イ',
        'じ' => 'ジ',
        'ぢ' => 'ジ',
        'き' => 'キ',
        'み' => 'ミ',
        'に' => 'ニ',
        'ぴ' => 'ピ',
        'り' => 'リ',
        'し' => 'シ',
        'ゐ' => 'ヰ',
        'ぼ' => 'ボ',
        'びょ' => 'ビョ',
        'ちょ' => 'チョ',
        'ど' => 'ド',
        'ご' => 'ゴ',
        'ぎょ' => 'ギョ',
        'ほ' => 'ホ',
        'ひょ' => 'ヒョ',
        'じょ' => 'ジョ',
        'こ' => 'コ',
        'きょ' => 'キョ',
        'も' => 'モ',
        'みょ' => 'ミョ',
        'の' => 'ノ',
        'にょ' => 'ニョ',
        'お' => 'オ',
        'ぽ' => 'ポ',
        'ぴょ' => 'ピョ',
        'ろ' => 'ロ',
        'りょ' => 'リョ',
        'しょ' => 'ショ',
        'そ' => 'ソ',
        'と' => 'ト',
        'を' => 'ヲ',
        'よ' => 'ヨ',
        'ぞ' => 'ゾ',
        'ぶ' => 'ブ',
        'びゅ' => 'ビュ',
        'ちゅ' => 'チュ',
        'ふ' => 'フ',
        'ぐ' => 'グ',
        'ぎゅ' => 'ギュ',
        'ひゅ' => 'ヒュ',
        'じゅ' => 'ジュ',
        'く' => 'ク',
        'きゅ' => 'キュ',
        'む' => 'ム',
        'みゅ' => 'ミュ',
        'ぬ' => 'ヌ',
        'にゅ' => 'ニュ',
        'ぷ' => 'プ',
        'ぴゅ' => 'ピュ',
        'る' => 'ル',
        'りゅ' => 'リュ',
        'しゅ' => 'シュ',
        'す' => 'ス',
        'つ' => 'ツ',
        'う' => 'ウ',
        'ゆ' => 'ユ',
        'ず' => 'ズ',
        'づ' => 'ズ',
        'ん' => 'ン'
    ];

    protected static $katakanaMap = [
        'ア' => 'あ',
        'バ' => 'ば',
        'ビャ' => 'びゃ',
        'チャ' => 'ちゃ',
        'ダ' => 'だ',
        'ガ' => 'が',
        'ギャ' => 'ぎゃ',
        'ハ' => 'は',
        'ヒャ' => 'ひゃ',
        'ジャ' => 'じゃ',
        'カ' => 'か',
        'キャ' => 'きゃ',
        'マ' => 'ま',
        'ミャ' => 'みゃ',
        'ナ' => 'な',
        'ニャ' => 'にゃ',
        'パ' => 'ぱ',
        'ピャ' => 'ぴゃ',
        'ラ' => 'ら',
        'リャ' => 'りゃ',
        'サ' => 'さ',
        'シャ' => 'しゃ',
        'タ' => 'た',
        'ワ' => 'わ',
        'ヤ' => 'や',
        'ザ' => 'ざ',
        'ベ' => 'べ',
        'デ' => 'で',
        'エ' => 'え',
        'ゲ' => 'げ',
        'ヘ' => 'へ',
        'ヒェ' => 'ひぇ',
        'ケ' => 'け',
        'メ' => 'め',
        'ネ' => 'ね',
        'ペ' => 'ぺ',
        'レ' => 'れ',
        'セ' => 'せ',
        'テ' => 'て',
        'ヱ' => 'ゑ',
        'ウェ' => 'ゑ',
        'ゼ' => 'ぜ',
        'ビ' => 'び',
        'チ' => 'ち',
        'ギ' => 'ぎ',
        'ヒ' => 'ひ',
        'イ' => 'い',
        'ジ' => 'じ',
        'ヂ' => 'じ',
        'キ' => 'き',
        'ミ' => 'み',
        'ニ' => 'に',
        'ピ' => 'ぴ',
        'リ' => 'り',
        'シ' => 'し',
        'ヰ' => 'ゐ',
        'ウィ' => 'ゐ',
        'ボ' => 'ぼ',
        'ビョ' => 'びょ',
        'チョ' => 'ちょ',
        'ド' => 'ど',
        'ゴ' => 'ご',
        'ギョ' => 'ぎょ',
        'ホ' => 'ほ',
        'ヒョ' => 'ひょ',
        'ジョ' => 'じょ',
        'コ' => 'こ',
        'キョ' => 'きょ',
        'モ' => 'も',
        'ミョ' => 'みょ',
        'ノ' => 'の',
        'ニョ' => 'にょ',
        'オ' => 'お',
        'ポ' => 'ぽ',
        'ピョ' => 'ぴょ',
        'ロ' => 'ろ',
        'リョ' => 'りょ',
        'ショ' => 'しょ',
        'ソ' => 'そ',
        'ト' => 'と',
        'ヲ' => 'を',
        'ウォ' => 'を',
        'ヨ' => 'よ',
        'ゾ' => 'ぞ',
        'ブ' => 'ぶ',
        'ビュ' => 'びゅ',
        'チュ' => 'ちゅ',
        'フ' => 'ふ',
        'グ' => 'ぐ',
        'ギュ' => 'ぎゅ',
        'ヒュ' => 'ひゅ',
        'ジュ' => 'じゅ',
        'ク' => 'く',
        'キュ' => 'きゅ',
        'ム' => 'む',
        'ミュ' => 'みゅ',
        'ヌ' => 'ぬ',
        'ニュ' => 'にゅ',
        'プ' => 'ぷ',
        'ピュ' => 'ぴゅ',
        'ル' => 'る',
        'リュ' => 'りゅ',
        'シュ' => 'しゅ',
        'ス' => 'す',
        'ツ' => 'つ',
        'ウ' => 'う',
        'ユ' => 'ゆ',
        'ズ' => 'ず',
        'ヅ' => 'ず',
        'ン' => 'ん'
    ];

    protected static $latinHiraganaMap = [
        'a' => 'あ',
        'i' => 'い',
        'u' => 'う',
        'e' => 'え',
        'o' => 'お',
        'ka' => 'か',
        'ki' => 'き',
        'ku' => 'く',
        'ke' => 'け',
        'ko' => 'こ',
        'kya' => 'きゃ',
        'kyu' => 'きゅ',
        'kyo' => 'きょ',
        'sa' => 'さ',
        'shi' => 'し',
        'su' => 'す',
        'se' => 'せ',
        'so' => 'そ',
        'sha' => 'しゃ',
        'shu' => 'しゅ',
        'sho' => 'しょ',
        'ta' => 'た',
        'chi' => 'ち',
        'tsu' => 'つ',
        'te' => 'て',
        'to' => 'と',
        'cha' => 'ちゃ',
        'chu' => 'ちゅ',
        'cho' => 'ちょ',
        'na' => 'な',
        'ni' => 'に',
        'nu' => 'ぬ',
        'ne' => 'ね',
        'no' => 'の',
        'nya' => 'にゃ',
        'nyu' => 'にゅ',
        'nyo' => 'にょ',
        'ha' => 'は',
        'hi' => 'ひ',
        'fu' => 'ふ',
        'he' => 'へ',
        'ho' => 'ほ',
        'hya' => 'ひゃ',
        'hyu' => 'ひゅ',
        'hyo' => 'ひょ',
        'ma' => 'ま',
        'mi' => 'み',
        'mu' => 'む',
        'me' => 'め',
        'mo' => 'も',
        'mya' => 'みゃ',
        'myu' => 'みゅ',
        'myo' => 'みょ',
        'ya' => 'や',
        'yu' => 'ゆ',
        'yo' => 'よ',
        'ra' => 'ら',
        'ri' => 'り',
        'ru' => 'る',
        're' => 'れ',
        'ro' => 'ろ',
        'rya' => 'りゃ',
        'ryu' => 'りゅ',
        'ryo' => 'りょ',
        'wa' => 'わ',
        'wi' => 'ゐ',
        'we' => 'ゑ',
        'wo' => 'を',
        'n' => 'ん',
        'ga' => 'が',
        'gi' => 'ぎ',
        'gu' => 'ぐ',
        'ge' => 'げ',
        'go' => 'ご',
        'gya' => 'ぎゃ',
        'gyu' => 'ぎゅ',
        'gyo' => 'ぎょ',
        'za' => 'ざ',
        'ji' => 'じ',
        'zu' => 'ず',
        'ze' => 'ぜ',
        'zo' => 'ぞ',
        'ja' => 'じゃ',
        'ju' => 'じゅ',
        'jo' => 'じょ',
        'da' => 'だ',
        'de' => 'で',
        'do' => 'ど',
        'ba' => 'ば',
        'bi' => 'び',
        'bu' => 'ぶ',
        'be' => 'べ',
        'bo' => 'ぼ',
        'bya' => 'びゃ',
        'byu' => 'びゅ',
        'byo' => 'びょ',
        'pa' => 'ぱ',
        'pi' => 'ぴ',
        'pu' => 'ぷ',
        'pe' => 'ぺ',
        'po' => 'ぽ',
        'pya' => 'ぴゃ',
        'pyu' => 'ぴゅ',
        'pyo' => 'ぴょ',
        'hye' => 'ひぇ'
    ];

    protected static $hiraganaLatinMap = [
        'あ' => 'a',
        'い' => 'i',
        'う' => 'u',
        'え' => 'e',
        'お' => 'o',
        'か' => 'ka',
        'き' => 'ki',
        'く' => 'ku',
        'け' => 'ke',
        'こ' => 'ko',
        'きゃ' => 'kya',
        'きゅ' => 'kyu',
        'きょ' => 'kyo',
        'さ' => 'sa',
        'し' => 'shi',
        'す' => 'su',
        'せ' => 'se',
        'そ' => 'so',
        'しゃ' => 'sha',
        'しゅ' => 'shu',
        'しょ' => 'sho',
        'た' => 'ta',
        'ち' => 'chi',
        'つ' => 'tsu',
        'て' => 'te',
        'と' => 'to',
        'ちゃ' => 'cha',
        'ちゅ' => 'chu',
        'ちょ' => 'cho',
        'な' => 'na',
        'に' => 'ni',
        'ぬ' => 'nu',
        'ね' => 'ne',
        'の' => 'no',
        'にゃ' => 'nya',
        'にゅ' => 'nyu',
        'にょ' => 'nyo',
        'は' => 'ha',
        'ひ' => 'hi',
        'ふ' => 'fu',
        'へ' => 'he',
        'ほ' => 'ho',
        'ひゃ' => 'hya',
        'ひゅ' => 'hyu',
        'ひょ' => 'hyo',
        'ま' => 'ma',
        'み' => 'mi',
        'む' => 'mu',
        'め' => 'me',
        'も' => 'mo',
        'みゃ' => 'mya',
        'みゅ' => 'myu',
        'みょ' => 'myo',
        'や' => 'ya',
        'ゆ' => 'yu',
        'よ' => 'yo',
        'ら' => 'ra',
        'り' => 'ri',
        'る' => 'ru',
        'れ' => 're',
        'ろ' => 'ro',
        'りゃ' => 'rya',
        'りゅ' => 'ryu',
        'りょ' => 'ryo',
        'わ' => 'wa',
        'ゐ' => 'wi',
        'ゑ' => 'we',
        'を' => 'wo',
        'ん' => 'n',
        'が' => 'ga',
        'ぎ' => 'gi',
        'ぐ' => 'gu',
        'げ' => 'ge',
        'ご' => 'go',
        'ぎゃ' => 'gya',
        'ぎゅ' => 'gyu',
        'ぎょ' => 'gyo',
        'ざ' => 'za',
        'じ' => 'ji',
        'ぢ' => 'ji',
        'ず' => 'zu',
        'づ' => 'zu',
        'ぜ' => 'ze',
        'ぞ' => 'zo',
        'じゃ' => 'ja',
        'じゅ' => 'ju',
        'じょ' => 'jo',
        'だ' => 'da',
        'で' => 'de',
        'ど' => 'do',
        'ば' => 'ba',
        'び' => 'bi',
        'ぶ' => 'bu',
        'べ' => 'be',
        'ぼ' => 'bo',
        'びゃ' => 'bya',
        'びゅ' => 'byu',
        'びょ' => 'byo',
        'ぱ' => 'pa',
        'ぴ' => 'pi',
        'ぷ' => 'pu',
        'ぺ' => 'pe',
        'ぽ' => 'po',
        'ぴゃ' => 'pya',
        'ぴゅ' => 'pyu',
        'ぴょ' => 'pyo',
        'ひぇ' => 'hye'
    ];

    public static function toHiragana($kanaStr)
    {
        $kanaStr = strtr($kanaStr, self::$katakanaMap);
        return $kanaStr;
    }

    public static function toKatakana($kanaStr)
    {
        $kanaStr = strtr($kanaStr, self::$hiraganaMap);
        return $kanaStr;
    }

    public static function toLatin($kanaStr)
    {
        $kanaStr = strtr($kanaStr, self::$hiraganaLatinMap);
        return $kanaStr;
    }

    public static function fromLatin($kanaStr)
    {
        $kanaStr = strtr($kanaStr, self::$latinHiraganaMap);
        return $kanaStr;
    }

    public static function downloadPlayerURI($uri)
    {
        static $targetPath = null;
        if (! $targetPath) {
            $targetPath = realpath(ServerEnvironment::getRootDirectory() . 'mod/slothsoft/res/vocab-ja');
        }
        $ret = $uri;
        if ($targetPath) {
            if (preg_match('/kana=(.*)&kanji=(.*)/u', $uri, $match)) {
                $kana = $match[1];
                $kanji = $match[2];
                
                $file = $kana . '&' . $kanji;
                $file = FileSystem::base64Encode($file);
                
                $fsPath = $targetPath . DIRECTORY_SEPARATOR . $file . '.mp3';
                $webPath = '/getResource.php/slothsoft/vocab-ja/' . $file;
                
                if (file_exists($fsPath)) {
                    $ret = $webPath;
                } else {
                    // $data = file_get_contents($uri);
                    
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $uri);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    
                    $length = strlen($data);
                    if ($length > self::PLAYER_LENGTH_MIN and $length < self::PLAYER_LENGTH_MAX) {
                        file_put_contents($fsPath, $data);
                        // clearstatcache();
                        $ret = $webPath;
                    } else {
                        // my_dump($uri);
                    }
                }
            }
        }
        return $ret;
    }

    public static function getPlayerURI($kana, $kanji)
    {
        static $sessionKey = 'japanese-audio-list';
        static $session = null;
        static $foundList = null;
        if (! $session) {
            $session = new Session();
            $foundList = $session->getGlobalValue($sessionKey, []);
        }
        $ret = null;
        $argList = [];
        $argList[] = [
            $kana,
            $kanji
        ];
        $argList[] = [
            $kana,
            ''
        ];
        $argList[] = [
            $kanji,
            ''
        ];
        $argList[] = [
            '',
            $kana
        ];
        $argList[] = [
            '',
            $kanji
        ];
        $argList[] = [
            $kanji,
            $kana
        ];
        
        $attemptList = [];
        foreach ($argList as $arg) {
            if ($arg[0] === '' and $arg[1] === '') {
                continue;
            }
            $key = implode('-', $arg);
            $attemptList[$key] = $arg;
        }
        
        foreach ($attemptList as $key => $arg) {
            if (isset($foundList[$key]) and $foundList[$key]) {
                $ret = $foundList[$key];
                break;
            }
        }
        
        $sessionChanged = false;
        
        if (! $ret) {
            foreach ($attemptList as $key => $arg) {
                if (! isset($foundList[$key])) {
                    $foundList[$key] = false;
                    $sessionChanged = true;
                    $uri = sprintf(self::PLAYER_URI, $arg[0], $arg[1]);
                    $res = Storage::loadExternalHeader($uri, Seconds::MONTH);
                    if (isset($res['content-type']) and $res['content-type'] === 'audio/mpeg') {
                        $length = (int) $res['content-length'];
                        if ($length > self::PLAYER_LENGTH_MIN and $length < self::PLAYER_LENGTH_MAX) {
                            $foundList[$key] = $uri;
                            $sessionChanged = true;
                            $ret = $uri;
                            break;
                        }
                    }
                }
            }
        }
        
        if ($sessionChanged) {
            $session->setGlobalValue($sessionKey, $foundList);
        }
        
        if ($ret) {
            $ret = self::downloadPlayerURI($ret);
        }
        
        return $ret;
    }

    public static function lookupPlayerUri($sourceKana)
    {
        static $uriList = [];
        $sourceKana = str_replace([
            '-',
            '.',
            ' '
        ], '', $sourceKana);
        $sourceKana = self::toHiragana($sourceKana);
        $altKana = self::toKatakana($sourceKana);
        if (! isset($uriList[$sourceKana])) {
            $uri = '';
            if (strlen($sourceKana)) {
                $form = [];
                $kana = $sourceKana;
                $kana = mb_convert_encoding($kana, 'EUC-JP', 'UTF-8');
                // $kana = rawurlencode($kana);
                // $kana = str_replace('%', '%A5%', $kana);
                $form['dsrchkey'] = $kana;
                $form['dicsel'] = '1';
                $form['dsrchtype'] = 'E';
                $form['actionparam'] = '_0_?_0_';
                
                if ($tmpPath = Storage::loadExternalXPath(self::PLAYER_URI_SEARCH, Seconds::MONTH, $form, [
                    'method' => 'POST'
                ])) {
                    $kanaList = [
                        $sourceKana,
                        $altKana
                    ];
                    foreach ($kanaList as $kana) {
                        $kana = self::getPlayerURI($kana, '');
                        $kana = str_replace(self::PLAYER_URI_BASE, '', $kana);
                        $nodeList = $tmpPath->evaluate(sprintf('.//script[contains(., "%s")]', $kana));
                        foreach ($nodeList as $node) {
                            if (preg_match('/m\("(.+)"\);/', $node->textContent, $match)) {
                                $uri = self::PLAYER_URI_BASE . $match[1];
                                break 2;
                            }
                        }
                    }
                }
            }
            $uriList[$sourceKana] = $uri;
        }
        return $uriList[$sourceKana];
    }

    const LOOKUP_URI = 'http://jisho.org/search/%s?page=%d';

    const PLAYER_LENGTH_MIN = 1000;

    // random??
    const PLAYER_LENGTH_MAX = 50000;

    // 52288; //"the audio for this file is currently unavailable."
    const PLAYER_URI = 'http://assets.languagepod101.com/dictionary/japanese/audiomp3.php?kana=%s&kanji=%s';

    const PLAYER_URI_OLD = 'http://www.csse.monash.edu.au/~jwb/audiock.swf?u=kana=%skanji=%s';

    const PLAYER_URI_BASE = 'http://www.csse.monash.edu.au/~jwb/audiock.swf?u=';

    const PLAYER_URI_SEARCH = 'http://www.csse.monash.edu.au/~jwb/cgi-bin/wwwjdic.cgi?1E';

    const ELE_ROOT = 'translator';

    const ELE_TEXT = 'translation';

    const ELE_WORD = 'word';

    const ATTR_WORDS = 'text';

    const ATTR_SYLLABLECOUNT = 'syllables';

    public function __construct()
    {
        $this->options['commonOnly'] = true;
    }

    protected function getTranslationURL($letters, $pageNo)
    {
        $tags = [];
        $tags[] = $letters;
        $tags[] = '#words';
        if ($this->options['commonOnly']) {
            // $tags[] = '#common';
        }
        return sprintf(self::LOOKUP_URI, rawurlencode(implode(' ', $tags)), $pageNo);
    }

    public function translateWord($word, &$nextWord)
    {
        $ret = null;
        for ($wordLength = count($word); $wordLength > 0; $wordLength --) {
            $tryWord = array_slice($word, 0, $wordLength);
            $res = $this->lookupWord($tryWord);
            if ($res !== null) {
                $ret = $res;
                break;
            }
        }
        if ($wordLength === 0) {
            $wordLength = 1;
        }
        if ($wordLength < count($word)) {
            $nextWord = array_slice($word, $wordLength);
        }
        return $ret;
    }

    protected function lookupWord($word)
    {
        $letters = implode('', $word);
        $hasTranslation = false;
        
        $ret = [];
        $ret['word'] = $letters;
        $ret['syllables'] = count($word);
        $ret['translation'] = [];
        
        for ($pageNo = 1; $pageNo < 10; $pageNo ++) {
            $uri = $this->getTranslationURL($letters, $pageNo);
            $xpath = Storage::loadExternalXPath($uri, Seconds::MONTH);
            if (! $xpath) {
                break;
            }
            $primaryNode = $xpath->evaluate('//*[@id="primary"][*]')->item(0);
            if (! $primaryNode) {
                break;
            }
            $translationNodeList = $xpath->evaluate('.//*[@class="exact_block"]/*[@class]', $primaryNode);
            if (! $translationNodeList->length) {
                break;
            }
            
            foreach ($translationNodeList as $translationNode) {
                /*
                 * $kana = $xpath->evaluate('normalize-space(.//*[@class="concept_light-representation"]/*[@class="furigana"])', $translationNode);
                 * $kanji = $xpath->evaluate('normalize-space(.//*[@class="concept_light-representation"]/*[@class="text"])', $translationNode);
                 * //
                 */
                $kanaList = [];
                foreach ($xpath->evaluate('.//*[@class="concept_light-representation"]/*[@class="furigana"]/*', $translationNode) as $node) {
                    $kanaList[] = $xpath->evaluate('normalize-space(.)', $node);
                }
                // *
                $kanjiList = [];
                foreach ($xpath->evaluate('.//*[@class="concept_light-representation"]/*[@class="text"]/node()', $translationNode) as $node) {
                    $isElement = $node->nodeType === XML_ELEMENT_NODE;
                    $kanji = $xpath->evaluate('normalize-space(.)', $node);
                    $kanji = $isElement ? [
                        $kanji
                    ] : preg_split("//u", $kanji, - 1, PREG_SPLIT_NO_EMPTY);
                    foreach ($kanji as $val) {
                        $kanjiList[] = $isElement ? $val : null;
                    }
                }
                // */
                $kanji = $xpath->evaluate('normalize-space(.//*[@class="concept_light-representation"]/*[@class="text"])', $translationNode);
                // $kanjiList = preg_split("//u", $kanji, -1, PREG_SPLIT_NO_EMPTY);
                
                foreach ($kanaList as $i => &$kana) {
                    if (strlen($kana) === 0) {
                        $kana = isset($kanjiList[$i]) ? $kanjiList[$i] : '';
                    }
                }
                unset($kana);
                
                $kana = trim(implode('', $kanaList));
                // $kanji = trim(implode('', $kanjiList));
                
                $englishNodeList = $xpath->evaluate('.//*[@class="meaning-meaning"][not(*[@class="break-unit"])]/node()', $translationNode);
                
                if ($this->options['commonOnly']) {
                    $isCommon = $xpath->evaluate('boolean(.//*[@class="concept_light-tag concept_light-common success label"])', $translationNode);
                    if (! $isCommon) {
                        continue;
                    }
                }
                
                if (! isset($ret['translation'][$kana])) {
                    $ret['translation'][$kana] = [];
                }
                if (! isset($ret['translation'][$kana][$kanji])) {
                    $ret['translation'][$kana][$kanji] = [];
                }
                
                foreach ($englishNodeList as $englishNode) {
                    // $key = $xpath->evaluate('normalize-space(.)', $englishNode);
                    $val = $englishNode->ownerDocument->saveXML($englishNode);
                    $key = md5($val);
                    if (! isset($ret['translation'][$kana][$kanji][$key])) {
                        $ret['translation'][$kana][$kanji][$key] = sprintf('<div xmlns="%s">%s</div>', DOMHelper::NS_HTML, $val);
                        $hasTranslation = true;
                    }
                }
            }
        }
        return $hasTranslation ? $ret : null;
    }
}
