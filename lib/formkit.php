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

    /** @var Rule[] */
    public static $rules = array();

    /** @var Filter[] */
    public static $filters = array();

    /** @var Form[] */
    public static $forms = array();

    /** @var string  */
    public static $current_form = '';

    private static $initialized = false;

    public static $lang = 'ja';
    /** @var Field */
    protected static $inCheckField;
    /** @var int */
    protected static $inCheckIndex = -1;

    // =========================
    // life cycle
    // =========================

    public static function init()
    {
        if (!static::$initialized) {
            spl_autoload_register(get_called_class().'::loadClass', true, true);
            require dirname(__FILE__).DIRECTORY_SEPARATOR.'functions.php';
        }
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
     * @param callable $func
     * @param int $option
     * @return Rule
     */
    public static function Rule($name, $func = null, $option = Rule::ArraySeparate)
    {
        if (is_null($func)) {
            return isset(static::$rules[$name]) ? static::$rules[$name] : null;
        }
        return new Rule($name, $func, $option);
    }

    /**
     * ルールを定義します
     *
     * <pre>
     * Usage:
     * FormKit::defRule('naturalNum', function($val) {
     *   return ctype_digit((string)$val);
     * });
     * FormKit::defRule(array(
     *   'tel' => function($val) {
     *     return preg_match('/^[0-9]{9,11}$/');
     *   },
     *   'same' => function($val, $targetName) {
     *     $field = FormKit::inCheckField(); // 適用中のフィールド要素を取得します
     *     $target = $field->form()->field($targetName);
     *     return ($target and $val == $target->value());
     *   },
     * ));
     * FormKit::defRule(array(
     *   FormKit::Rule('requiredIf', function($val, $targetName, $op = '', $expr = '') {
     *   })
     * ));
     * </pre>
     * @param string|array $name
     * @param callable $func
     */
    public static function defRule($name, $func = null)
    {
        $rules = is_array($name) ? $name : array($name => $func);
        foreach ($rules as $name => $func) {
            $rule = ($name instanceof Rule) ? $name : static::Rule($name, $func);
            if (static::$strict) {
                if (isset(static::$rules[$rule->name])) {
                    trigger_error('already exists', E_USER_WARNING);
                }
                if (!is_callable($rule->func)) {
                    trigger_error('func was must be callable', E_USER_WARNING);
                }
            }
            static::$rules[$rule->name] = $rule;
        }
    }

    public static function defRuleMessage($name, $message = null, $lang = '')
    {
        $lang or $lang = self::$lang;
        is_array($name) or ($name = array($name => $message));
        foreach ($name as $_name => $_message) {
            if (isset(static::$rules[$_name])) {
                static::$rules[$_name]->setMessage($_message, $lang);
            }
        }
    }

    /**
     * @return Field
     */
    public static function inCheckField()
    {
        return static::$inCheckField;
    }

    /**
     * @return int
     */
    public static function inCheckIndex()
    {
        return static::$inCheckIndex;
    }

    /**
     * @param Field $field
     * @param int $index
     */
    public static function setInCheckField(Field $field, $index = -1)
    {
        static::$inCheckField = $field;
        static::$inCheckIndex = $index;
    }

    // =========================
    // Definition Filter
    // =========================

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

    // =========================
    // class loader
    // =========================
    public static function loadClass($class)
    {
        if (strncmp($class, 'FormKit\\', 8) == 0) {
            require dirname(__FILE__).DIRECTORY_SEPARATOR.str_replace('FormKit\\', '', $class).'.php'.'';
        } elseif ($class == 'FK') {
            class_alias('FormKit\\FormKit', 'FK');
        } elseif (in_array($class, array('Form', 'Field', 'Filter', 'Rule'))) {
            class_alias("FormKit\\$class", $class);
        }
    }
}
