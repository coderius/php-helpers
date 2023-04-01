<?php

namespace Tests\Unit;

use Tests\TestCase;
use PhpHelpers\ArrayHelper;
use PhpHelpers\Exceptions\InvalidArgumentException;
use stdClass;

class ArrayHelperTest extends TestCase
{
    public function testToArray()
    {
        $object = $this->getObject(1);
        $object2 = $this->getObject(2);

        $conf = ['stdClass' => [
            'text',
            'desc',
            'leng' => function ($object) {
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

    public function testMerge()
    {
        $arr = ArrayHelper::merge(['foo', 'bar'], ['x', 'y']);
        $this->assertSame($arr, ['foo', 'bar', 'x', 'y']);

        $arr = ArrayHelper::merge(['foo' => 123, 'bar'], ['foo' => 789, 'y']);
        $this->assertSame($arr, ['foo' => 789, 'bar', 'y']);
    }


    public function testGetValue()
    {
        $object = new \stdClass();
        $object->extra = 'extra';

        $array = [
            'foo' => [
                'bar' => $object,
            ]
        ];

        $this->assertSame(ArrayHelper::getValue($array, 'foo.bar.extra'), 'extra');
    }

    // setValue
    public function testSetValue()
    {
        $array = [
            'foo' => [
                'bar' => 'extra',
            ]
        ];

        $res = [
            'foo' => [
                'bar' => ['arr' => 'val'],
            ]
        ];
        ArrayHelper::setValue($array, 'foo.bar', ['arr' => 'val']);
        $this->assertSame($array, $res);
    }

    //remove
    public function testRemove()
    {
        $array = ['type' => 'A', 'options' => [1, 2]];
        ArrayHelper::remove($array, 'type');
        $this->assertSame($array, ['options' => [1, 2]]);
    }

    //removeValue
    public function testRemoveValue()
    {
        $array = ['type' => 'A', 'options' => [1, 2]];
        ArrayHelper::removeValue($array, 'A');
        $this->assertSame($array, ['options' => [1, 2]]);
    }

    //index
    public function testIndex()
    {
        $array = [
            ['id' => '123', 'data' => 'abc'],
            ['id' => '345', 'data' => 'def'],
        ];

        $output = ArrayHelper::index($array, 'id');

        $result = [
            '123' => ['id' => '123', 'data' => 'abc'],
            '345' => ['id' => '345', 'data' => 'def'],
        ];

        $this->assertSame($output, $result);

    }

    //getColumn
    public function testGetColumn()
    {
        $array = [
            ['id' => '123', 'data' => 'abc'],
            ['id' => '345', 'data' => 'def'],
        ];
        $output = ArrayHelper::getColumn($array, 'id');
        $result = ['123', '345'];

        $this->assertSame($output, $result);
    }

    //map
    public function testMap()
    {
        $array = [
            ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
            ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
            ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
        ];
        
        $output = ArrayHelper::map($array, 'id', 'name');
        $result = [
            '123' => 'aaa',
            '124' => 'bbb',
            '345' => 'ccc',
        ];

        $this->assertSame($output, $result);

        $output2 = ArrayHelper::map($array, 'id', 'name', 'class');
        $result2 = [
            'x' => [
                '123' => 'aaa',
                '124' => 'bbb',
            ],
            'y' => [
                '345' => 'ccc',
            ],
        ];
        $this->assertSame($output2, $result2);
    }

    //keyExists
    public function testKeyExists()
    {
        $array = ['one' => 'some', 'two' => 'else'];
        $res = ArrayHelper::keyExists('two', $array);
       
        $array2 = ['one' => 'some', 'two' => 'else'];
        $res2 = ArrayHelper::keyExists('Two', $array2, false);
        $this->assertTrue($res);
        $this->assertTrue($res2);
    }

    public function testKeyExistsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $array = ['one' => 'some', 'two' => 'else'];
        ArrayHelper::keyExists('two', (new \ArrayObject($array)),false);
    }

    //multisort
    public function testMultisort()
    {
        $data = [
            ['age' => 30, 'name' => 'Alexander'],
            ['age' => 30, 'name' => 'Brian'],
            ['age' => 19, 'name' => 'Barney'],
        ];
        
        ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);

        $result = [
            ['age' => 19, 'name' => 'Barney'],
            ['age' => 30, 'name' => 'Brian'],
            ['age' => 30, 'name' => 'Alexander'],
        ];

        $this->assertSame($data, $result);
        //----------
        $data1 = [
            ['age' => 30, 'name' => 'Alexander'],
            ['age' => 30, 'name' => 'Brian'],
            ['age' => 19, 'name' => 'Barney'],
        ];

        ArrayHelper::multisort($data1, function($item) {
            return isset($item['age']) ? ['age', 'name'] : 'name';
        });

        $result = [
            0 => [
                'age' => 30,
                'name' => 'Alexander',
            ],
            1 => [
                'age' => 30,
                'name' => 'Brian',
            ],
            2 => [
                'age' => 19,
                'name' => 'Barney',
            ],
        ];

        $this->assertSame($data1, $result);

    }

    //htmlEncode
    public function testHtmlEncode()
    {
        $data = ['string' => "<a href='test'>Test</a>"];
        $encoded = ArrayHelper::htmlEncode($data);
        $result = [
            'string' => "&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;",
        ];
        $this->assertSame($encoded, $result);

    }

    //htmlDecode
    public function testHtmlDecode()
    {
        $data = [
            'string' => "&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;",
        ];
        $decoded = ArrayHelper::htmlDecode($data);
        $result = ['string' => "<a href='test'>Test</a>"];
        
        $this->assertSame($decoded, $result);
    }

    //isAssociative
    public function testIsAssociative()
    {
        $this->assertTrue( ArrayHelper::isAssociative(['one' => 'some', 'two' => 'else']) );
    }

    //isIndexed
    public function testIsIndexed()
    {
        $this->assertTrue( ArrayHelper::isIndexed(['Jne', 'Twoi']) );
    }

    //isIn
    public function testisIn()
    {
        $this->assertTrue(ArrayHelper::isIn('a', ['a']));
        $this->assertTrue(ArrayHelper::isIn('a', (new \ArrayObject(['a']))));
    }

    public function testisInInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArrayHelper::isIn('a', 'a');
    }

    //isTraversable
    public function testIsTraversable()
    {
        $var = (new \ArrayObject(['a', 'c']));
        $this->assertTrue(ArrayHelper::isTraversable($var));
    }

    //isSubset
    public function testIsSubset()
    {
        // true
        $this->assertTrue(ArrayHelper::isSubset((new \ArrayObject(['a', 'c'])), (new \ArrayObject(['a', 'b', 'c']))));
    }

    public function testIsSubsetInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ArrayHelper::isSubset('string', (new \ArrayObject(['a', 'b', 'c'])));
    }

    //filter
    public function testFilter()
    {
        $array = [
            'A' => [1, 2],
            'B' => [
                'C' => 1,
                'D' => 2,
            ],
            'E' => 1,
        ];
        
        $output1 = ArrayHelper::filter($array, ['A']);
        $result1 = [
            'A' => [1, 2],
        ];
        
        $output2 = ArrayHelper::filter($array, ['A', 'B.C']);
        $result2 = [
            'A' => [1, 2],
            'B' => ['C' => 1],
        ];
        
        $output3 = ArrayHelper::filter($array, ['B', '!B.C']);
        $result3 = [
            'B' => ['D' => 2],
        ];

        $this->assertSame($output1, $result1);
        $this->assertSame($output2, $result2);
        $this->assertSame($output3, $result3);

    }

    //recursiveSort
    public function testRecursiveSort()
    {
        $array = array("lemon", "orange", "banana", "apple");
        ArrayHelper::recursiveSort($array);
        $result = [
            0 => 'apple',
            1 => 'banana',
            2 => 'lemon',
            3 => 'orange',
        ];
        $this->assertSame($array, $result);
        // ---------
        $array2 = array("lemon", "orange", "banana", "apple");
        ArrayHelper::recursiveSort($array2, 'rsort');
        $result2 = [
            0 => 'orange',
            1 => 'lemon',
            2 => 'banana',
            3 => 'apple',
        ];
        $this->assertSame($array2, $result2);
    }


}
