<?php

interface FormKit_Rule_Interface
{
	/**
	 * @return string
	 */
	public function get_rule_name();

	/**
	 * @param FormKit_Form $form
	 * @param FormKit_Field $field
	 * @return bool|string
	 */
	public function do_test($form, $field/* , optional values */);

	/**
	 * @param FormKit_Form $form
	 * @param FormKit_Field $field
	 * @return string
	 */
	public function get_message_format($form, $field);
}
