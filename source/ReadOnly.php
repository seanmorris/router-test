<?php
namespace SeanMorris\RouteTest;
trait ReadOnly
{
	public function __get($name)
	{
		return $this->$name;
	}
}
