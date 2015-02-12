<?php
/**
 * Created by PhpStorm.
 * User: hikaru
 * Date: 14/05/13
 * Time: 3:03
 */

/**
 * 変数が空文字かどうか判定します
 * @param mixed $var
 * @return bool 引数が空文字の場合TRUE、そうでない場合FALSEを返します
 */
function fk_is_blank($var)
{
	return $var === '';
}

/**
 * 変数が空文字以外の文字列、または数値かどうか判定します
 * @param mixed $var
 * @return bool 引数が空文字以外の文字列、または数値の場合TRUE、どちらでもない場合FALSEを返します
 */
function fk_is_solid_string($var)
{
	return (is_string($var) || is_numeric($var)) && strlen((string)$var) > 0;
}

function fk_is_valid_index($var)
{
	return is_int($var) || (is_numeric($var) && ($var === '0' || intval($var)));
}

