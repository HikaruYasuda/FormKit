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
    /** @var Fieldset */
    protected $_parent;
    /** @var bool バリデーション要否 */
    protected $_willValidation = true;
    /** @var bool  */
    protected $_validity = null;
    /** @var string[] */
    protected $_errors;
    /** @var bool フィルタリング要否 */
    protected $_willFiltering = true;
    /** @var array[] バリデーションルール */
    protected $_rules = array();
    /** @var array[] 入力フィルター */
    protected $_filters = array();

    public function __toString()
    {
        $value = $this->hasValue() ? "value={$this->value()}" : "hasn't value";
        return __CLASS__ . "[name={$this->_name},label={$this->_label},$value]";
    }

    /**
     * フィールド名を設定または取得します
     * @param string $name
     * @return string|static
     */
    public function name($name = self::UNSPECIFIED)
    {
        if ($name === self::UNSPECIFIED) {
            return parent::name();
        }
        if (is_string($name)) {
            if ($this->_parent) {
                $this->_parent->remove($this->_name);
                $this->_name = $name;
                $this->_parent->add($this);
            }
        } else {
            FormKit::$strict and trigger_error('parameter 1 must be a string', E_USER_WARNING);
        }
        return $this;
    }

    // ===================================
    // Access To Field Value
    // ===================================

    /**
     * 値を設定または取得します
     * @param string $value
     * @return mixed|static
     */
    public function value($value = self::UNSPECIFIED)
    {
        if ($value === self::UNSPECIFIED) {
            return $this->applyFilter(parent::value());
        }
        return parent::value($value);
    }

    // ===================================
    // Setting Parent
    // ===================================

    /**
     * 親のフィールドセットを取得します
     * @return FieldSet
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * 親のフィールドセットを付け替えます
     * @param FieldSet $parent
     * @return static
     */
    public function changeParent(FieldSet $parent = null)
    {
        if ($this->_parent !== $parent) {
            $this->_parent and $this->_parent->remove($this->_name);
            $parent and $this->_parent = $parent;
        }
        return $this;
    }

    // ===================================
    // Setting Attributes
    // ===================================

    /**
     * 属性値を追加または取得します
     *
     * <pre>
     * $field->attr('readonly', 'readonly'); // 属性[readonly]の値を設定します
     * $field->attr(array(
     *     'cols' => 20,
     *     'rows' => 10,
     * )); // 属性[cols],[rows]の値を設定します
     * var_export($field->attr('readonly')); // -> 'readonly'
     * var_export($field->attr('cols|rows')); // -> array('col' => 20, 'rows' => 10,);
     * var_export($field->attr()); // -> array('readonly' => 'readonly', 'cols' => 20, 'rows' => 10,);
     * </pre>
     * @param string|array $attr 取得する属性名、または設定する属性名、または設定する属性名をキーにした属性値の連想配列
     * @param string $value
     * @return static|array|mixed
     */
    public function attr($attr = null, $value = self::UNSPECIFIED)
    {
        if (is_null($attr)) {
            return $this->getAttribute();
        } elseif ($value === self::UNSPECIFIED) {
            $names = fk_escapeExplode('|', $attr);
            if (count($names) == 1) {
                return $this->getAttribute($names[0]);
            }
            return $this->getAttribute($names);
        }
        return $this->setAttribute($attr, $value);
    }

    public function makeAttr($attributes)
    {
        if (!is_array($attributes)) {
            return $attributes;
        }
        $html = ' ';
        foreach ((array)$attributes as $key => $val) {
            $html .= htmlspecialchars($key, ENT_QUOTES).'="'.htmlspecialchars($val, ENT_QUOTES).'" ';
        }
        return $html;
    }

    public function parseAttr($attr)
    {
        if (is_array($attr)) {
            return $attr;
        }
        if (preg_match_all('/([a-z-]+)\s*=\s*[\"\']([^\"\']*)[\"\']/i', $attr, $matches)) {
            $attributes = array();
            foreach ($matches as $m) {
                $attributes[$m[1]] = $m[2];
            }
        } else {
            $attributes = array();
        }
        return $attributes;
    }

    // ===================================
    // Render HTML , URL
    // ===================================

    /**
     * HTML要素を取得します
     * @param string $attribute
     * @return string
     */
    public function html($attribute = '')
    {
        $attributes = $this->parseAttr($attribute);
        $name = isset($attribute['name']) ? $attribute['name'] : $this->_name;
        $type = isset($attribute['type']) ? $attribute['type'] : $this->_type;
        $value = isset($attribute['value']) ? $attribute['value'] : $this->value();
        $html = '';
        $attribute = $this->makeAttr($attributes);

        switch ($type) {
            case 'checkbox':
            case 'radio':
                if ($this->_options) {
                    $values = fk_toStringArray($value);
                    foreach ($this->_options as $k => $v) {
                        $val = fk_h($k);
                        $label = fk_h($v);
                        $checked = in_array((string)$k, $values) ? 'checked="checked"' : '';
                        $html .= <<<__HTML__
<label><input type="$type" name="$name" value="$val" $checked $attribute/>$label</label>
__HTML__;
                    }
                } else {
                    $val = fk_h($value);
                    $label = fk_h($this->label());
                    $html .= <<<__HTML__
<label><input type="$type" name="$name" value="$val" $attribute/>$label</label>
__HTML__;
                }
                return $html;
            case 'bool':
                $label = fk_h($this->label());
                $checked = $value ? 'checked="checked"' : '';
                return <<<__HTML__
<label><input type="checkbox" name="$name" value="1" $checked $attribute/>$label</label>
__HTML__;
            case 'select':
                $html = <<<__HTML__
<select name="$name" $attribute>
__HTML__;
                foreach ($this->_options as $k => $v) {
                    $option = '';
                    foreach (is_array($v) ? $v : array($k => $v) as $_k => $_v) {
                        $val = fk_h($_k);
                        $label = fk_h($_v);
                        $selected = $value ? 'selected="selected"' : '';
                        $html .= <<<__HTML__
<option value="$val" $selected>$label</option>
__HTML__;
                    }
                    if (is_array($v)) {
                        $label = fk_h($k);
                        $html .= <<<__HTML__
<optgroup label="$label">
$option
</optgroup>
__HTML__;
                    } else {
                        $html .= $option;
                    }
                }
                return $html . '</select>';
            case 'textarea':
                $val = fk_h($value);
                return <<<__HTML__
<textarea name="$name" $attribute>$val</textarea>
__HTML__;
            case 'button':
            case 'submit':
            case 'reset':
                $label = fk_h($this->label());
                $val = fk_h($value);
                return <<<__HTML__
<button type="$type" name="$name" value="$val" $attribute>$label</button>
__HTML__;
            default:// text,hidden,password...etc
                $val = fk_h($value);
                return <<<__HTML__
<input type="$type" name="$name" value="$val" $attribute>
__HTML__;
        }
    }

    /**
     * クエリ文字列を生成します
     *
     * @param bool $encode キーや値をURLエンコードせずに処理します
     * @param bool $trimEmpty NULL値、空文字、FALSEなど文字列にキャストした際に''と等しくなる値を除外します
     * @param bool $allowArray FALSEが指定され、かつ値が配列だった場合、先頭の1件のみクエリ化します.trimEmptyが適用された後に処理されます
     * @return string
     */
    public function query($encode = true, $trimEmpty = false, $allowArray = true)
    {
        $value = $this->value();
        $values = is_array($value) ? $value : array($value);
        if ($trimEmpty) {
            $values = array_filter($values, function($v) {
                return $v !== null and strval($v) !== '';
            });
        }
        if ( ! $allowArray and count($values) > 1) {
            $values = array_shift($values);
        }
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

    // ===================================
    // Validation
    // ===================================

    /**
     * ルールを追加します
     * @param string|string[]|Rule|Rule[] $rule 追加するルール
     * <br>これらは同じルールを定義します
     * <pre>
     * $field->_addRule('required|maxLength:50');
     * $field->_addRule(array(
     *     'required',
     *     'maxLength' => 50,
     * ));
     * </pre>
     * @return static
     */
    public function rule($rule)
    {
        if (is_string($rule)) {
            $rule = fk_escapeExplode('|', $rule);
        } elseif ($rule instanceof Filter) {
            $rule = array($rule);
        }
        if (is_array($rule)) {
            foreach ($rule as $name => $args) {
                if ($args instanceof Rule) {
                    $this->_rules[$args->name] = $args;
                } else {
                    if (is_int($name)) {
                        $args = array_map('trim', fk_escapeExplode(':', $name));
                        $name = array_shift($args);
                    }
                    if (isset(FormKit::$rules[$name])) {
                        FormKit::$strict and trigger_error("undefined rule [$name]", E_USER_NOTICE);
                    }
                    $this->_rules[$name] = is_null($args) ? array() : (array)$args;
                }
            }
        } elseif (FormKit::$strict) {
            trigger_error('parameter $rule must be a string or an array');
        }
        return $this;
    }

    /**
     * ルールを削除します
     * @param string $rule
     * @return $this
     */
    public function removeRule($rule = null)
    {
        if (is_null($rule)) {
            $rules = is_array($rule) ? $rule : array_map('trim', explode('|', $rule));
            foreach ($rules as $rule) {
                if (array_key_exists($rule, $this->_rules)) {
                    unset($this->_rules[$rule]);
                }
            }
        } else {
            $this->_rules = array();
        }
        return $this;
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
     * バリデーション要否を設定します
     * @param bool $enable
     */
    public function willValidation($enable)
    {
        $this->_willValidation = !!$enable;
    }

    /**
     * バリデーションを実行します
     */
    public function validation()
    {
        $valid = true;
        $errors = array();
        if ($this->_willValidation) {
            foreach ($this->_rules as $name => $args) {
                if ( ! isset(FormKit::$rules[$name])) {
                    FormKit::$strict and trigger_error('undefined rule :'.$name, E_USER_WARNING);
                    continue;
                }
                $error = FormKit::$rules[$name]->run($this, $args);
                if ($error) {
                    $errors[$name] = $error;
                    $valid = false;
                }
            }
        }
        $this->_errors = $errors;
        $this->_validity = $valid;
    }

    /**
     * @return bool|null
     */
    public function validity()
    {
        return $this->_validity;
    }

    /**
     * エラーメッセージを取得します
     *
     * @param string|int $name
     * @return string|string[]
     */
    public function error($name = null)
    {
        if (is_null($name)) {
            return $this->_errors;
        } elseif (is_int($name)) {
            $errors = array_values($this->_errors);
            return isset($errors[$name]) ? $errors[$name] : '';
        }
        return isset($this->_errors[$name]) ? $this->_errors[$name] : '';
    }

    // ===================================
    // Filtering
    // ===================================

    /**
     * フィルタを追加します
     * @param string|string[]|Filter|Filter[] $filter 追加するフィルタ
     * <br>これらは同じフィルタを定義します
     * <pre>
     * $field->_addFilter('trim|replace:is:are');
     * $field->_addFilter(array(
     *     'trim',
     *     'replace' => array('is', 'are'),
     * ));
     * </pre>
     * @return static
     */
    public function filter($filter)
    {
        if (is_string($filter)) {
            $filter = fk_escapeExplode('|', $filter);
        } elseif ($filter instanceof Filter) {
            $filter = array($filter);
        }
        if (is_array($filter)) {
            foreach ($filter as $name => $args) {
                if ($args instanceof Filter) {
                    $this->_filters[$args->name] = $args;
                } else {
                    if (is_int($name)) {
                        $args = array_map('trim', fk_escapeExplode(':', $name));
                        $name = array_shift($args);
                    }
                    if (isset(FormKit::$filters[$name])) {
                        FormKit::$strict and trigger_error("undefined filter [$name]", E_USER_NOTICE);
                    }
                    $this->_filters[$name] = is_null($args) ? array() : (array)$args;
                }
            }
        } elseif (FormKit::$strict) {
            trigger_error('parameter $filter must be a string or an array');
        }
        return $this;
    }

    /**
     * フィルタを削除します
     * @param string $filter
     * @return static
     */
    public function removeFilter($filter = null)
    {
        if (is_null($filter)) {
            $filters = is_array($filter) ? $filter : array_map('trim', explode('|', $filter));
            foreach ($filters as $filter) {
                if (array_key_exists($filter, $this->_filters)) {
                    unset($this->_filters[$filter]);
                }
            }
        } else {
            $this->_filters = array();
        }
        return $this;
    }

    protected function applyFilter($value)
    {
        foreach ($this->_filters as $name => $args) {
            if ( ! isset(FormKit::$filters[$name])) {
                FormKit::$strict and trigger_error('undefined filter :'.$name, E_USER_WARNING);
                continue;
            }
            $value = FormKit::$filters[$name]->apply($this, $value, $args);
        }
        return $value;
    }
}

