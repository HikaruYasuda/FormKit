<?php
namespace FormKit;

/**
 * フォームキットクラス
 * @package FormKit
 * @author hikaru
 * @since PHP 5.3+
 * @version 1.0.0
 */
class FormKit
{
    /** @var bool  */
    public static $strict = false;

    /** @var array */
    public static $rules = array();

    /** @var array */
    public static $filters = array();

    /** @var Form[] */
    public static $forms = array();

    /** @var string  */
    public $current_form = '';

    // =========================
    // life cycle
    // =========================

    public static function init()
    {

    }

    public static function registerAutoLoad()
    {
        spl_autoload_register(get_called_class().'::loadClass', true, true);
    }

    public static function unregisterAutoLoad()
    {
        spl_autoload_unregister(get_called_class().'::loadClass');
    }

    // =========================
    // factory methods
    // =========================

    /**
     * @param string $name
     * @return Form
     */
    public static function Form($name = 'default')
    {
        static::$forms[$name] = new Form();
        return static::$forms[$name];
    }

    /**
     * @param $name
     * @param string $type
     * @param string $label
     * @return Field
     */
    public static function Field($name, $type = '', $label = '')
    {
        return new Field($name, $type, $label);
    }

    // =========================
    // Definition Rule
    // =========================

    /**
     * @param string $name
     * @param string|callable $test
     * @return Rule
     */
    public static function Rule($name, $test/* [, $option[, ..]] */)
    {
        $rule = new Rule();
        $rule->name = $name;
        $rule->tester = $test;
        $rule->option = array_slice(func_get_args(), 2);
        return $rule;
    }

    /**
     * ルールを定義します
     *
     * <pre>
     * Usage:
     * FormKit::defRule('naturalNum', function($val) {
     *   return fk_blankOrNull($val) or ctype_digit((string)$val);
     * });
     * FormKit::defRule(array(
     *   'tel' => function($val) {
     *     return fk_blankOrNull($val) or preg_match('/^[0-9]{9,11}$/');
     *   },
     *   'same' => function($val, $targetName) {
     *     if (fk_blankOrNull($val)) return true;
     *     $field = FormKit::inCheckField(); // 適用中のフィールド要素を取得します
     *     $target = $field->form()->field($targetName);
     *     return ($target and $val == $target->value());
     *   },
     * ));
     * </pre>
     * @param string|array $name
     * @param callable $func
     */
    public static function defRule($name, $func = null)
    {
        $rules = is_array($name) ? $name : array($name => $func);
        foreach ($rules as $name => $func) {
            if (static::$strict) {
                if (isset(static::$rules[$name])) {
                    trigger_error('already exists', E_USER_WARNING);
                }
                if (!is_callable($func)) {
                    trigger_error('func was must be callable', E_USER_WARNING);
                }
            }
            is_callable($func) and (static::$rules[$name] = $func);
        }
    }

    /**
     * フィルタを定義します
     * @param string|array $name
     * @param callable $func
     */
    public static function defFilter($name, $func = null)
    {
        $rules = is_array($name) ? $name : array($name => $func);
        foreach ($rules as $name => $func) {
            if (static::$strict) {
                if (isset(static::$rules[$name])) {
                    trigger_error('already exists', E_USER_WARNING);
                }
                if (!is_callable($func)) {
                    trigger_error('func was must be callable', E_USER_WARNING);
                }
            }
            is_callable($func) and (static::$rules[$name] = $func);
        }
    }

    public static function loadClass($class)
    {
        $paths = explode("\\", ltrim(strtolower($class), "\\"));
        $package = array_shift($paths);
        if ($paths and $package == 'formkit') {
            $path = dirname(__FILE__).DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $paths).'.php';
            require $path.'';
        }
    }
}
