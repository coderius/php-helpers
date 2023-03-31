<?php

namespace Tests\Unit;

use Tests\TestCase;
use PhpHelpers\ArrayHelper;

class ArrayHelperTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    // public function testBasicTest()
    // {
    //     $this->assertTrue(true);
    // }

    public function testToArray()
    {
        $object = $this->getObject(1);
        $object2 = $this->getObject(2);

        $conf = ['stdClass' => [
            'text',
            'desc',
            'leng' => function($object) {
                return strlen($object->extra);
            },
        ]];

        $output = ArrayHelper::toArray($object, $conf, $recursive = true);
        
        $output2 = ArrayHelper::toArray($object2, $conf, $recursive = true);

        $result = [
            'text' => 'Lorem ipsum... 1',
            'desc' => 'Lorem ipsum desc... 1',
            'leng' => 5
        ];

        $this->assertEqualsCanonicalizing($output, $result);
        $this->assertNotEqualsCanonicalizing($output2, $result);
    }

    private function getObject($value)
    {
        $object = new \stdClass();
        $object->text = 'Lorem ipsum... ' . $value;
        $object->desc = 'Lorem ipsum desc... ' . $value;
        $object->extra = 'extra';


        return $object;
    }

}