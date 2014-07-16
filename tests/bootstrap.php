<?php

date_default_timezone_set('UTC');
/**
 * If xdebug is installed a test for infinite template inclusion will fail with the default `max_nesting_level` ( 100 )
 */
ini_set('xdebug.max_nesting_level', 1000);

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Liquid\\Tests\\', __DIR__);


$mode = \Liquid\Liquid::ERROR_MODE_STRICT;

if ($env_mode = getenv('LIQUID_PARSER_MODE')) {
    echo '-- ' . strtoupper($env_mode) . " ERROR MODE \n";
    $mode = $env_mode;
}

\Liquid\Template::error_mode($mode);


function debug($data = null){
    $calledFrom = debug_backtrace();
    $caller = substr(str_replace(dirname(dirname(__DIR__)), '', $calledFrom[0]['file']), 1);
    $line = $calledFrom[0]['line'];

    if (PHP_SAPI == 'cli') {
        echo "\n>>> {$caller} (line {$line})\n";
        print_r($data);
        echo "\n<<<\n";
    } else {
        echo "<pre><strong>{$caller}</strong>";
        echo " (line <strong>{$line}</strong>)";
        echo "\n". str_replace('<', '&lt;', str_replace('>', '&gt;', print_r($data, true))) . "\n</pre>\n";
    }
}
