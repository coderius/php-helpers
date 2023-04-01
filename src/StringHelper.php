<?php
namespace PhpHelpers;

class StringHelper
{
    public static function byteLength($string)
    {
        return mb_strlen((string)$string, '8bit');
    }

    public static function byteSubstr($string, $start, $length = null)
    {
        if ($length === null) {
            $length = static::byteLength($string);
        }

        return mb_substr($string, $start, $length, '8bit');
    }

    public static function basename($path, $suffix = '')
    {
        $len = mb_strlen($suffix);
        if ($len > 0 && mb_substr($path, -$len) === $suffix) {
            $path = mb_substr($path, 0, -$len);
        }

        $path = rtrim(str_replace('\\', '/', $path), '/');
        $pos = mb_strrpos($path, '/');
        if ($pos !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    public static function dirname($path)
    {
        $normalizedPath = rtrim(
            str_replace('\\', '/', $path),
            '/'
        );
        $separatorPosition = mb_strrpos($normalizedPath, '/');

        if ($separatorPosition !== false) {
            return mb_substr($path, 0, $separatorPosition);
        }

        return '';
    }

    public static function truncate($string, $length, $suffix = '...', $encoding = null, $asHtml = false)
    {
        if ($encoding === null) {
            $encoding = 'UTF-8';
        }
        if ($asHtml) {
            return static::truncateHtml($string, $length, $suffix, $encoding);
        }

        if (mb_strlen($string, $encoding) > $length) {
            return rtrim(mb_substr($string, 0, $length, $encoding)) . $suffix;
        }

        return $string;
    }

    public static function truncateWords($string, $count, $suffix = '...', $asHtml = false)
    {
        if ($asHtml) {
            return static::truncateHtml($string, $count, $suffix);
        }

        $words = preg_split('/(\s+)/u', trim($string), 0, PREG_SPLIT_DELIM_CAPTURE);
        if (count($words) / 2 > $count) {
            return implode('', array_slice($words, 0, ($count * 2) - 1)) . $suffix;
        }

        return $string;
    }

    protected static function truncateHtml($string, $count, $suffix, $encoding = false)
    {
        $config = \HTMLPurifier_Config::create(null);
        $lexer = \HTMLPurifier_Lexer::create($config);
        $tokens = $lexer->tokenizeHTML($string, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $depth = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                $openTokens[$depth] = $token->name;
                $truncated[] = $token;
                ++$depth;
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                if (false === $encoding) {
                    preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['', ''];
                    $token->data = $prefixSpace[1] . self::truncateWords(ltrim($token->data), $count - $totalCount, '');
                    $currentCount = self::countWords($token->data);
                } else {
                    $token->data = self::truncate($token->data, $count - $totalCount, '', $encoding);
                    $currentCount = mb_strlen($token->data, $encoding);
                }
                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if ($token->name === $openTokens[$depth - 1]) {
                    --$depth;
                    unset($openTokens[$depth]);
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if ($totalCount >= $count) {
                if (0 < count($openTokens)) {
                    krsort($openTokens);
                    foreach ($openTokens as $name) {
                        $truncated[] = new \HTMLPurifier_Token_End($name);
                    }
                }
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);
        return $generator->generateFromTokens($truncated) . ($totalCount >= $count ? $suffix : '');
    }

    public static function startsWith($string, $with, $caseSensitive = true)
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            return strncmp($string, $with, $bytes) === 0;
        }

        $encoding = 'UTF-8';
        $string = static::byteSubstr($string, 0, $bytes);

        return mb_strtolower($string, $encoding) === mb_strtolower($with, $encoding);
    }

    public static function endsWith($string, $with, $caseSensitive = true)
    {
        if (!$bytes = static::byteLength($with)) {
            return true;
        }
        if ($caseSensitive) {
            // Warning check, see https://php.net/substr-compare#refsect1-function.substr-compare-returnvalues
            if (static::byteLength($string) < $bytes) {
                return false;
            }

            return substr_compare($string, $with, -$bytes, $bytes) === 0;
        }

        $encoding = 'UTF-8';
        $string = static::byteSubstr($string, -$bytes);

        return mb_strtolower($string, $encoding) === mb_strtolower($with, $encoding);
    }

    public static function explode($string, $delimiter = ',', $trim = true, $skipEmpty = false)
    {
        $result = explode($delimiter, $string);
        if ($trim !== false) {
            if ($trim === true) {
                $trim = 'trim';
            } elseif (!is_callable($trim)) {
                $trim = function ($v) use ($trim) {
                    return trim($v, $trim);
                };
            }
            $result = array_map($trim, $result);
        }
        if ($skipEmpty) {
            // Wrapped with array_values to make array keys sequential after empty values removing
            $result = array_values(array_filter($result, function ($value) {
                return $value !== '';
            }));
        }

        return $result;
    }

    public static function countWords($string)
    {
        return count(preg_split('/\s+/u', $string, 0, PREG_SPLIT_NO_EMPTY));
    }

    public static function normalizeNumber($value)
    {
        $value = (string) $value;

        $localeInfo = localeconv();
        $decimalSeparator = isset($localeInfo['decimal_point']) ? $localeInfo['decimal_point'] : null;

        if ($decimalSeparator !== null && $decimalSeparator !== '.') {
            $value = str_replace($decimalSeparator, '.', $value);
        }

        return $value;
    }

    public static function base64UrlEncode($input)
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    public static function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function floatToString($number)
    {
        // . and , are the only decimal separators known in ICU data,
        // so its safe to call str_replace here
        return str_replace(',', '.', (string) $number);
    }

    public static function matchWildcard($pattern, $string, $options = [])
    {
        if ($pattern === '*' && empty($options['filePath'])) {
            return true;
        }

        $replacements = [
            '\\\\\\\\' => '\\\\',
            '\\\\\\*' => '[*]',
            '\\\\\\?' => '[?]',
            '\*' => '.*',
            '\?' => '.',
            '\[\!' => '[^',
            '\[' => '[',
            '\]' => ']',
            '\-' => '-',
        ];

        if (isset($options['escape']) && !$options['escape']) {
            unset($replacements['\\\\\\\\']);
            unset($replacements['\\\\\\*']);
            unset($replacements['\\\\\\?']);
        }

        if (!empty($options['filePath'])) {
            $replacements['\*'] = '[^/\\\\]*';
            $replacements['\?'] = '[^/\\\\]';
        }

        $pattern = strtr(preg_quote($pattern, '#'), $replacements);
        $pattern = '#^' . $pattern . '$#us';

        if (isset($options['caseSensitive']) && !$options['caseSensitive']) {
            $pattern .= 'i';
        }

        return preg_match($pattern, (string)$string) === 1;
    }

    public static function mb_ucfirst($string, $encoding = 'UTF-8')
    {
        $firstChar = mb_substr((string)$string, 0, 1, $encoding);
        $rest = mb_substr((string)$string, 1, null, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $rest;
    }

    public static function mb_ucwords($string, $encoding = 'UTF-8')
    {
        $string = (string) $string;
        if (empty($string)) {
            return $string;
        }

        $parts = preg_split('/(\s+\W+\s+|^\W+\s+|\s+)/u', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $ucfirstEven = trim(mb_substr($parts[0], -1, 1, $encoding)) === '';
        foreach ($parts as $key => $value) {
            $isEven = (bool)($key % 2);
            if ($ucfirstEven === $isEven) {
                $parts[$key] = static::mb_ucfirst($value, $encoding);
            }
        }

        return implode('', $parts);
    }
}
