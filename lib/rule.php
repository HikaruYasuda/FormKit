<?php
namespace FormKit;

class Rule
{
    const CheckBlank = 1;// 1 << 0
    const ArraySeparate = 2;// 1 << 1

    // =========================
    //
    // =========================

    /** @var string */
    public $name;
    /** @var callable */
    public $func;
    public $option = self::ArraySeparate;
    /** @var string[] */
    public $messages = array();

    /**
     * @param string $name
     * @param callable $func
     * @param string $message
     * @param string $lang
     */
    public function __construct($name, $func, $message = '', $lang = 'ja')
    {
        $this->name = $name;
        $this->func = $func;
        $this->messages[$lang] = $message;
    }

    /**
     * @param $flag
     * @param bool $enable
     * @return static
     */
    public function setFlag($flag, $enable = true)
    {
        if ($enable) {
            $this->option |= $flag;
        } else {
            $this->option &= (~$flag);
        }
        return $this;
    }

    /**
     * @param bool $enable
     * @return static
     */
    public function checkBlankFlag($enable = true)
    {
        return $this->setFlag(self::CheckBlank, $enable);
    }

    /**
     * @param bool $enable
     * @return static
     */
    public function arraySeparateFlag($enable = true)
    {
        return $this->setFlag(self::ArraySeparate, $enable);
    }

    /**
     * @param callable $func
     * @return static
     */
    public function setFunc($func)
    {
        $this->func = $func;
        return $this;
    }

    /**
     * @param string $message
     * @param string $lang
     * @return static
     */
    public function setMessage($message, $lang = '')
    {
        $lang or $lang = FormKit::$lang;
        $this->messages[$lang] = $message;
        return $this;
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @param string $lang
     * @return string
     */
    public function getMessage($fieldName, $args, $lang = '')
    {
        $lang or $lang = FormKit::$lang;
        if (isset($this->messages[$lang])) {
            $message = $this->messages[$lang];
        } else {
            $message = '';
        }
        $message = str_replace("$0", $fieldName, $message);
        foreach ($args as $index => $arg) {
            $message = str_replace('$'.$index, $arg, $message);
        }
        return $message;
    }
}
