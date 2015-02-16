<?php
namespace FormKit;

/**
 * フォームクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3
 * @version 1.0.0
 */
class Form extends FieldSet implements \ArrayAccess
{
    // =========================
    // Life cycle
    // =========================

    /** @var array  */
    public $_attributes = array();

    /** @var array エラー */
    public $_errors = array();

    public $_error_message_format = '';

    public $_error_message_placeholder = '';

    public function __construct()
    {
    }

    // =========================
    // field set control
    // =========================

    /**
     * フィールド要素を追加します
     *
     * <pre>
     * Usage:
     * $form->add(FK::Field('first_name', 'text', '名')->rule('maxLength:50'));
     * $form->add('email_address'); // = $form->add(FK::Field('email_address'))
     * </pre>
     * @param Field|Field[]|string|string[]|FieldSet $field
     * @return static
     */
    public function add($field)
    {
        parent::add($field);
        return $this;
    }

    /**
     * フィールドインスタンスを追加します
     * @param Field $field
     * @return static
     */
    public function addFieldObject(Field $field)
    {
        parent::addFieldObject($field);
        foreach ($this->fields as $field) {
            $field->form($this);
        }
        return $this;
    }

    /**
     * フィールド要素を取得します
     *
     * <pre>
     * Usage:
     * $form->add('first_name')->add('last_name');
     * var_dump($form->field('first_name'));
     * // フィールド名を渡すとフィールド要素を返します
     * // object(\FormKit\Field)#1 (1) {
     * //   ["_name:public"]=> string(3) "first_name"
     * // }
     * var_dump($form->field('middle_name'));
     * // 存在しないフィールド名だとNULLを返します
     * // NULL
     * var_dump($form->field(array('first_name', 'last_name')));
     * // フィールド名リストを渡すとフィールド要素リストを返します
     * // array(2) {
     * //   [0]=>
     * //   object(\FormKit\Field)#1 (1) {
     * //     ["_name:public"]=> string(3) "first_name"
     * //   }
     * //   [1]=>
     * //   object(\FormKit\Field)#2 (1) {
     * //     ["_name:public"]=> string(3) "last_name"
     * //   }
     * // }
     * var_dump($form->field(array('email', 'tel')));
     * // フィールド名リストに存在しないフィールド名があった場合でも配列形式で返します
     * // array(0) { }
     * </pre>
     * @param string[]|string|int $fieldName 取得するフィールドのフィールド名、またはフィールド名のリスト。
     * @return Field|Field[] 引数がフィールド名の場合、対応するフィールド要素を返します。
     * 引数がフィールド名リストの場合はフィールド名をキーにしたフィールドの配列、
     * 引数がない、またはNULLの場合は全フィールドの配列を返します。
     * 指定したフィールド名のフィールドが存在しない場合はNULLを返します。
     */
    public function field($fieldName = null)
    {
        return parent::get($fieldName);
    }

    /**
     * フィールド要素を削除します
     * @param string|string[] $fieldName
     * @return static
     */
    public function remove($fieldName = null)
    {
        return parent::remove($fieldName);
    }

    // =========================
    // field access
    // =========================

    /**
     * @param $fieldNames
     * <pre>
     * $form->
     * $form->input('hobbies', array('music', 'TV game', array(20, 5, 13)));
     * var_export($form->value('hobbies/1')); // -> 'TV game'
     * var_export($form->value('hobbies/5')); // -> NULL
     * var_export($form->value('hobbies/2/0')); // -> 20
     * var_export($form->value(array('hobbies/1'))); // ->
     * </pre>
     * @return array
     */
    public function value($fieldNames)
    {
        $parse = function($str) {
            $paths = explode('/', $str, 2);
            $root = array_shift($paths);
            $path = array_shift($paths);
            return array($root, is_string($path) ? $path : '');
        };
        $fieldNames = is_null($fieldNames) ? parent::names() : (array)$fieldNames;
        $values = array();
        foreach ($fieldNames as $fieldName) {
            list($fieldName, $path) = $parse($fieldName);
            $field = parent::get($fieldName);
            if ($field) {
                $values[$fieldName] = fk_pathGet($field->_getValue(), $path);
            }
        }
        return $values;
    }

    /**
     * パラメータを設定します
     * @param string|array|object $fieldName フィールド名、またはフィールド名をキーにした値の連想配列かオブジェクト
     * @param mixed $value 第一引数でフィールド名を指定した場合の値
     * @return static
     */
    public function input($fieldName, $value = Field::UNSPECIFIED)
    {
        if (is_string($fieldName)) {
            $map = array($fieldName => $value);
        } elseif (is_array($fieldName) or is_object($fieldName)) {
            $map = (array)$fieldName;
        } else {
            throw new \InvalidArgumentException;
        }
        foreach ($map as $fieldName => $value) {
            $field = parent::get($fieldName);
            $field and $field->_setValue($value);
        }
        return $this;
    }

    // =========================
    // Definition rules, filters
    // =========================

    /**
     * ルールを定義します
     * @param string $name
     * @param callable $func
     * @return static
     */
    public function defRule($name, $func)
    {
        FormKit::defRule($name, $func);
        return $this;
    }

    /**
     * フィルタを定義します
     * @param string $name
     * @param callable $func
     * @return static
     */
    public function defFilter($name, $func)
    {
        FormKit::defFilter($name, $func);
        return $this;
    }

    // =========================
    // Form attributes
    // =========================

    public function setAttr($attr, $value = null)
    {

    }

    public function attr($attr, $default = null)
    {

    }

    /**
     * パラメータを設定します
     * @param object|string[]|string $field_name パラメータのキー、またはキーと値がペアになった連想配列かオブジェクト
     * @param mixed $value 第一引数でフィールド名を指定した場合の値
     * @return self
     * @throws
     */
    public function set_parameters($field_name, $value = Field::UNSPECIFIED)
    {
        return $this->input($field_name, $value);
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
			foreach ($field_name as $a_field_name) {
				$param_list[$a_field_name] =
					$this->exists($a_field_name) ? $this->field($a_field_name)->value() : NULL;
			}
			return $param_list;
		} elseif (is_string($field_name)) {
			return array($field_name =>
				$this->exists($field_name) ? $this->field($field_name)->value() : NULL);
		}
		throw new \InvalidArgumentException();
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
		foreach ($this->$fields as $field) {
			if (isset($exclude_params[$field->name()]) || ($target_params && ! isset($target_params[$field->name()]))) {
				continue;
			}
            $value = $field->value();
			if ($value !== null and $value !== '') {
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
			return $this->has_value(parent::names());
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
			return $this->exists($field_name) ? $this->field($field_name)->hasValue() : FALSE;
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
		/** @var Field $field */
		if (func_num_args() == 0) // get all
		{
			return $this->get_valid_values(parent::names());
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
		elseif ( ($field = $this->field($field_name)) !== NULL && $field->hasValue())
		{
			return $field->value();
		}
		return NULL;
	}

	public function label($field_name = NULL)
	{
		if (is_null($field_name))
		{
			return $this->label(parent::names());
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
	 * @return Validator
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