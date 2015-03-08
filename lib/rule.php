<?php
namespace FormKit;

class Rule
{
    /** @var string */
    public $name;
    /** @var callable */
    public $func;
    /** @var bool TRUEの場合、値が空文字やNULLの場合であっても判定処理をします */
    public $checkBlank = false;
    /** @var bool TRUEの場合、配列は分割した要素ごとに判定処理をします */
    public $arraySeparate = true;
    /** @var string[] */
    public $messages = array();

    /**
     * @param string $name
     * @param callable $func
     * @param string $message
     * @param string $lang
     */
    public function __construct($name, $func, $message = '', $lang = 'ja')
    {
        $this->name = $name;
        $this->func = $func;
        $this->messages[$lang] = $message;
    }

    /**
     * @param bool $enable
     * @return static
     */
    public function checkBlank($enable = null)
    {
        $this->checkBlank = $enable;
        return $this;
    }

    /**
     * @param bool $enable
     * @return static
     */
    public function arraySeparate($enable = null)
    {
        $this->arraySeparate = $enable;
        return $this;
    }

    /**
     * @param callable $func
     * @return static
     */
    public function setFunc($func)
    {
        $this->func = $func;
        return $this;
    }

    /**
     * @param string $message
     * @param string $lang
     * @return static
     */
    public function setMessage($message, $lang = '')
    {
        $lang or $lang = FormKit::$lang;
        $this->messages[$lang] = $message;
        return $this;
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @param string $lang
     * @return string
     */
    public function getMessage($fieldName, $args, $lang = '')
    {
        $lang or $lang = FormKit::$lang;
        if (isset($this->messages[$lang])) {
            $message = $this->messages[$lang];
        } else {
            $message = '$0 has wrong';
        }
        $message = str_replace("$0", $fieldName, $message);
        foreach ($args as $index => $arg) {
            $message = str_replace('$'.$index, $arg, $message);
        }
        return $message;
    }

    /**
     * @param Field $field
     * @param array $args
     * @return bool
     */
    public function run(Field $field, $args)
    {

    }

    /**
     * @return array
     */
    public static function getBuiltInRules()
    {
        $refClass = new \ReflectionClass(get_called_class());
        $rules = array();
        foreach ($refClass->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->name;
            $message = '';
            $rule[$name] = new static($name, "{$method->class}::$name", $message);
        }
        return $rules;
    }

    // =================================
    // Built-in Rules
    // =================================

    /**
     * 数値上限下限チェック
     * @param $val
     * @param $min
     * @param $max
     * @return bool
     * @errorMessage
     */
    public static function between($val, $min, $max)
    {
        return ((int)$min <= (int)$val and (int)$val <= (int)$max);
    }

    /**
     * 数値下限チェック
     * @param $value
     * @param $min
     * @return bool
     */
    public function min($value, $min)
    {
        return (int)$value >= (int)$min;
    }

    /** 数値上限チェック */
    public function max($value, $max) { return (int)$value <= (int)$max; }

    /** 文字数上限下限チェック */
    public function length($value, $min, $max) { $mb_len = mb_strlen($value); return ((int)$min <= $mb_len && $mb_len <= (int)$max); }

    /** 文字数上限チェック */
    public function minlength($value, $min) { $mb_len = mb_strlen($value); return $mb_len >= (int)$min; }

    /** 文字数上限チェック */
    public function maxlength($value, $max) { $mb_len = mb_strlen($value); return $mb_len <= (int)$max; }

    /** 正規表現チェック */
    public function regex($value, $pattern) {
        $args = func_get_args();
        array_shift($args);
        return (bool)preg_match(implode(':', $args), $value);
    }

    /** 整数チェック */
    public function int($value) { return $this->regex($value, '/^[\-+]?[0-9]+$/'); }

    /** 自然数チェック */
    public function natural($value) { return $this->regex($value, '/^[0-9]+$/'); }

    /** 浮動小数点チェック */
    //  public function decimal($value) { return $this->regex($value, '/^[\-+]?[0-9]+\.[0-9]+$/'); }
    public function decimal($value) { return $this->regex($value, '/^[\-+]?([0-9]+(\.[0-9]*)?|\.[0-9]+)?$/'); }

    /** 半角英字チェック */
    public function alpha($value) { return $this->regex($value, '/^[a-z]+$/i'); }

    /** 半角英数字チェック */
    public function alpha_num($value) { return $this->regex($value, '/^[a-z0-9]+$/i'); }

    /** 半角英数字+アンダースコア+ハイフンチェック */
    public function alpha_dash($value) { return $this->regex($value, '/^([-a-z0-9_-])+$/i'); }

    /** 全角カナチェック */
    public function kana($value) { return $this->regex($value, '/^[ァ-ヶー　０-９]*$/u'); }

    //** URLチェック */
    public function url($value) { return $this->regex($value, '/([\w*%#!()~\'-]+\.)+[\w*%#!()~\'-]+(\/[\w*%#!()~\'-.]+)*/u'); }

    /** メールアドレスチェック */
    public function email($value) { return $this->regex($value, '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'); }

    /** 日付形式チェック yyyy/mm/dd */
    public function date($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*@'); }

    /** 日付時分形式チェック yyyy/mm/dd HH:ii */
    public function datehm($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9])$@x'); }

    /** 日時形式チェック yyyy/mm/dd HH:ii:ss */
    public function datetime($value) { return $this->regex($value, '@^([0-9]{4}(-|/)(0?[1-9]|1[012])(-|/)(0?[1-9]|[12][0-9]|3[01]))\w*(0?[1-9]|1[0-9]|2[0-3]):(0?[1-9]|[1-5][0-9]):(0?[1-9]|[1-5][0-9])$@x'); }

    /** 過去日時チェック(現在を含む) */
    public function past($value, $pattern = 'auto', $now = NULL)
    {
        $pattern == 'auto' AND ($pattern = $this->getTimePattern($value));
        if ($pattern == 'unknown')
        {
            return TRUE;
        }
        return $this->compareTime($value, $pattern, $now) <= 0;
    }

    /** 未来日時チェック(現在を含む) */
    public function future($value, $pattern = 'auto', $now = NULL)
    {
        $pattern == 'auto' AND ($pattern = $this->getTimePattern($value));
        if ($pattern == 'unknown')
        {
            return TRUE;
        }
        return $this->compareTime($value, $pattern, $now) >= 0;
    }

    /** 他のフィールドに一致するか */
    public function match($value, $form_field, /** @param string $delimiter 区切り文字 */$delimiter = ',')
    {
        if ( ! is_array($form_field))
        {
            $form_field = explode($delimiter, $form_field);
        }
        foreach ($form_field as $field_name)
        {
            if ( ($field = $this->field($field_name)) && $field->value() !== $value)
            {
                return FALSE;
            }
        }
        return TRUE;
    }

    /** 配列内の要素に一致するか */
    public function in_array($value, $array, /** @param string $delimiter 区切り文字 */$delimiter = ',')
    {
        if ( ! is_array($array))
        {
            $array = explode($delimiter, $array);
        }
        return str_in_array($value, $array);
    }

}
