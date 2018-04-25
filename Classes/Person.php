<?php

class Person
{
	protected $name;
	protected $phone;
	protected $email;
	protected $image_url;
	
	function __construct($name, $phone, $email, $image_url)
	{
		$this->name = $name;
		$this->phone = $phone;
		$this->email = $email;
		$this->image_url = $image_url;
	}
}