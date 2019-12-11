<?php
namespace SeanMorris\RouteTest;
class Router
{
	protected static $resources = [];

	public static function resource($name)
	{
		static::$resources[$name] = Config::get('routes', $name);
	}

	public static function route(Request $request, $formatter = NULL)
	{
		$match = $routeDef = FALSE;

		foreach(static::$resources as $resource)
		{
			foreach($resource as $path => $route)
			{
				if($match = static::matchPath($request->path, $path))
				{
					$routeDef = $route;
					break 2;
				}
			}
		}

		if(!$routeDef)
		{
			return FALSE;
		}

		$result = FALSE;

		foreach($routeDef as $methodDef)
		{
			if(!isset($methodDef['method'], $methodDef['controller'], $methodDef['function']))
			{
				continue;
			}

			if($methodDef['method'] !== $request->method)
			{
				continue;
			}

			$controller = new $methodDef['controller'];
			$function   = $methodDef['function'];

			$result = $controller->$function($match);

			break;
		}

		if(!$formatter)
		{
			$formatter = new Formatter;
		}

		if($contentType = $formatter->contentType())
		{
			header('Content-type:' . $contentType);
		}

		return $formatter->encode( $formatter->format($result) );
	}

	protected static function matchPath($supplied, $testPath)
	{
		$suppliedParts = explode('/', $supplied);
		$testParts     = explode('/', $testPath);

		if(count($suppliedParts) !== count($testParts))
		{
			return FALSE;
		}

		$match = (object) ['replacements' => []];

		foreach($testParts as $pos => $testPart)
		{
			if(preg_match('/^\{(.+?)\}$/', $testPart, $groups))
			{
				$match->replacements[ $groups[1] ] = $suppliedParts[$pos];

				continue;
			}

			if($suppliedParts[$pos] !== $testPart)
			{
				return FALSE;
			}
		}

		$match->path = $supplied;

		return $match;
	}
}
