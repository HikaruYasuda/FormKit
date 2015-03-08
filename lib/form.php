<?php
namespace FormKit;

/**
 * フォームクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3
 * @version 1.0.0
 */
class Form implements \Traversable, \Countable
{
    /** @var FieldSet */
    public $fieldSet;

    // =========================
    // Life cycle
    // =========================

    /** @var array  */
    public $_attributes = array();

    /** @var array エラー */
    public $_errors = array();

    public $_error_message_format = '';

    public $_error_message_placeholder = '';

    /**
     * @param FieldSet $fieldSet
     */
    public function __construct(FieldSet $fieldSet = null)
    {
        $this->fieldSet = $fieldSet;
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
     * </pre>
     * @param Field|Field[]|string|string[]|FieldSet $field
     * @return static
     */
    public function add($field)
    {
        $this->fieldSet->add($field);
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
     * @param string $fieldName 取得するフィールドのフィールド名、またはフィールド名のリスト。
     * @return Field|Field[] 引数がフィールド名の場合、対応するフィールド要素を返します。
     * 引数がフィールド名リストの場合はフィールド名をキーにしたフィールドの配列、
     * 引数がない、またはNULLの場合は全フィールドの配列を返します。
     * 指定したフィールド名のフィールドが存在しない場合はNULLを返します。
     */
    public function field($fieldName = null)
    {
        if (is_null($fieldName)) {
            return $this->fieldSet->toMap();
        }
        is_string($fieldName) and ($fieldName = array_map('trim', explode(',', $fieldName)));
        return $this->fieldSet->get($fieldName);
    }

    /**
     * フィールド要素を削除します
     * @param string|string[] $fieldName
     * @return static
     */
    public function remove($fieldName = null)
    {
        is_string($fieldName) and ($fieldName = array_map('trim', explode(',', $fieldName)));
        $this->fieldSet->remove($fieldName);
        return $this;
    }

    /**
     * 指定されたフィールド名のフィールドがフィールドリスト内に存在するか判定します
     * @param string $fieldName 判定するフィールド名
     * @return bool 存在する場合TRUE、しない場合FALSEを返します。
     */
    public function exists($fieldName)
    {
        return $this->fieldSet->exists($fieldName);
    }

    /**
     * フィールド数を取得します
     * @return int フィールド数
     */
    public function count()
    {
        return $this->fieldSet->count();
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
        $fieldNames = is_null($fieldNames) ? $this->fieldSet->fieldNames() : (array)$fieldNames;
        $values = array();
        foreach ($fieldNames as $fieldName) {
            list($fieldName, $path) = $parse($fieldName);
            $field = $this->field($fieldName);
            if ($field) {
                $values[$fieldName] = fk_pathGet($field->value(), $path);
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
    public function input($fieldName = null, $value = Field::UNSPECIFIED)
    {
        if (is_null($fieldName)) {
            $map = $_REQUEST;
        } elseif (is_string($fieldName)) {
            $map = array($fieldName => $value);
        } elseif (is_array($fieldName) or is_object($fieldName)) {
            $map = (array)$fieldName;
        } else {
            throw new \InvalidArgumentException;
        }
        foreach ($map as $fieldName => $value) {
            $field = $this->field($fieldName);
            $field and $field->value($value);
        }
        return $this;
    }

    /**
     * パラメータを設定します
     * @param object|string[]|string $field_name パラメータのキー、またはキーと値がペアになった連想配列かオブジェクト
     * @param mixed $value 第一引数でフィールド名を指定した場合の値
     * @return static
     */
    public function set_parameters($field_name, $value = Field::UNSPECIFIED)
    {
        return $this->input($field_name, $value);
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
        /** @var Field $field */
        foreach ($this->fieldSet as $field) {
            if (isset($exclude_params[$field->name()]) || ($target_params && ! isset($target_params[$field->name()]))) {
                continue;
            }
            $value = $field->value();
            if ($value !== null and $value !== '') {
                if ($field->type() === 'checkbox' and $field->value() == 0) {
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
        if (func_num_args() == 0) {
            return $this->has_value($this->fieldSet->fieldNames());
        } elseif (is_array($field_name)) {
            foreach ($field_name as $a_field_name) {
                if ($everyone) {
                    if ( ! $this->has_value($a_field_name)) {
                        // ANDの場合一つでも存在しなかったらFALSE
                        return FALSE;
                    }
                } elseif ($this->has_value($a_field_name)) {
                    // ORの場合一つでも存在したらTRUE
                    return TRUE;
                }
            }
            return !!$everyone;
        } elseif (is_string($field_name)) {
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
		if (func_num_args() == 0) {
            return $this->get_valid_values($this->fieldSet->fieldNames());
		} elseif (is_array($field_name)) {
			$array = array();
			foreach ($field_name as $a_field_name) {
				if ( ! is_null($value = $this->get_valid_values($a_field_name))) {
					$array[$a_field_name] = $value;
				}
			}
			return $array;
		} elseif ( ($field = $this->field($field_name)) !== NULL && $field->hasValue()) {
			return $field->value();
		}
		return NULL;
	}

    /**
     * @param string|string[]|null $fieldName
     * @return array|null|string
     */
    public function label($fieldName = null)
    {
        if (is_string($fieldName)) {
            $field = $this->field($fieldName);
            return $field ? $field->label() : null;
        }
        if (is_null($fieldName)) {
            $fieldName = $this->fieldSet->fieldNames();
        }
        $labels = array();
        foreach ($this->field($fieldName) as $field) {
            $labels[$field->name()] = $field->label();
        }
        return $labels;
    }

	/**
	 * フィールドオプションを取得します
	 * @param string|array|null $field_name フィールド名:対応するフィールドの値,フィールド名の配列:対応するフィールドの連想配列,NULL:全フィールドの連想配列
	 * @return mixed
	 */
	public function options($field_name = NULL)
	{
		if (is_null($field_name)) {
            return $this->options($this->fieldSet->fieldNames());
		} elseif (is_array($field_name)) {
			$array = array();
			foreach ($field_name as $a_field_name) {
				if ( ! is_null($options = $this->options($a_field_name))) {
					$array[$a_field_name] = $options;
				}
			}
			return $array;
		}
		if ( ($field = $this->field($field_name)) !== NULL) {
			return $field->options();
		}
		return NULL;
	}

	/**
	 * フィールドオプションの指定された値のラベルを取得します
	 * @param string $field_name フィールド名
	 * @param string $value ラベルを取得する値。指定しない場合現在のフィールド値に対応したラベルを返します
     * @param mixed $default
	 * @return mixed
	 */
	public function option_label($field_name, $value = null, $default = null)
	{
        $field = $this->field($field_name);
        return $field ? $field->optionLabel($value, $default) : null;
	}

    /**
     * フィールドのHTMLを取得します
     * @param string|null $fieldName
     * @param array $attributes
     * @param bool $addId IDを生成するか.nullの場合はformの設定に準拠する
     * @return string
     */
    public function html($fieldName = null, $attributes = array(), $addId = null)
    {
        if (is_null($fieldName)) {
            $fieldName = $this->fieldSet->fieldNames();
        }
        if (is_array($fieldName)) {
            $html = array();
            foreach ($this->field($fieldName) as $key => $field) {
                $html[] = $field->html($attributes, $addId);
            }
            return implode("\n", $html);
        }
        if (!isset($attributes['name'])) {
            $attributes['name'] = $fieldName;
            list($fieldName) = explode('[', $fieldName);
        }
        $field = $this->field($fieldName);
        return is_null($field) ? '' : $field->html($attributes);
    }

    /**
     * バリデーション処理をします
     * @return bool TRUE:成功
     */
    public function validate()
    {
        $this->_errors = array();
        /** @var Field $field */
        foreach ($this->fieldSet as $field) {
            $field->validation();
            if ( ! $field->validity()) {
                $this->_errors[$field->name()] = current($field->error());
            }
        }
        return !count($this->_errors);
    }

    /**
     * コントローラのバリデートメソッドと合わせてバリデーションを行います
     * @param callable $func_name
     * @return bool
     */
    public function validate_with($func_name)
    {
        if ($this->validate()) {
            if (is_callable($func_name)) {
                $func_name($this);
            } else {
                FormKit::$strict and trigger_error('parameter 1 must be a callable', E_USER_WARNING);
            }
        }
        return !count($this->_errors);
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
		elseif ( ($message = $this->errors($field_name)))
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
}