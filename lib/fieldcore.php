<?php

class FieldCore
{
    /** 未指定を表す定数 */
    const UNSPECIFIED = '!hXT(s8P%hq%';

    /** @var string フィールド名 */
    public $_name = '';
    /** @var string タイプ */
    public $_type = '';
    /** @var string ラベル */
    public $_label = '';
    /** @var string 値 */
    public $_value = self::UNSPECIFIED;
    /** @var mixed デフォルト値 */
    public $_default = null;
    /** @var array 選択オプション [key => value] */
    public $_options = array();
    /** @var array[] 入力フィルター */
    public $_filters = array();
    /** @var array[] バリデーションルール */
    public $_rules = array();
    /** @var array 属性 [key => value] */
    public $_attributes = array();
    /** @var array タグ [key => value] */
    public $_tags = array();

    /**
     * 値を設定します
     * @param mixed $value
     * @return static
     */
    public function _setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * 値を取得します
     * @return mixed
     */
    public function _getValue()
    {
        return $this->_value === self::UNSPECIFIED ? $this->_getDefault() : $this->_value;
    }

    /**
     * デフォルト値を設定します
     * @param mixed $value
     * @return static
     */
    public function _setDefault($value)
    {
        $this->_default = $value;
        return $this;
    }

    /**
     * デフォルト値を取得します
     * @return mixed
     */
    public function _getDefault()
    {
        return $this->_default;
    }

    /**
     * フォームオプションを設定します
     * @param array $values
     * @return static
     */
    public function _setOptions($values)
    {
        if (is_array($values)) {
            $this->_options = $values;
        } else {
            $this->_options = array();
        }
        return $this;
    }

    /**
     * フォームオプションを取得します
     * @return array
     */
    public function _getOptions()
    {
        return $this->_options;
    }

    /**
     * オプションラベルを取得します
     * @param string $value
     * @param string $default
     * @return string
     */
    public function _getOptionLabel($value = null, $default = '')
    {
        if (is_null($value)) {
            $value = $this->_getValue();
        }
        return array_key_exists($value, $this->_options) ? $this->_options[$value] : $default;
    }

    /**
     * ルールを追加します
     * @param string|array $rule 追加するルール
     * <br>これらは同じルールを定義します
     * <pre>
     * $form->_addRule('required|maxLength:50');
     * $form->_addRule(array(
     *     'required',
     *     'maxLength' => 50,
     * ));
     * </pre>
     * @return static
     */
    public function _addRule($rule)
    {
        if (is_string($rule)) {
            $rules = self::explode('|', $rule, '\|');
            foreach ($rules as $rule) {
                $args = array_map('trim', self::explode(':', $rule, '\:'));
                $ruleName = array_shift($args);
                $this->_rules[$ruleName] = $args;
            }
        } elseif (is_array($rule)) {
            foreach ($rule as $key => $aRule) {
                if (is_string($key)) {
                    $args = (array)$aRule;
                    $this->_rules[$key] = $args;
                } else {
                    $args = array_map('trim', self::explode(':', $rule, '\:'));
                    $ruleName = array_shift($args);
                    $this->_rules[$ruleName] = $args;
                }
            }
        }
        return $this;
    }

    /**
     * ルールを削除します
     * @param string $rule
     * @return $this
     */
    public function _clearRule($rule = null)
    {
        if ($rule) {
            is_array($rule) or ($rule = array_map('trim', explode('|', $rule)));
            foreach ($rule as $aRule) {
                if (isset($this->_rules[$aRule])) {
                    unset($this->_rules[$aRule]);
                }
            }
        } else {
            $this->_rules = array();
        }
        return $this;
    }

    /**
     * ルールをすべて取得します
     * @return array[]
     */
    public function _getRules()
    {
        return $this->_rules;
    }

    /**
     * フィルタを追加します
     * @param string|array $filter 追加するフィルタ
     * <br>これらは同じフィルタを定義します
     * <pre>
     * $form->_addFilter('trim|replace:is:are');
     * $form->_addFilter(array(
     *     'trim',
     *     'replace' => array('is', 'are'),
     * ));
     * </pre>
     * @return static
     */
    public function _addFilter($filter)
    {
        if (is_string($filter)) {
            $filters = self::explode('|', $filter, '\|');
            foreach ($filters as $filter) {
                $args = array_map('trim', self::explode(':', $filter, '\|'));
                $filterName = array_shift($args);
                $this->_rules[$filterName] = $args;
            }
        } elseif (is_array($filter)) {
            foreach ($filter as $key => $aFilter) {
                if (is_string($key)) {
                    $args = (array)$aFilter;
                    $this->_filters[$key] = $args;
                } else {
                    $args = array_map('trim', self::explode(':', $filter, '\:'));
                    $filterName = array_shift($args);
                    $this->_filters[$filterName] = $args;
                }
            }
        }
        return $this;
    }

    /**
     * ルールを削除します
     * @param string $filter
     * @return $this
     */
    public function _clearFilter($filter = null)
    {
        if ($filter) {
            is_array($filter) or ($filter = array_map('trim', explode('|', $filter)));
            foreach ($filter as $aFilter) {
                if (isset($this->_filters[$aFilter])) {
                    unset($this->_filters[$aFilter]);
                }
            }
        } else {
            $this->_filters = array();
        }
        return $this;
    }

    /**
     * フィルタをすべて取得します
     * @return array[]
     */
    public function _getFilters()
    {
        return $this->_filters;
    }

    /**
     * 属性値を設定します
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function _setAttribute($name, $value)
    {
        switch (strtolower($name)) {
            case 'name':
                $this->_name = $value;
                return $this;
            case 'type':
                $this->_type = $value;
                return $this;
            case 'value':
                return $this->_setValue($value);
        }
        $this->_attributes[$name] = $value;
        return $this;
    }

    /**
     * 属性値を連想配列で上書きします
     * @param array $attributes
     * @return static
     */
    public function _setAttributes(array $attributes)
    {
        $this->_attributes = array();
        foreach ($attributes as $name => $value) {
            $this->_setAttribute($name, $value);
        }
        return $this;
    }

    /**
     * 属性値を削除します
     * @param string $name
     * @return static
     */
    public function _clearAttribute($name = null)
    {
        if ($name) {
            if (array_key_exists($name, $this->_attributes)) {
                unset($this->_attributes[$name]);
            }
        } else {
            $this->_attributes = array();
        }
        return $this;
    }

    /**
     * すべての属性値を連想配列で取得します
     * @return array
     */
    public function _getAttributes()
    {
        return array(
            'name' => $this->_name,
            'type' => $this->_type,
            'value' => $this->_getValue(),
        ) + $this->_attributes;
    }

    /**
     * 属性値を取得します
     * @param string $name 属性名
     * @param mixed $default
     * @return mixed
     */
    public function _getAttribute($name, $default = null)
    {
        switch (strtolower($name)) {
            case 'name':
                return $this->_name;
            case 'type':
                return $this->_type;
            case 'value':
                return $this->_getValue();
        }
        return array_key_exists($name, $this->_attributes) ? $this->_attributes[$name] : $default;
    }

    /**
     * タグを設定します
     * @param string $name タグ名
     * @param mixed $value 値
     * @return static
     */
    public function _setTag($name, $value)
    {
        $this->_tags[$name] = $value;
        return $this;
    }

    /**
     * タグを取得します
     * @param string $name タグ名
     * @param mixed $default
     * @return mixed
     */
    public function _getTag($name, $default = null)
    {
        return array_key_exists($name, $this->_tags) ? $this->_tags[$name] : $default;
    }

    protected static function explode($delimiter, $string, $escape = '', $replace = '#+@+#')
    {
        if (empty($escape)) {
            return explode($delimiter, $string);
        }
        $split = explode($delimiter, str_replace($escape, $replace, $string));
        return array_map(function($s) use ($replace, $escape) {
            return str_replace($replace, $escape, $s);
        }, $split);
    }
}