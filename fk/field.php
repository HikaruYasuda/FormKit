<?php

class FK_Field extends Field
{
    const UNDEFINED = 'Fa;o8 P90u!';

    /** @var string */
    public $name;
    /** @var mixed */
    public $value;
    /** @var bool */
    public $multi = false;

    public function __construct($name, $type = '', $label = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->label = $label;
        $this->value = null;
    }
}

abstract class FK_OptionalField extends FK_Field
{
    /** @var array */
    public $options = array();


}

abstract class FK_MultiSelectableField extends FK_OptionalField
{
    /** @var bool */
    public $multi = true;
}

class FK_SelectField extends FK_OptionalField
{

}

class FK_CheckboxField extends FK_MultiSelectableField
{

}

class FK_RadioField extends FK_Field
{

}

class FK_TextareaField extends FK_Field
{

}