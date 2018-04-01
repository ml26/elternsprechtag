<?php

class Logger
{
    public static function Info($f3, $source, $text) {
       Logger::Write($f3, $source, $text, 'INFO');
    }
    
    public static function Warn($f3, $source, $text) {
       Logger::Write($f3, $source, $text, 'WARN');
    }
    
    public static function Error($f3, $source, $text) {
       Logger::Write($f3, $source, $text, 'ERROR');
    }
    
    private static function Write($f3, $source, $text, $type) {
        $f3->get('log')->write("{$type} - {$source} - {$text}");
    }
    
    public static function Initialize($f3) {
        $f3->set('log', new Log('log.log'));
//         Logger::Write($f3, 'init', 'init', 'I');
    }
}


?>
