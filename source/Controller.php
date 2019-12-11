<?php
namespace SeanMorris\RouteTest;
abstract class Controller
{
	protected const MODEL = NULL;

	abstract public function list($match);
	abstract public function create($match);
	abstract public function get($match);
	abstract public function update($match);
	abstract public function delete($match);

	protected function parseInput()
	{
		$body = NULL;

		if(!$body = file_get_contents('php://input'))
		{
			return FALSE;
		}

		if(!$input = json_decode($body, JSON_OBJECT_AS_ARRAY))
		{
			return FALSE;
		}

		return $input;
	}

	protected static function loadModel($match)
	{
		foreach($match->replacements as $class => $id)
		{
			if(is_a($class, static::MODEL, TRUE))
			{
				return $class::read($id);
			}
		}

		return FALSE;
	}
}
