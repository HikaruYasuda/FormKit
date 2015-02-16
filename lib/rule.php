<?php
namespace FormKit;

class Rule
{
	// =========================
	// global method
	// =========================

	// =========================
	//
	// =========================

	/** @var string */
	public $name;
	/** @var string|callable */
	public $tester;
	/** @var string|callable */
	public $message;
	/** @var array */
	public $option = array();

	/**
	 * @param string $message
	 * @return static
	 */
	public function message($message = null)
	{
		return $this;
	}
}
