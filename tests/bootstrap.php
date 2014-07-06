<?php

date_default_timezone_set('UTC');

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Liquid\\Tests\\', __DIR__);


$mode = \Liquid\Liquid::ERROR_MODE_STRICT;

if ($env_mode = getenv('LIQUID_PARSER_MODE')) {
    echo '-- ' . strtoupper($env_mode) . " ERROR MODE \n";
    $mode = $env_mode;
}
\Liquid\Template::error_mode($mode);
