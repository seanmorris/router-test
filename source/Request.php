<?php
namespace SeanMorris\RouteTest;
class Request
{
	protected $method, $path, $body;

	use ReadOnly;

	public function __construct($method = NULL, $path = NULL, $body = NULL)
	{
		$this->method = $method ?: $_SERVER['REQUEST_METHOD'];
		$this->path   = $path   ?: $_SERVER['REQUEST_URI'];

		if($this->path[0] === '/')
		{
			$this->path = substr($this->path, 1);
		}
	}
}
