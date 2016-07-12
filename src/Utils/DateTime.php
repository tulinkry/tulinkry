<?php

namespace Tulinkry;

use Nette;
use Nette\Utils\ObjectMixin;
use Tulinkry\Utils\Date;

class DateTime extends Nette\DateTime
{

	/**
	 * Nette\Object functionality
	 * double inheritance is not possible
	 */


	/**
	 * Returns property value. Do not call directly.
	 * @param  string  property name
	 * @return mixed   property value
	 * @throws MemberAccessException if the property is not defined.
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}


	/**
	 * Sets value of a property. Do not call directly.
	 * @param  string  property name
	 * @param  mixed   property value
	 * @return void
	 * @throws MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}


	/**
	 * Is property defined?
	 * @param  string  property name
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}


	/**
	 * Access to undeclared property.
	 * @param  string  property name
	 * @return void
	 * @throws MemberAccessException
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}


	public function getWeekday ( $localization = "cs", $format = 0 )
	{
		return Date::weekday ( $this -> getTimestamp (), $localization, $format );
	}

};