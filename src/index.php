<?php
require __DIR__.'/../vendor/autoload.php';
use PhpHelpers\ArrayHelper;

$object = new \stdClass();
$object->text = 'Lorem ipsum... ';
$object->desc = 'Lorem ipsum desc... ';
$object->extra = 'extra';

$array = array("lemon", "orange", "banana", "apple");

$res = ArrayHelper::recursiveSort($array);

print_r($array);