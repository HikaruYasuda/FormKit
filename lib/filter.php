<?php

namespace FormKit;

/**
 * フォームフィルタクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.1
 * @version 1.0.0
 */
class Filter
{
    /** @var string */
    public $name;
    /** @var callable */
    public $func;
    /** @var \ReflectionFunctionAbstract */
    public $ref;
    public $assignField = null;

    /**
     * @param string $name
     * @param callable $func
     */
    public function __construct($name, $func)
    {
        $this->name = $name;
        $this->func = $func;
    }

    /**
     * @param bool $enable
     * @return static
     */
    public function assignField($enable = null)
    {
        $this->assignField = $enable;
        return $this;
    }

    /**
     * @param callable $func
     * @return \ReflectionFunction|\ReflectionMethod
     */
    protected static function getReflection($func)
    {
        if (is_string($func) and strpos($func, '::') !== false) {
            list($class, $method) = explode('::', $func);
            $ref = new \ReflectionMethod($class, $method);
        } elseif (is_array($func)) {
            list($class, $method) = $func;
            $ref = new \ReflectionMethod($class, $method);
        } else {
            $ref = new \ReflectionFunction($func);
        }
        return $ref;
    }

    /**
     * @param \ReflectionFunctionAbstract $ref
     * @return string|null
     */
    protected static function getFirstArgName($ref)
    {
        if ($ref->getNumberOfParameters() > 0) {
            $params = $ref->getParameters();
            return $params[0]->name;
        }
        return null;
    }

    /**
     * @param Field $field
     * @param mixed $value
     * @param array $args
     * @return bool
     */
    public function apply(Field $field, $value, $args)
    {
        if (is_null($this->assignField)) {
            $ref = static::getReflection($this->func);
            $this->assignField = (static::getFirstArgName($ref) === 'field');
        }
        array_unshift($args, $value);
        $this->assignField and array_unshift($args, $field);
        return call_user_func_array($this->func, $args);
    }

    // =================================
    // Built-in Filters
    // =================================

    /**
     * @param Field $field
     * @param $value
     * @return string
     * @assignField
     */
    public static function in_options(Field $field, $value)
    {
        $options = $field->options();
        $keys = array_map('strval', array_keys($options));
        if ( ! in_array(strval($value), $keys)) {
            return $value;
        }
        return null;
    }

    /**
     * @param $value
     * @return mixed
     * @checkBlank
     */
    public static function empty_is_null($value)
    {
        return $value === '' ? null : $value;
    }

    /**
     * 整数形式に変換します
     *
     * @param $value
     * @return int|null
     */
    public static function int($value)
    {
        is_object($value) and (!method_exists($value, '__toString')) and ($value = null);
        is_array($value) and ($value = null);
        $value === '' and ($value = null);
        if (is_null($value)) return null;
        return (int)$value;
    }

    /**
     * 小数点数形式に変換します
     *
     * @param $value
     * @return float|null
     */
    public static function float($value)
    {
        is_object($value) and (!method_exists($value, '__toString')) and ($value = null);
        is_array($value) and ($value = null);
        $value === '' and ($value = null);
        if (is_null($value)) return null;
        return (float)$value;
    }

    /**
     * 文字列形式に変換します
     *
     * @param $value
     * @return string|null
     */
    public static function string($value)
    {
        is_object($value) and (!method_exists($value, '__toString')) and ($value = null);
        is_array($value) and ($value = null);
        if (is_null($value)) return null;
        return (string)$value;
    }

    public static function trim($value, $charList = " \t\n\r\0\x0B")
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return trim($value, $charList);
    }

    public static function rtrim($value, $charList = " \t\n\r\0\x0B")
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return rtrim($value, $charList);
    }

    public static function ltrim($value, $charList = " \t\n\r\0\x0B")
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return ltrim($value, $charList);
    }

    /**
     * 文字列置換します
     *
     * @param string $value
     * @param string $search
     * @param string $replace
     * @param int $count
     * @return string
     */
    public static function replace($value, $search, $replace, &$count = null)
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return str_replace($search, $replace, $value, $count);
    }

    /**
     * 大文字英字にします
     *
     * @param $value
     * @return string
     * @see mb_strtoupper
     */
    public static function upper($value)
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return mb_strtoupper($value);
    }
    /**
     * 小文字英字にします
     *
     * @param $value
     * @return string
     * @see mb_strtolower
     */
    public static function lower($value)
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return mb_strtolower($value);
    }
    /**
     * 最初に現れた正規表現に合う文字列のみを抜き出します
     *
     * @param $value
     * @param $pattern
     * @return string
     */
    public static function regex($value, $pattern)
    {
        $value = static::string($value);
        if (is_null($value)) return null;
        return preg_match($pattern, $value, $matches) ? $matches[1] : '';
    }
    /**
     * 最初に現れたカナ文字列のみを抜き出します
     *
     * @param $value
     * @param string $pattern
     * @return string
     */
    public static function kana($value, $pattern = '/([ァ-ヶー　０-９]*)/u')
    {
        return static::regex($value, $pattern);
    }
    /**
     * 日付時分形式にします
     *
     * @param $value
     * @param string $format
     * @return string|null
     */
    public static function date($value, $format = 'Y/m/d')
    {
        if ($value) {
            if (is_int($value) or ctype_digit($value)) {
                return date($format, $value);
            } elseif (is_string($value)) {
                if ($date = date_create_from_format($format, $value)) {
                    return $date->format($format);
                } elseif ($date = date_create_from_format(str_replace('/', '-', $format), $value)) {
                    return $date->format($format);
                } elseif ($date = date_create_from_format(str_replace('-', '/', $format), $value)) {
                    return $date->format($format);
                }
            }
        }
        return null;
    }
    /**
     * 日付時分形式にします
     *
     * @param $value
     * @param string $format
     * @return string|null
     */
    public static function datehm($value, $format = 'Y/m/d H:i')
    {
        return static::date($value, $format);
    }
    /**
     * 日時形式にします
     *
     * @param $value
     * @param string $format
     * @return string|null
     */
    public static function datetime($value, $format = 'Y/m/d H:i')
    {
        return static::date($value, $format);
    }
}
