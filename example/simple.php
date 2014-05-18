<?php

require __DIR__.'/../vendor/autoload.php';

$task = new NeoSpeech\Client('mail@example.com','1234657890','login_key','password');

$resp = $task->convertSimple('Hello, World!');

var_dump($resp);

$resp2 = $task->getConversionStatus($resp['conversionNumber']);

var_dump($resp2);