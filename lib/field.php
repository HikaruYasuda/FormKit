<?php

namespace FormKit;

/**
 * 入力フィールドクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.1
 * @version 1.0.0
 * @method static|mixed default($default = null)
 */
class Field {
	/** @var string フィールド名 */
	protected $_name;
	/** @var string 配列インデックスを無視したベース名 */
	protected $_basename;
	/** @var string フィールド種別 */
	protected $_type = 'text';
	/** @var bool 配列かどうか */
	protected $_is_array = false;
	/** @var string ラベル */
	protected $_label = '';
	/** @var mixed フィールドの値 */
	protected $_value;
	/** @var mixed デフォルト値 */
	protected $_default;
	/** @var array SELECT,RADIO,CHECKBOXの選択オプション */
	protected $_options;
	/** @var array バリデーションルール */
	protected $_rules;
	/** @var array 入力フィルター */
	protected $_filters;

	public $attr = array();
	protected static $functional_attributes = array('name', 'type', 'label', 'id', 'value', 'default', 'options', 'rule', 'filter');


	/**
	 * コンストラクタ
	 * @param string $name
	 * @param string $type
	 * @param string $label
	 */
	function __construct($name, $type, $label) {
		$this->_name = $name;
		$this->_basename = ($pos = strpos($name, '[')) ? substr($name, 0, $pos) : $name;

		$type and $this->_type = $type;
		($label !== '' && ! is_null($label)) and $this->_label = $label;
	}

	function __call($name, array $args)
	{
		if (method_exists($this, '_dynamic_call__'.$name))
		{
			call_user_func_array(array($this, '_dynamic_call__'.$name), $args);
		}
	}

	function _toString() {
		return __CLASS__."[name={$this->_name},label={$this->_label},value={$this->_value}]";
	}

	/**
	 * インスタンスを生成します
	 * @param string $name
	 * @param string $type
	 * @param string $label
	 * @return static
	 */
	public static function make($name, $type = '', $label = '') {
		return new static($name, $type, $label);
	}

	/**
	 * HTML要素を取得します
	 * @param string $attribute
	 * @return string
	 */
	public function html($attribute = '') {
		$attribute = is_string($attribute) ? $attribute : '';
		$name = $this->_name;
		$type = $this->_type;
		$value = $this->_value;
		$html = '';

		switch ($type) {
			case 'checkbox':
			case 'radio':
				foreach ($this->_options as $k => $v) {
					$html .= '<label><input type="' . $type . '" name="'.$name.'" value="'.$k.'" '.$attribute.'/> '.$v.'</label>';
				}
				break;
			case 'select':
			case 'file':
				$html = '<input type="file" name="'.$name.'" '.$attribute.'/>';
				break;
			case 'textarea':
				$html = '<textarea name="'.$name.'" '.$attribute.'>'.$value.'</textarea>';
				break;
			default:// text,hidden,password...etc
				$html = '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" '.$attribute.'/>';
				break;
		}
		return $html;
	}

	/**
	 * @return bool
	 */
	public function has_value()
	{
		switch ($this->_type)
		{
			default:
				return ($this->_value !== '' && $this->_value !== NULL);
		}
	}

	protected  function property_get_or_set($name, $val) {
		if (property_exists($this, $name)) {
			if (is_null($val)) {
				return $this->{$name};
			} else {
				$this->{$name} = $val;
				return $this;
			}
		}
		return null;
	}

	/**
	 * フォーム名を取得します
	 * @return static|mixed
	 */
	public function name() { return $this->_name; }

	/**
	 * ベース名を取得します
	 * @return static|mixed
	 */
	public function basename() { return $this->_basename; }

	/**
	 * ラベルを設定または取得します
	 * @param $val
	 * @return static|mixed
	 */
	public function label($val = null) { return $this->property_get_or_set('_label', $val); }

	/**
	 * フォーム値を設定または取得します
	 * @param $val
	 * @return static|mixed
	 */
	public function value($val = null)
	{
		$type = $this->_type;

		if (is_null($val))
		{
			switch ($type)
			{
				default:
					if (is_null($this->_value) && $this->_default !== NULL)
					{
						$value = $this->_default;
					}
					else
					{
						$value = $this->_value;
					}
					return $this->_form_filter->filtering($value, $this->_filters, $this);
					break;
			}
		}
		else
		{
			switch ($this->_type)
			{
				case 'checkbox':
				case 'radio':
				case 'select':
				default:
					$this->_value = $val;
					break;
			}
		}
		return $this;
	}

	/**
	 * フォーム種別を設定または取得します
	 * @param $val
	 * @return static|mixed
	 */
	public function type($val = null) { return $this->property_get_or_set('_type', $val); }

	/**
	 * デフォルト値を設定または取得します
	 * @param $val
	 * @return static|mixed
	 */
	public function default_value($val = null) { return $this->property_get_or_set('_default', $val); }
	public function _dynamic_call__default() { return call_user_func_array(array($this, 'default_value'), func_get_args()); }

	/**
	 * フォームオプションを設定または取得します
	 * @param $val
	 * @return static|mixed
	 */
	public function options($val = null) { return $this->property_get_or_set('_options', $val); }

	/**
	 * バリデーションルールを設定します
	 * @param string|array|null $rule
	 * @return static|array
	 */
	public function rule($rule = null)
	{
		if (is_null($rule))
		{
			return $this->_rules;
		}
		elseif (is_array($rule))
		{
			foreach ($rule as $one)
			{
				$this->rule($one);
			}
		}
		else
		{
			$rules = explode('|', $rule);
			foreach ($rules as $rule)
			{
				$args = explode(':', $rule);
				foreach ($args as $idx => $token)
				{
					$args[$idx] = trim($token);
				}
				$rule_name = array_shift($args);
				$this->_rules[$rule_name] = $args;
			}
		}
		return $this;
	}

	/**
	 * バリデーションルールを削除します
	 * @param string|array|null $rule_name
	 * @return static
	 */
	public function clear_rule($rule_name = NULL)
	{
		if (is_null($rule_name))
		{
			$this->_rules = array();
		}
		elseif (is_array($rule_name))
		{
			foreach ($rule_name as $a_rule_name)
			{
				$this->clear_rule($a_rule_name);
			}
		}
		elseif (array_key_exists($rule_name, $this->_rules))
		{
			unset($this->_rules[$rule_name]);
		}
		return $this;
	}

	/**
	 * フィルターを設定します
	 * @param string $filter_query
	 * @return static
	 */
	public function filter($filter_query = null) {
		if ( ! empty($filter_query)) {
			$filters = explode('|', $filter_query);
			foreach ($filters as $filter) {
				$args = explode(':', $filter);
				$filter_name = array_shift($args);
				$this->_filters[] = array('name' => $filter_name, 'args' => $args);
			}
		}
		return $this;
	}

	public function query()
	{
		switch ($this->_type)
		{
			default:
				return "{$this->_name}=".urlencode($this->value());
		}
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
		if ( ! $this->_validator)
		{
			$this->_validator = new Form_Validator;
			$this->_validator->add($this);
		}
		return $this->_validator;
	}

	/**
	 * エラーメッセージ
	 * @return string
	 */
	public function error_msg() {
		return $this->_validator ? $this->_validator->last_error_message($this->_name) : NULL;
	}

	public function attr($attr, $val = null)
	{
		if (func_num_args() == 0)
		{
			return $this->get_all_attr();
		}
		elseif (func_num_args() == 1)
		{
			return $this->get_attr(strtolower($attr));
		}
		return $this->set_attr(strtolower($attr), $val);
	}

	public function get_all_attr()
	{
		// todo:
	}

	public function get_attr($attr)
	{
		if (in_array(strtolower($attr), static::$functional_attributes))
		{
			return $this->$attr();
		}
		return isset($this->attr[$attr]) ? $this->attr[$attr] : null;
	}

	/**
	 * @param string $attr
	 * @param mixed $val
	 * @return static
	 */
	public function set_attr($attr, $val)
	{
		if (in_array(strtolower($attr), static::$functional_attributes))
		{
			return $this->$attr($val);
		}
		$this->attr[$attr] = $val;
		return $this;
	}

}

