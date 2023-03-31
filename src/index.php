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

$a = ['k1' => 'v1', ['k2' => 22]];
        $b = ['d1' => 'v1', ['d2' => 2]];
        $output = ArrayHelper::merge($a, $b);
 
        print_r(['k1' => 'v1', ['k2' => 22],'d1' => 'v1', ['d2' => 2]]);        

// print_r([['k1'] => 'v1',
//     ['k1'] => 'v1',
//     [
//         ['k2'] => 22,
//     ],
//     ['d1'] => 'v1',
//     [
//         ['d2'] => 2,
//     ],
// ]);