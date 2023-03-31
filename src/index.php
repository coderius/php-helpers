<?php
require __DIR__.'/../vendor/autoload.php';
use PhpHelpers\ArrayHelper;

$object = new \stdClass();
$object->text = 'Lorem ipsum... ';
$object->desc = 'Lorem ipsum desc... ';
$object->extra = 'extra';

$arr = $object;
        
$conf = ['stdClass' => [
    'text',
    'desc',
    'leng' => function($object) {
        return strlen($object->extra);
    },
    
]];

$output = ArrayHelper::toArray($arr, $conf, $recursive = true);

var_dump($output);