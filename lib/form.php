<?php

/**
 * Class FormKit_Form
 */
class Form implements ArrayAccess
{
	// =========================
	// Life cycle
	// =========================

	public function __construct()
	{
	}

	/** @var FieldSet フィールド要素 */
	public $fieldset;

	/** @var array  */
	public $attributes = array();

	/** @var array エラー */
	public $errors = array();

	// =========================
	// Configuration
	// =========================

	protected $config = array(
		'error_msg_format' => '',
		'error_msg_placeholder' => '',
	);

	/**
	 * like {@see FormKit::set_config}
	 * @param array $config
	 * @return array
	 */
	public function set_config(array $config)
	{
		return ($this->config = FormKit::call_func('array_extend', $this->config, $config));
	}

	/**
	 * like {@see FormKit::set_config_item}
	 * @param string $item
	 * @param mixed $value
	 * @return array
	 */
	public function set_config_item($item, $value)
	{
		return FormKit::call_func('set_config', FormKit::call_func('array_path', $item, $value));
	}

	/**
	 * like {@see FormKit::config_item}
	 * @param string $item
	 * @param mixed $default
	 * @return mixed
	 */
	public function config_item($item, $default = null)
	{
		return FormKit::call_func('array_trace', $this->config, $item, $default);
	}

	// =========================
	// Field access
	// =========================

	/**
	 * @uses FieldSet::add()
	 * @param Field|string|Field[]|string[]|FormKit_FieldSet $field
	 * @return static
	 */
	public function add_field($field)
	{
		if (func_num_args() > 1)
		{
			$fields = array();
			foreach (func_get_args() as $arg)
			{
				is_object($arg) and $arg instanceof FormKit_FieldSet and $arg = (array)$arg;
				is_array($arg) or $arg = array($arg);
				$fields = array_merge($fields, $arg);
			}
			return $this->add_field($fields);
		}
		$this->add($field);
		return $this;
	}

	// =========================
	// Define rules
	// =========================

	/**
	 * @param FormKit_Rule $rule
	 * @return static
	 */
	public function add_rule($rule)
	{
		if ($rule instanceof FormKit_Rule)
		{

			return $this;
		}
		throw new InvalidArgumentException;
	}

	/**
	 * @param string|int|array $item
	 * @param null $value
	 * @return $this
	 */
	public function input($item, $value = null)
	{
		if (is_array($item))
		{
			foreach ($item as $a_item)
			{
				$this->fieldset->filter($a_item)->value($value);
			}
		}
		return $this;
	}

	// =========================
	// Form attributes
	// =========================
	public function set_attr($attr, $value = null)
	{

	}

	public function attr($attr, $default = null)
	{

	}









	/**
	 * フィールドをセットします
	 * @param FormKit_FieldSet|FormKit_Field[]|FormKit_Field|string[]|string $field
	 * @return self
	 * @see FormKit_FieldSet::add()
	 * @throws InvalidArgumentException
	 */
	public function add($field)
	{
		call_user_func_array(array($this->_field_set(), 'add'), func_get_args());
		return $this;
	}

	/**
	 * フィールドリストからフィールドを削除します
	 * @param string[]|string $field_name 削除するフィールドのキー名、またはキー名の配列。指定しなかった場合はすべてのフィールドを削除します。
	 * @return self
	 */
	public function remove($field_name = null)
	{
		call_user_func_array(array($this->_field_set(), 'remove'), func_get_args());
		return $this;
	}

	/**
	 * パラメータを取得または設定します
	 * @param object|string[]|string $field_name パラメータのキー、またはキーと値がペアになった連想配列かオブジェクト
	 * @param mixed $value 第一引数でフィールド名を指定した場合の値
	 * @return self
	 * @throws
	 */
	public function set_parameters($field_name, $value = null)
	{
		if (is_array($field_name) || is_object($field_name))
		{
			foreach ($field_name as $k => $v)
			{
				$this->set_parameters($k, $v);
			}
			return $this;
		}
		elseif (is_string($field_name))
		{
			if ($this->exists($field_name))
			{
				$this->field($field_name)->value($value);
				return $this;
			}
		}
		throw new InvalidArgumentException();
	}

	/**
	 * フィールド名とペアになったパラメータリストを取得します
	 * @param string[]|string $field_name
	 * @return string[]
	 * @throws
	 */
	public function get_parameter_list($field_name) {
		if (is_array($field_name)) {
			$param_list = array();
			foreach ($field_name as $a_field_name)
			{
				$param_list[$a_field_name] =
					$this->exists($a_field_name) ? $this->field($a_field_name)->value() : NULL;
			}
			return $param_list;
		} elseif (is_string($field_name)) {
			return array($field_name =>
				$this->exists($field_name) ? $this->field($field_name)->value() : NULL);
		}
		throw new InvalidArgumentException();
	}


	/**
	 *
	 * @param string[] $exclude_params
	 * @param string[] $target_params
	 * @return array
	 */
	public function get_queries($exclude_params = null, $target_params = null)
	{
		$exclude_params = empty($exclude_params) ? array() : array_flip($exclude_params);
		$target_params = empty($target_params) ? array() : array_flip($target_params);

		$queries = array();
		/** @var Form_Field $field */
		foreach ($this->_field_list as $field) {
			if (isset($exclude_params[$field->name()]) || ($target_params && ! isset($target_params[$field->name()])))
			{
				continue;
			}
			if ( ! self::is_blank_or_null($field->value())) {
				if ($field->type() == 'checkbox' && $field->value() == 0) {
					continue;
				}
				$queries[$field->name()] = $field->value();
			}
		}
		return $queries;
	}

	/**
	 * フォームフィールドの値からクエリ文字列を取得します。
	 * @param string[] $exclude_params
	 * @param string[] $target_params
	 * @return string
	 */
	public function get_query_string($exclude_params = null, $target_params = null)
	{
		$queries = $this->get_queries($exclude_params, $target_params);
		return count($queries) ? '?'.http_build_query($queries) : '';
	}

	/**
	 * 入力値が存在するかを取得します
	 * @param string[]|string $field_name
	 * @param bool $everyone すべてのフィールドに入力がある場合TRUEを返します。配列指定の場合に有効です。
	 * @return bool
	 */
	public function has_value($field_name = null, $everyone = FALSE) {
		if (func_num_args() == 0)
		{
			return $this->has_value(array_keys($this->_field_list));
		}
		elseif (is_array($field_name))
		{
			foreach ($field_name as $a_field_name)
			{
				if ($everyone)
				{
					if ( ! $this->has_value($a_field_name))// ANDの場合一つでも存在しなかったらFALSE
					{
						return FALSE;
					}
				}
				elseif ($this->has_value($a_field_name))// ORの場合一つでも存在したらTRUE
				{
					return TRUE;
				}
			}
			return !!$everyone;
		}
		elseif (is_string($field_name))
		{
			return $this->exists($field_name) ? $this->field($field_name)->has_value() : FALSE;
		}
		return FALSE;
	}

	/**
	 * 空文字、NULL以外のフィールド値を取得します
	 * @param string[]|string $field_name フィールド名:対応するフィールドの値,フィールド名の配列:対応するフィールドの連想配列,NULL:全フィールドの連想配列
	 * @return array|mixed|null
	 */
	public function get_valid_values($field_name = NULL)
	{
		/** @var Form_Field $field */
		if (func_num_args() == 0) // get all
		{
			return $this->get_valid_values(array_keys($this->_field_list));
		}
		elseif (is_array($field_name))
		{
			$array = array();
			foreach ($field_name as $a_field_name)
			{
				if ( ! is_null($value = $this->get_valid_values($a_field_name)))
				{
					$array[$a_field_name] = $value;
				}
			}
			return $array;
		}
		elseif ( ($field = $this->field($field_name)) !== NULL && $field->has_value())
		{
			return $field->value();
		}
		return NULL;
	}

	/**
	 * フィールドの値を取得します
	 * @param string|array|null $field_name フィールド名:対応するフィールドの値,フィールド名の配列:対応するフィールドの連想配列,NULL:全フィールドの連想配列
	 * @param int $index
	 * @return mixed
	 */
	public function value($field_name = NULL, $index = NULL)
	{
		if (func_num_args() == 0)
		{
			return $this->value(array_keys($this->_field_list));
		}
		elseif (is_array($field_name))
		{
			$array = array();
			foreach ($field_name as $a_field_name)
			{
				if ( ! is_null($value = $this->value($a_field_name)))
				{
					$array[$a_field_name] = $value;
				}
			}
			return is_null($index) ? $array : (isset($array[$index]) ? $array[$index] : NULL);
		}
		if ( ($field = $this->field($field_name)) !== NULL)
		{
			$value = $field->value();
			return is_null($index) ? $value : (isset($value[$index]) ? $value[$index] : NULL);
		}
		return NULL;
	}

	public function label($field_name = NULL)
	{
		if (is_null($field_name))
		{
			return $this->label(array_keys($this->_field_list));
		}
		elseif (is_array($field_name))
		{
			$array = array();
			foreach ($field_name as $a_field_name)
			{
				if ( ! is_null($label = $this->label($a_field_name)))
				{
					$array[$a_field_name] = $label;
				}
			}
			return $array;
		}
		if ( ($field = $this->field($field_name)) !== NULL)
		{
			return $field->label();
		}
		return NULL;
	}

	/**
	 * フィールドオプションを取得します
	 * @param string|array|null $field_name フィールド名:対応するフィールドの値,フィールド名の配列:対応するフィールドの連想配列,NULL:全フィールドの連想配列
	 * @return mixed
	 */
	public function options($field_name = NULL)
	{
		if (is_null($field_name))
		{
			return $this->options(array_keys($this->_field_list));
		}
		elseif (is_array($field_name))
		{
			$array = array();
			foreach ($field_name as $a_field_name)
			{
				if ( ! is_null($options = $this->options($a_field_name)))
				{
					$array[$a_field_name] = $options;
				}
			}
			return $array;
		}
		if ( ($field = $this->field($field_name)) !== NULL)
		{
			return $field->options();
		}
		return NULL;
	}

	/**
	 * フィールドオプションの指定された値のラベルを取得します
	 * @param string $field_name フィールド名
	 * @param string $value ラベルを取得する値。指定しない場合現在のフィールド値に対応したラベルを返します
	 * @return mixed
	 */
	public function option_label($field_name, $value = NULL)
	{
		if ( ($field = $this->field($field_name)) !== NULL)
		{
			$options = $field->options();
			if (func_num_args() == 1)
			{
				$value = $field->value();
			}
			return isset($options[$value]) ? $options[$value] : NULL;
		}
		return NULL;
	}

	/**
	 * フィールドのHTMLを取得します
	 * @param $element_name
	 * @return string
	 */
	public function toHTML($element_name) {
		$element = $this->field($element_name);
		return is_null($element) ? '' : $element->html();
	}

	/**
	 * バリデーション処理をします
	 * @return bool TRUE:成功
	 */
	public function validate()
	{
		return $this->validator()->run();
	}

	/**
	 * コントローラのバリデートメソッドと合わせてバリデーションを行います
	 * @param string|array $func_name
	 * @return bool
	 */
	public function validate_with($func_name)
	{
		return $this->validator()->run_with($func_name);
	}

	/**
	 * バリデータを取得します
	 * @return FormKit_Validator
	 */
	public function validator()
	{
		if ( ! $this->_validator)
		{
			$this->_validator = new FormKit_validator;
			$this->_validator->add($this->_field_list);
		}
		return $this->_validator;
	}

	/**
	 * エラーメッセージ
	 * @param string|array|null $field_name
	 * @param bool $formatting
	 * @return array|string
	 */
	public function error_msg($field_name = NULL, $formatting = TRUE)
	{
		if (is_array($field_name))
		{
			$array = array();
			foreach ($field_name as $one)
			{
				if ( ($message = $this->error_msg($one)) !== NULL)
				{
					$array[$one] = $message;
				}
			}
			return empty($array) ? NULL : $array;
		}
		elseif ( ($message = $this->validator()->last_error_message($field_name)))
		{
			if ($formatting && $this->_config_error_msg_format && $this->_config_error_msg_placeholder)
			{
				if (is_array($message))
				{
					foreach ($message as &$a_message)
					{
						$a_message = str_replace($this->_config_error_msg_placeholder, $a_message, $this->_config_error_msg_format);
					}
				}
				else
				{
					$message = str_replace($this->_config_error_msg_placeholder, $message, $this->_config_error_msg_format);
				}
			}
			return $message;
		}
		return NULL;
	}

	/**
	 * @param string $field_name
	 * @param string $message
	 * @return self
	 */
	public function set_error_msg($field_name, $message)
	{
		$this->validator()->set_error_msg($field_name, $message);
		return $this;
	}

	/**
	 * エラーの有無を取得します
	 * @param string|null $field_name
	 * @return bool TRUE:エラーあり
	 */
	public function has_error($field_name = NULL)
	{
		$message = $this->validator()->last_error_message($field_name);
		return ! empty($message);
	}

	/**
	 * エラーメッセージフォーマットを設定、または取得します
	 * @param string $format
	 * @param string $placeholder
	 * @return string|null
	 */
	public function error_msg_format($format = NULL, $placeholder = NULL)
	{
		if (is_null($format))
		{
			return $this->_config_error_msg_format;
		}
		$this->_config_error_msg_format = $format;
		if ( ! empty($placeholder))
		{
			$this->_config_error_msg_placeholder = $placeholder;
		}
		return NULL;
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}