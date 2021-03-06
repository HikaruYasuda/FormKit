<?php

namespace FormKit;

require __DIR__.'/../lib/formkit.php';

/**
 * Created by PhpStorm.
 * User: hikaru
 * Date: 2014/07/08
 * Time: 0:56
 */

class FormKit_FieldSetTest extends \PHPUnit_Framework_TestCase {

	public function testNew()
	{
		$fieldset = new FieldSet;
		$this->assertInstanceOf('FormKit\FieldSet', $fieldset);
		$this->assertInstanceOf('Iterator', $fieldset);
		$this->assertEquals(0, $fieldset->count());
		return $fieldset;
	}

	public function testAdd()
	{
		$fieldset = new FieldSet;
		$returnValue = $fieldset->add('byName');
		$this->assertEquals($returnValue, $fieldset);
		$this->assertEquals(1, $fieldset->count());
		$this->assertInstanceOf('FormKit\Field', $fieldset->get(0));
		$this->assertEquals('byName', $fieldset->get(0)->name());
	}

	public function testAdd__byField()
	{
		$fieldset = new FieldSet;
		$returnValue = $fieldset->add(FormKit::Field('byField'));
		$this->assertEquals($returnValue, $fieldset);
		$this->assertEquals(1, $fieldset->count());
		$this->assertInstanceOf('FormKit\Field', $fieldset->get(0));
		$this->assertEquals('byField', $fieldset->get(0)->name());
		return $fieldset;
	}

	public function testAdd_fieldset()
	{
		$fieldset = new FieldSet;
		$fieldset->add('byName');
		$fieldset2 = new FieldSet;
		$fieldset2->add(FormKit::Field('byField'));

		$returnValue = $fieldset->add_fieldset($fieldset2);
		$this->assertEquals($returnValue, $fieldset);
		$this->assertEquals(2, $fieldset->count());
		$this->assertInstanceOf('FormKit\Field', $fieldset->get(0));
		$this->assertEquals('byName', $fieldset->get(0)->name());
		return $fieldset;
	}

	public function testGet()
	{
		$fieldset = new FieldSet;
		$fieldset->add('byName');
		$this->assertTrue(is_array($fieldset->get()));
	}
}
