<?php

if (0) {
    // class access assistants
    class FK extends \FormKit\FormKit {}
    class Form extends \FormKit\Form {}
    class Field extends \FormKit\Field {}
    class Rule extends \FormKit\Rule {}
    class Filter extends \FormKit\Filter {}
}


/**
 * 変数が空文字かどうか判定します
 * @param mixed $var
 * @return bool 引数が空文字の場合TRUE、そうでない場合FALSEを返します
 */
function fk_blank($var)
{
    return $var === '';
}

function fk_blankOrNull($var)
{
    return $var === null or $var === '';
}

/**
 * 変数が空文字以外の文字列、または数値かどうか判定します
 * @param mixed $var
 * @return bool 引数が空文字以外の文字列、または数値の場合TRUE、どちらでもない場合FALSEを返します
 */
function fk_isSolidString($var)
{
    return (is_string($var) and $var !== '') or is_int($var) or is_float($var);
}

/**
 * @param $var
 * @return bool 引数が0以上の整数値または整数文字列の場合true、どちらでもない場合falseを返します
 */
function fk_isValidIndex($var)
{
    return (is_int($var) or ctype_digit($var)) and $var >= 0;
}

/**
 * @param mixed $value
 * @param string $path
 * @return mixed
 */
function fk_pathGet($value, $path)
{
    $paths = explode('/', $path);
    foreach ($paths as $p) {
        if (!is_array($value) or !isset($value[$p])) {
            return null;
        }
        $value = $value[$p];
    }
    return $value;
}

function fk_escapeExplode($delimiter, $string, $escape = '', $replace = '#+@+#')
{
    if (empty($escape)) {
        return explode($delimiter, $string);
    }
    $split = explode($delimiter, str_replace($escape, $replace, $string));
    return array_map(function($s) use ($replace, $escape) {
        return str_replace($replace, $escape, $s);
    }, $split);
}

function fk_h($string)
{
    return htmlspecialchars($string, ENT_QUOTES);
}

function fk_toStringArray($var)
{
    if (is_null($var)) {
        return array();
    }
    return array_map(function($v) {
        return (string)$v;
    }, (array)$var);
}
