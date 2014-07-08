<?php
require __DIR__.'/functions.php';
require __DIR__.'/form.php';
require __DIR__.'/field.php';
require __DIR__.'/fieldset.php';
require __DIR__.'/filter.php';
require __DIR__.'/rule.php';

/**
 * フォームキットクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3+
 * @version 1.0.0
 */
class FormKit
{

	/** @var array  */
	public $config = array();

	/** @var array  */
	public $rules = array();

	/** @var FormKit_Form[] */
	public static $forms = array();
	/** @var string  */
	public $current_form = '';

	/**
	 * @param string $name
	 * @param array $config
	 * @return FormKit_Form
	 */
	public static function Form($name = 'default', $config = null)
	{
		static::$forms[$name] = new FormKit_Form($config);
		return static::$forms[$name];
	}

	// =========================
	// Configuration
	// =========================

	/**
	 * @param array $config
	 * @return array
	 */
	public function setConfig(array $config)
	{
		return ($this->config = static::array_extend($this->config, $config));
	}

	/**
	 * @param string $item
	 * @param mixed $value
	 * @return array
	 */
	public function setConfigItem($item, $value)
	{
		return $this->setConfig(static::array_path($item, $value));
	}

	/**
	 * @param string $item
	 * @param mixed $default
	 * @return mixed
	 */
	public function configItem($item, $default = null)
	{
		return static::array_trace($this->config, $item, $default);
	}

	// =========================
	// Field Rule
	// =========================

	/**
	 * @param string $name
	 * @param string|callable $test
	 * @return FormKit_Rule
	 */
	public static function Rule($name, $test/* [, $option[, ..]] */)
	{
		$rule = new FormKit_Rule();
		$rule->name = $name;
		$rule->tester = $test;
		$rule->option = array_slice(func_get_args(), 2);
		return $rule;
	}

	/**
	 * @param FormKit_Rule $rule
	 */
	public function defRule($rule)
	{
		if ($rule instanceof FormKit_Rule)
		{
			$this->rules[$rule->name] = $rule;
		}
		throw new InvalidArgumentException;
	}

	// =========================
	//
	// =========================

	public static function FieldSet()
	{

	}

	/**
	 * @param $name
	 * @param string $type
	 * @param string $label
	 * @return FormKit_Field
	 */
	public static function Field($name, $type = '', $label = '')
	{
		if ($name instanceof FormKit_Field)
		{
			return $name;
		}
		$field = new FormKit_Field($name, $type, $label);
		return $field;
	}



	// =========================
	// utilities
	// =========================

	final public static function call_func($func)
	{
		return static::call_func_array($func, array_slice(func_get_args(), 1));
	}

	final public static function call_func_array($func, $args)
	{
		static $first_called_class;
		$first_called_class or $first_called_class = get_called_class().'::';
		if ($func == 'call_func' or $func == 'call_func_array')
		{
			throw new Exception('call infinity...');
		}
		return call_user_func_array($first_called_class.$func, $args);
	}

	/**
	 * @param array $array
	 * @param array $arrays
	 * @return array
	 */
	public static function array_extend($array, $arrays/* [, $arrays[, ...]] */)
	{
		$arrays = array_slice(func_get_args(), 1);
		if ( ! is_array($array))
		{
			throw new InvalidArgumentException('All arguments must be arrays.');
		}
		foreach ($arrays as $arr)
		{
			if ( ! is_array($arr))
			{
				throw new InvalidArgumentException('All arguments must be arrays.');
			}

			foreach ($arr as $k => $v)
			{
				// numeric keys are appended
				if (is_int($k))
				{
					array_key_exists($k, $array) ? $array[] = $v : $array[$k] = $v;
				}
				elseif (is_array($v) and array_key_exists($k, $array) and is_array($array[$k]))
				{
					$array[$k] = static::array_extend($array[$k], $v);
				}
				else
				{
					$array[$k] = $v;
				}
			}
		}

		return $array;
	}

	/**
	 * @param string $item_path
	 * @param mixed  $value
	 * @param string $separator
	 * @return array
	 */
	public static function array_path($item_path, $value, $separator = '.')
	{
		$paths = explode($separator, $item_path);
		$array = $value;
		while ($path = array_pop($paths))
		{
			$array = array($path => $array);
		}
		return $array;
	}

	/**
	 * @param array $array      The source array
	 * @param string $item_path Path to the array item
	 * @param mixed $default    The return value if the item isn't found
	 * @param string $separator Separator for path string
	 * @return mixed            Item or default if not found
	 */
	public static function array_trace($array, $item_path, $default = null, $separator = '.')
	{
		$paths = explode($separator, $item_path);
		$trace = $array;
		foreach ($paths as $path)
		{
			if ( ! array_key_exists($path, $trace))
			{
				return $default;
			}
			$trace = $trace[$path];
		}
		return $trace;
	}

}
