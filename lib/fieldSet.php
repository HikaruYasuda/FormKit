<?php
/**
 * フィールドセットクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3
 * @version 1.0.0
 */
class FormKit_FieldSet implements Iterator {
	/** @var FormKit_Field[] フィールド要素 */
	protected $fields = array();

	/**
	 * @param self|FormKit_Field[]|FormKit_Field|string[]|string $field
	 */
	public function __construct($field = null)
	{
		$field and $this->add($field);
	}

	/**
	 * @param self $fieldset
	 * @return static
	 */
	public function add_fieldset(self $fieldset)
	{
		foreach ($fieldset as $field)
		{
			$this->add($field);
		}
		return $this;
	}

	public static function _test_add()
	{
		var_dump(function() {
			echo '1';
		});
		$fieldset = new static;
		echo '<p>init fieldset</p>';
		print_r($fieldset);

		echo '<p>add fields</p>';
		$fieldset->add(FormKit::Field('name1'));
		$fieldset->add('name2');
		$fieldset->add(array(
			FormKit::Field('name3'),
			'name4',
			'name5' => 'name5type',
			'name6' => array('6type'),
			'name7' => array('7type', '7label'),
			'name8' => array(
				'type' => '8type',
				'label' => '8label',
				'default' => '8default',
				'id' => '8id',
				'value' => '8value',
				'options' => array('8option1' => '8option1label', '8option2' => '8option2label'),
				'rule' => 'required',
				'filter' => 'in_options',
			),
		));
		print_r($fieldset);

		echo '<p>add other fieldset</p>';
		$newFieldset = new static;
		$newFieldset->add('nameOther');
		$fieldset->add($newFieldset);
		print_r($fieldset);
	}

	/**
	 * @param FormKit_Field $field
	 * @return static
	 */
	public function add_field_object(FormKit_Field $field)
	{
		$this->fields[$field->name()] = $field;
		return $this;
	}

	/**
	 *
	 * usage:
	 * <br>add_field_structure('name1')
	 * <br>add_field_structure('name2', 'name2type')
	 * <br>add_field_structure('name3', array('name3type'))
	 * <br>add_field_structure('name4', array('name4type', 'name4label'))
	 * <br>add_field_structure('name5', array('type' => 'name5type', more attributes...))
	 * @param string $field_name
	 * @param array|string $structure
	 * @return static
	 */
	public function add_field_structure($field_name, $structure = array())
	{
		is_array($structure) or $structure = array($structure);
		$type = isset($structure[0]) ? $structure[0] : '';
		$label = isset($structure[1]) ? $structure[1] : '';

		/** @var FormKit_Field $field */
		$field = FormKit::call_func('Field', $field_name, $type, $label);
		foreach ($structure as $key => $val)
		{
			$field->attr($key, $val);
		}
		return $this->add_field_object($field);
	}

	/**
	 * フィールドをセットします
	 *
	 * usage:
	 * <br>add($fieldset)
	 * <br>add($field)
	 * <br>add(array($field
	 * <br>, 'name1'
	 * <br>, 'name2' => 'name2type'
	 * <br>, 'name3' => array('name3type')
	 * <br>, 'name4' => array('name4type', 'name4label')
	 * <br>, 'name5' => array('type' => 'name5type', more attributes...)
	 * <br>))
	 * <br>add('name5')
	 * @param self|FormKit_Field|array|string $field
	 * @return static
	 * @throws InvalidArgumentException
	 */
	public function add($field)
	{
		if (is_object($field))
		{
			// case FieldSet instance
			if ($field instanceof self)
			{
				return $this->add_fieldset($field);
			}
			// case Field instance
			if ($field instanceof FormKit_Field)
			{
				return $this->add_field_object($field);
			}
		}
		elseif (is_array($field))
		{
			foreach ($field as $key => $val)
			{
				// case Field instance array
				if (is_object($val) and $val instanceof FormKit_Field)
				{
					$this->add_field_object($val);
				}
				// case [field name => field structure] array
				elseif (is_string($key))
				{
					$this->add_field_structure($key, $val);
				}
				// case filed name array
				elseif (is_int($key) and is_string($val))
				{
					$this->add_field_structure($val);
				}
				else
				{
					throw new InvalidArgumentException;
				}
			}
			return $this;
		}
		// case field name
		elseif (is_string($field))
		{
			return $this->add_field_structure($field);
		}

		throw new InvalidArgumentException;
	}

	/**
	 * フィールドリストからフィールドを削除します
	 * @param string[]|string $field_name 削除するフィールドのキー名、またはキー名の配列。指定しなかった場合はすべてのフィールドを削除します。
	 * @return static
	 */
	public function remove($field_name = null)
	{
		if (func_num_args() == 0)
		{
			return $this->remove_all();
		}
		elseif (is_array($field_name))
		{
			foreach ($field_name as $a_field_name)
			{
				$this->remove($a_field_name);
			}
		}
		elseif (is_string($field_name) and $this->exists($field_name))
		{
			unset($this->fields[$field_name]);
		}
		return $this;
	}

	/**
	 * @return static
	 */
	public function remove_all()
	{
		$this->fields = array();
		return $this;
	}

	/**
	 * @param string $query
	 * @return static Fieldset of found fields
	 */
	public function find($query)
	{
		static $operand_list = array('!=', '*=', '^=', '$=', '=');

		if ($query == '*')
		{
			return $this->find_core('name', '*', '');
		}
		foreach ($operand_list as $ope)
		{
			if (($pos = strpos($query, $ope)) !== false)
			{
				$attr = $pos ? substr($query, 0, $pos) : 'name';
				$search = substr($query, $pos + strlen($ope));
				return $this->find_core($attr, $ope, $search);
			}
		}
		return $this->find_core('name', '=', $query);
	}

	/**
	 * <br>usage:
	 * <br>find_index(0); =>
	 * <br>find_index('>2');
	 * @param int|string $index
	 * @param string $ope
	 * @return static Fieldset of found fields
	 */
	public function find_index($index, $ope = '=')
	{
		static $operand_list = array('!=', '>=', '<=', '=', '>', '<');
		if (is_string($index))
		{
			foreach ($operand_list as $prefix)
			{
				if (strpos($index, $prefix) === 0)
				{
					$ope = $prefix;
					$index = substr($index, strlen($prefix));
					break;
				}
			}
		}

		return $this->find_func(function($_, $index, $ope, $query) {
			$query = intval($query);
			switch ($ope)
			{
				case '!=':
					return $index != $query;
				case '=':
					return $index == $query;
				case '>':
					return $index > $query;
				case '<':
					return $index < $query;
				case '>=':
					return $index >= $query;
				case '<=':
					return $index <= $query;
				default:
					return false;
			}
		}, $ope, $index);
	}

	/**
	 * 関数でフィルタリングしたフィールドセットを取得します
	 * @param callable $func
	 * @param mixed $arg
	 * @return static Fieldset of found fields
	 */
	public function find_func($func, $arg = null/* [, $arg[, ..]] */)
	{
		$fieldset = new static;
		if (is_callable($func))
		{
			$index = 0;
			foreach ($this->fields as $field)
			{
				$args = array($field, $index++) + array_slice(func_get_args(), 1);
				if (call_user_func_array($func, $args))
				{
					$fieldset->add($field);
				}
			}
		}
		return $fieldset;
	}

	/**
	 * @param $attr
	 * @param $ope
	 * @param $search
	 * @return static Fieldset of found fields
	 */
	protected function find_core($attr, $ope,  $search)
	{

		return array();
	}

	protected function _filter(array $expr)
	{
		$strategies = array();
		foreach ($expr as $piece_of_expr)
		{
			$piece_of_expr = trim($piece_of_expr);
			$strategies[] = array(self::_get_filtering_method($piece_of_expr), $piece_of_expr);
		}

		$field_set = new FormKit_FieldSet;
		foreach ($this->_field_list as $index => $field)
		{
			if (self::$strategies[0]($index, $field, $strategies[1]))
			{
				$field_set->add($field);
			}
		}
		return $field_set;
	}

	protected static function _get_filtering_strategy($expr)
	{
		$lc_expr = strtolower($expr);
		static $types = array(':text', ':textarea', ':checkbox', ':radio', ':hidden', ':button', ':file', ':select', ':password', ':submit');

		if (in_array($lc_expr, $types))
		{
			return array('_filtering_by_type' => array('expr' => ltrim($expr, ':')));
		}
		if ($lc_expr == ':odd' || $lc_expr == ':even')
		{

		}
		if (preg_match('/:(eq|type|)\(\)/', $expr, $matches))
		{

		}
		return '_filtering_by_wildcard';
	}

	/**
	 * フィールド数を取得します
	 * @return int フィールド数
	 */
	public function count()
	{
		return count($this->fields);
	}

	/**
	 * フィールドリストからフィールドを取得します
	 * @param string[]|string|int $field_name 取得するフィールドのフィールド名、またはフィールド名のリスト。
	 * @return FormKit_FieldSet|FormKit_Field 引数がフィールド名の場合は指定したフィールドを返します。
	 * 引数がフィールド名リストの場合はフィールド名をキーにしたフィールドの配列、
	 * 引数がない、またはNULLの場合は全フィールドの配列を返します。
	 * 指定したフィールド名のフィールドが存在しない場合はNULLを返します。
	 * @throws
	 */
	public function get($field_name = null) {
		if (is_string($field_name))
		{
			return $this->exists($field_name) ? $this->fields[$field_name] : null;
		}
		if (is_int($field_name))
		{
			return $this->get_by_index($field_name);
		}
		if (is_array($field_name))
		{
			$field_list = array();
			foreach ($field_name as $a_field_name)
			{
				if ($this->exists($a_field_name))
				{
					$field_list[] = $this->get($a_field_name);
				}
			}
			return $field_list;
		}
		if (is_null($field_name))
		{
			return $this->to_array();
		}
		throw new InvalidArgumentException();
	}

	/**
	 * @param int $index
	 * @return FormKit_Field
	 */
	public function get_by_index($index)
	{
		is_int($index) or $index = intval($index);
		if ($this->count() > $index)
		{
			foreach ($this->fields as $field)
			{
				if (0 == $index--)
				{
					return $field;
				}
			}
		}
		return null;
	}

	/**
	 * @return FormKit_Field[]
	 */
	public function to_array()
	{
		$field_list = array();
		foreach ($this->fields as $field)
		{
			$field_list[] = $field;
		}
		return $field_list;
	}

	/**
	 * 指定されたフィールド名のフィールドがフィールドリスト内に存在するか判定します
	 * @param string $field_name 判定するフィールド名
	 * @return bool 存在する場合TRUE、しない場合FALSEを返します。
	 */
	public function exists($field_name)
	{
		return is_string($field_name) && array_key_exists($field_name, $this->fields);
	}

	//---------------------------
	// implements for Iterator
	//---------------------------

	public function current()
	{
		return current($this->fields);
	}

	public function next()
	{
		next($this->fields);
	}

	public function key()
	{
		return key($this->fields);
	}

	public function valid()
	{
		$key = $this->key();
		return ($key !== NULL && $key !== FALSE);
	}

	public function rewind()
	{
		reset($this->fields);
	}

	//-------------------------
	// static methods
	//-------------------------

	/**
	 * 変数が空文字かどうか判定します
	 * @param mixed $var
	 * @return bool 引数が空文字の場合TRUE、そうでない場合FALSEを返します
	 */
	protected static function is_blank($var)
	{
		return $var === '';
	}

	/**
	 * 変数が空文字以外の文字列、または数値かどうか判定します
	 * @param mixed $var
	 * @return bool 引数が空文字以外の文字列、または数値の場合TRUE、どちらでもない場合FALSEを返します
	 */
	protected static function is_solid_string($var)
	{
		return (is_string($var) || is_numeric($var)) && strlen((string)$var) > 0;
	}

	/**
	 * @param mixed $var
	 * @return bool
	 */
	protected static function is_valid_index($var)
	{
		return is_int($var) || (is_numeric($var) && ($var === '0' || intval($var)));
	}
}