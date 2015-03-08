<?php
namespace FormKit;

abstract class FieldCore
{
    /** 未指定を表す定数 */
    const UNSPECIFIED = '!hXT(s8P%hq%';

    /** @var string フィールド名 */
    protected $_name = '';
    /** @var string タイプ */
    protected $_type = '';
    /** @var string ラベル */
    protected $_label = '';
    /** @var string 値 */
    protected $_value = self::UNSPECIFIED;
    /** @var mixed デフォルト値 */
    protected $_default = '';
    /** @var array 選択オプション [key => value] */
    protected $_options = array();
    /** @var array 属性 [key => value] */
    protected $_attributes = array();
    /** @var array タグ [key => value] */
    protected $_tags = array();

    /**
     * コンストラクタ
     * @param string $name
     * @param string $type
     * @param string $label
     */
    function __construct($name, $type = '', $label = '')
    {
        $this->_name = $name;
        isset($type) and ($this->_type = $type);
        isset($label) and ($this->_label = $label);
    }

    // ===================================
    // Basic Properties
    // ===================================

    /**
     * フィールド名を設定または取得します
     * @param string $name
     * @return string|static
     */
    public function name($name = self::UNSPECIFIED)
    {
        if ($name === self::UNSPECIFIED) {
            return $this->_name;
        }
        if (is_string($name)) {
            $this->_name = $name;
        } else {
            FormKit::$strict and trigger_error('parameter 1 must be a string', E_USER_WARNING);
        }
        return $this;
    }

    /**
     * タイプを設定または取得します
     * @param string $type
     * @return string|static
     */
    public function type($type = self::UNSPECIFIED)
    {
        if ($type === self::UNSPECIFIED) {
            return $this->_type;
        }
        if (is_string($type)) {
            $this->_type = $type;
        } else {
            FormKit::$strict and trigger_error('parameter 1 must be a string', E_USER_WARNING);
        }
        return $this;
    }

    /**
     * ラベルを設定または取得します
     * @param string $label
     * @return string|static
     */
    public function label($label = self::UNSPECIFIED)
    {
        if ($label === self::UNSPECIFIED) {
            return $this->_label;
        }
        $this->_label = $label;
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
            return $this->getPlaneValue($this->def());
        }
        $this->_value = $value;
        return $this;
    }

    /**
     * フィルタやデフォルト値を適用する前の値を取得します
     * @param string $default
     * @return string
     */
    public function getPlaneValue($default = null)
    {
        return $this->_value === self::UNSPECIFIED ? $default : $this->_value;
    }

    /**
     * デフォルト値を設定または取得します
     * @param mixed $value
     * @return mixed|static
     */
    public function def($value = self::UNSPECIFIED)
    {
        if ($value === self::UNSPECIFIED) {
            return $this->_default;
        }
        $this->_default = $value;
        return $this;
    }

    /**
     * デフォルト値と等しいか判定します
     * @param bool $strict TRUEの場合型を含めた判定をします
     * @return bool
     */
    public function isDefault($strict = false)
    {
        $value = $this->value();
        return $strict ? ($value === $this->_default) : ($value == $this->_default);
    }

    /**
     * 値を持っているか判定します
     * @param bool $includeEmpty 空文字列を値としてカウントするか
     * @return bool 空文字列、null、空の配列、未定義のいずれかの場合false、それ以外の場合true
     */
    public function hasValue($includeEmpty = false)
    {
        $value = $this->value();
        if ($includeEmpty and $value === '') {
            return true;
        }
        return !in_array($value, array(null, array(), ''), true);
    }

    /**
     * フォームオプションを設定または取得します
     * @param array|string $value
     * @return array|static
     */
    public function options($value = self::UNSPECIFIED)
    {
        if ($value === self::UNSPECIFIED) {
            return $this->_options;
        }
        $this->_options = (array)$value;
        return $this;
    }

    /**
     * 選択中オプションのラベルを取得します
     * @param string $value
     * @param string $default
     * @return string
     */
    public function optionLabel($value = null, $default = '')
    {
        if (is_null($value)) {
            $value = $this->value();
        }
        return array_key_exists($value, $this->_options) ? $this->_options[$value] : $default;
    }

    // ===================================
    // Setting Tags
    // ===================================

    /**
     * タグを設定または取得します
     * @param string $name タグ名
     * @param mixed $value
     * @param mixed $default
     * @return mixed|static
     */
    public function tag($name, $value = self::UNSPECIFIED, $default = null)
    {
        if ($value === self::UNSPECIFIED) {
            return array_key_exists($name, $this->_tags) ? $this->_tags[$name] : $default;
        }
        $this->_tags[$name] = $value;
        return $this;
    }

    /**
     * タグの設定を解除します
     * @param string $name
     * @return static
     */
    public function removeTag($name = null)
    {
        if (is_null($name)) {
            $this->_tags = array();
        } elseif (array_key_exists($name, $this->_tags)) {
            unset($this->_tags[$name]);
        }
        return $this;
    }

    // ===================================
    // Setting Attributes
    // ===================================

    /**
     * 属性値を設定します
     * @param string|array $name
     * @param mixed $value
     * @return static
     */
    public function setAttribute($name, $value)
    {
        if (is_string($name)) {
            $attributes = array($name => $value);
        } else {
            $attributes = $name;
        }
        foreach ($attributes as $name => $value) {
            if (is_string($name)) {
                $lcName = strtolower($name);
                if ($lcName === 'name') {
                    $this->_name = $value;
                } elseif ($lcName === 'type') {
                    $this->_type = $value;
                } elseif ($lcName === 'value') {
                    $this->value($value);
                } elseif ($name !== '') {
                    $this->_attributes[$name] = $value;
                }
            } else {
                FormKit::$strict and trigger_error('invalid argument');
            }
        }
        return $this;
    }

    /**
     * 属性値を取得します
     * @param string|string[] $name 属性名
     * @param mixed $default 未定義に返すデフォルト値
     * @param bool $listCleaning TRUEの場合、配列で返す際の存在しない属性値は返しません
     * @return mixed
     */
    public function getAttribute($name = null, $default = null, $listCleaning = true)
    {
        $attributes = $this->getAllAttributes();
        if (is_null($name)) {
            return $attributes;
        } elseif (is_string($name)) {
            return array_key_exists($name, $attributes) ? $attributes[$name] : $default;
        }
        $array = array();
        foreach ($name as $aName) {
            if (array_key_exists($aName, $attributes)) {
                $array[$aName] = $attributes[$aName];
            } elseif ( ! $listCleaning) {
                $array[$aName] = $default;
            }
        }
        return $array;
    }

    /**
     * すべての属性値を連想配列で取得します
     * @return array
     */
    protected function getAllAttributes()
    {
        return array(
            'name' => $this->name(),
            'type' => $this->type(),
            'value' => $this->value(),
        ) + $this->_attributes;
    }

    /**
     * 属性値を削除します
     * @param string $name
     * @return static
     */
    public function removeAttribute($name = null)
    {
        if (is_null($name)) {
            $this->_attributes = array();
        } elseif (array_key_exists($name, $this->_attributes)) {
            unset($this->_attributes[$name]);
        }
        return $this;
    }
}