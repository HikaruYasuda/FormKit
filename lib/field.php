<?php
namespace FormKit;

/**
 * 入力フィールドクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.1
 * @version 1.0.0
 */
class Field extends FieldCore
{
    /** @var FieldSet */
    public $fieldset;

    /**
     * コンストラクタ
     * @param string $name
     * @param string $type
     * @param string $label
     */
    function __construct($name, $type, $label = '')
    {
        $this->_name = $name;
        $this->_basename = ($pos = strpos($name, '[')) ? substr($name, 0, $pos) : $name;

        $type and ($this->_type = $type);
        ($label !== null) and ($this->_label = $label);
    }

    public function __toString()
    {
        $value = $this->hasValue() ? "value={$this->_getValue()}" : "hasn't value";
        return __CLASS__ . "[name={$this->_name},label={$this->_label},$value]";
    }

    /**
     * インスタンスを生成します
     * @param string $name
     * @param string $type
     * @param string $label
     * @return static
     * @deprecated
     */
    public static function make($name, $type = '', $label = '')
    {
        return new static($name, $type, $label);
    }

    /**
     * 親フォームを取得または設定します
     * @return Form|FieldSet
     */
    public function form()
    {
        return $this->fieldset;
    }

    /**
     * フィールド名を取得します
     * @return string
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * タイプを取得します
     * @return string
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * ラベルを取得します
     * @return string
     */
    public function label()
    {
        return $this->_label;
    }

    /**
     * 値を設定または取得します
     * @param string $value
     * @return mixed|static
     */
    public function value($value = self::UNSPECIFIED)
    {
        return $value === self::UNSPECIFIED ? $this->_getValue() : $this->_setValue($value);
    }

    /**
     * デフォルト値を設定または取得します
     * @param mixed $value
     * @return mixed|static
     */
    public function def($value = self::UNSPECIFIED)
    {
        return $value === self::UNSPECIFIED ? $this->_getDefault() : $this->_setDefault($value);
    }

    /**
     * フォームオプションを設定または取得します
     * @param array|string $value
     * @return mixed|static
     */
    public function options($value = self::UNSPECIFIED)
    {
        return $value == self::UNSPECIFIED ? $this->_getOptions() : $this->_setOptions($value);
    }

    /**
     * フォームオプションのラベルを取得します
     * @param string|null $value 取得するオプション値、nullを指定した場合は現在のフィールド値
     * @param string $default
     * @return string
     */
    public function optionLabel($value = null, $default = '')
    {
        return $this->_getOptionLabel($value, $default);
    }

    /**
     * 値を持っているか判定します
     * @param bool $includeEmpty 空文字列を値としてカウントするか
     * @return bool 空文字列、null、空の配列、未定義のいずれかの場合false、それ以外の場合true
     */
    public function hasValue($includeEmpty = false)
    {
        if ($includeEmpty and $this->_value === '') {
            return true;
        }
        return ($this->_value !== null
            and $this->_value !== ''
            and $this->_value !== array()
            and $this->_value !== self::UNSPECIFIED);
    }

    /**
     * バリデーションルールを追加します
     * @param string|array $rule 追加するルール
     * <br>これらは同じルールを追加します
     * <pre>
     * $field->rule('required|minLength:2|maxLength:50');
     * $field->rule(array(
     *     'required',
     *     'minLength:2',
     *     'maxLength' => 50,
     * ));
     * </pre>
     * @return static
     */
    public function rule($rule)
    {
        return $this->_addRule($rule);
    }

    /**
     * バリデーションルールを削除します
     * @param string $rule
     * @return static
     */
    public function removeRule($rule = null)
    {
        return $this->_clearRule($rule);
    }

    /**
     * バリデーションルールが存在するか判定します
     * @param string $rule
     * @return bool
     */
    public function hasRule($rule = null)
    {
        if (is_null($rule)) {
            return !empty($this->_rules);
        }
        return isset($this->_rules[$rule]);
    }

    /**
     * フィルタを追加します
     * @param string|array $filter 追加するフィルタ
     * <br>これらは同じフィルタを追加します
     * <pre>
     * $field->filter('trim|replace:is:are');
     * $field->filter(array(
     *     'trim',
     *     'replace' => array('is', 'are'),
     * ));
     * </pre>
     * @return static
     */
    public function filter($filter)
    {
        return $this->_addFilter($filter);
    }

    /**
     * フィルタを削除します
     * @param string $filter
     * @return static
     */
    public function removeFilter($filter = null)
    {
        return $this->_clearFilter($filter);
    }

    /**
     * 属性値を追加または取得します
     * <pre>
     * $field->attr('readonly', 'readonly'); // 属性[readonly]の値を設定します
     * $field->attr(array(
     *     'cols' => 20,
     *     'rows' => 10,
     * )); // 属性[cols],[rows]の値を設定します
     * var_export($field->attr('readonly')); // -> 'readonly'
     * var_export($field->attr()); // -> array('readonly' => 'readonly', 'cols' => 20, 'rows' => 10,);
     * </pre>
     * @param string|array $attr 取得する属性名、または設定する属性名、または設定する属性名をキーにした属性値の連想配列
     * @param string $value
     * @return static|array|mixed
     */
    public function attr($attr = null, $value = self::UNSPECIFIED)
    {
        if (is_null($attr)) {
            return $this->_getAttributes();
        } elseif (is_array($attr)) {
            foreach ($attr as $name => $value) {
                $this->_setAttribute($name, $value);
            }
            return $this;
        } elseif ($value === self::UNSPECIFIED) {
            return $this->_getAttribute($attr);
        }
        return $this->_setAttribute($attr, $value);
    }

    /**
     * 属性値を削除します
     * @param string $attr
     * @return static
     */
    public function removeAttr($attr = null)
    {
        return $this->_clearAttribute($attr);
    }

    /**
     * @param $tag
     * @param string $value
     * @return mixed|static
     */
    public function tag($tag, $value = self::UNSPECIFIED)
    {
        if (is_array($tag)) {
            foreach ($tag as $name => $value) {
                $this->_setTag($name, $value);
            }
            return $this;
        } elseif ($value === self::UNSPECIFIED) {
            return $this->_getTag($tag);
        }
        return $this->_setTag($tag, $value);
    }

    /**
     * @param null $tag
     * @return static
     */
    public function removeTag($tag = null)
    {
        return $this->_clearAttribute($tag);
    }

    /**
     * HTML要素を取得します
     * @param string $attribute
     * @return string
     */
    public function html($attribute = '')
    {
        $attribute = is_string($attribute) ? $attribute : '';
        $name = $this->_name;
        $type = $this->_type;
        $value = $this->_getValue();
        $html = '';

        switch ($type) {
            case 'checkbox':
            case 'radio':
                foreach ($this->_options as $k => $v) {
                    $html .= '<label><input type="' . $type . '" name="' . $name . '" value="' . $k . '" ' . $attribute . '/> ' . $v . '</label>';
                }
                break;
            case 'select':
            case 'file':
                $html = '<input type="file" name="' . $name . '" ' . $attribute . '/>';
                break;
            case 'textarea':
                $html = '<textarea name="' . $name . '" ' . $attribute . '>' . $value . '</textarea>';
                break;
            default:// text,hidden,password...etc
                $html = '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" ' . $attribute . '/>';
                break;
        }
        return $html;
    }

    /** nullと空文字を除外します */
    const Query_ExcludeEmpty = 1;
    /** キーや値をエンコードせずに処理します */
    const Query_NoEncode = 2;
    /** リストの場合でも先頭の1件しかクエリ化しません.Query_ExcludeEmptyが指定された場合は除外後に先頭の1件をクエリ化します */
    const Query_NoArray = 4;

    /**
     *
     * @param int $option
     * @return string
     */
    public function query($option = 0)
    {
        $exEmpty = !!($option & self::Query_ExcludeEmpty);
        $encode = !($option & self::Query_NoEncode);
        $singleOnly = !!($option & self::Query_NoArray);

        $value = $this->_getValue();
        $values = is_array($value) ? $value : array($value);
        $exEmpty and ($values = array_filter($values, function($v) {
            return $v !== null and $v !== '';
        }));
        $singleOnly and (count($values) > 1) and ($values = array_shift($values));
        $encode and ($values = array_map('urlencode', $values));

        $name = $this->_name;
        $encode and ($name = urlencode($name));
        (count($values) > 1) and ($name .= '[]');

        $queries = array();
        foreach ($values as $value) {
            $queries[] = "$name=$value";
        }
        return implode('&', $queries);
    }

    /**
     * バリデーション処理します
     * @return bool
     */
    public function validate()
    {
        return $this->validator()->run();
    }

    /**
     * バリデータを取得します
     * @return Form_Validator
     */
    public function validator()
    {
        if (!$this->_validator) {
            $this->_validator = new Form_Validator;
            $this->_validator->add($this);
        }
        return $this->_validator;
    }

    /**
     * エラーメッセージ
     * @return string
     */
    public function error_msg()
    {
        return $this->_validator ? $this->_validator->last_error_message($this->_name) : NULL;
    }
}

