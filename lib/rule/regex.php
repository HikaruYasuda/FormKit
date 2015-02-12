<?php

class FormKit_Rule_Regex implements FormKit_Rule_Interface
{
	/**
	 * @return string
	 */
	public function get_rule_name()
	{
		return 'regex';
	}

	/**
	 * @param FormKit_Form $form
	 * @param FormKit_Field $field
	 * @return bool|string
	 */
	public function do_test($form, $field /* , optional values */)
	{
		if (func_num_args() < 3)
		{
			throw new InvalidArgumentException;
		}

		$pattern = func_get_arg(2);
		return preg_match($pattern, $field->value());
	}

	/**
	 * @param FormKit_Form $form
	 * @param FormKit_Field $field
	 * @return string
	 */
	public function get_message_format($form, $field)
	{
		switch ($form->config_item('lang', 'ja'))
		{
			case 'en':
				return '';
			case 'ja':
			default:
				return '{label}の値が不正です。';
		}
	}
}
