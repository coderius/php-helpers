PHP HELPERS
============

# Сontains helper classes:

- **ArrayHelper**

### ArrayHelper usage:

* ArrayHelper::toArray($object, $properties = [], $recursive = true)
* ArrayHelper::merge($ar, $ar2)
* ArrayHelper::getValue($array, $key, $default = null)
* ArrayHelper::setValue(&$array, $path, $value)
* ArrayHelper::remove(&$array, $key, $default = null)
* ArrayHelper::removeValue(&$array, $value)
* ArrayHelper::index($array, $key, $groups = [])
* ArrayHelper::getColumn($array, $name, $keepKeys = true)
* ArrayHelper::map($array, $from, $to, $group = null)
* ArrayHelper::keyExists($key, $array, $caseSensitive = true)
* ArrayHelper::multisort(&$array, $key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
* ArrayHelper::htmlEncode($data, $valuesOnly = true, $charset = null)
* ArrayHelper::htmlDecode($data, $valuesOnly = true)
* ArrayHelper::isAssociative($array, $allStrings = true)
* ArrayHelper::isIndexed($array, $consecutive = false)
* ArrayHelper::isIn($needle, $haystack, $strict = false)
* ArrayHelper::isTraversable($var)
* ArrayHelper::isSubset($needles, $haystack, $strict = false)
* ArrayHelper::filter($array, $filters)
* ArrayHelper::recursiveSort(array &$array, $sorter = null)

Usage examples for ArrayHelper see in tests [tests/Unit/ArrayHelperTest.php](tests/Unit/ArrayHelperTest.php)

## Testing

* Go to root folder in terminal and run tests:
```
php ./vendor/bin/phpunit
```