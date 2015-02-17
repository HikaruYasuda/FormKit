<?php
namespace FormKit;

class Rule
{
    const CheckBlank = 1;
    const ArraySeparate = 2;

    // =========================
    //
    // =========================

    /** @var string */
    public $name;
    /** @var callable */
    public $func;
    public $option = 0;
    public $messages = array();

    public function __construct($name, $func, $option = self::ArraySeparate)
    {
        $this->name = $name;
        $this->func = $func;
        $this->option = $option;
    }

    /**
     * @param int $option
     * @return static|int
     */
    public function option($option = null)
    {
        if (is_null($option)) {
            return $this->option;
        }
        $this->option = $option;
        return $this;
    }

    /**
     * @param string $message
     * @param string $lang
     * @return static
     */
    public function setMessage($message, $lang = 'ja')
    {
        $this->messages[$lang] = $message;
        return $this;
    }

    public function getMessage($fieldName, $args, $lang = 'ja')
    {
        if (isset($this->messages[$lang])) {
            $message = $this->messages[$lang];
        } elseif ($this->messages) {
            $message = current($this->messages);
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
