<?php
namespace SeanMorris\RouteTest;
class Config
{
	protected static $configDir;

	public static function get(...$names)
	{
		if(!static::$configDir)
		{
			$env = (object) getenv();

			static::$configDir = $env->RT_CONFIG_DIR ?? '/app/config';
		}

		if(!$filename = array_shift($names))
		{
			return FALSE;
		}

		$filename = static::$configDir . '/' . $filename . '.yml';

		if(!file_exists($filename))
		{
			return FALSE;
		}

		$config = yaml_parse_file($filename);

		while($name = array_shift($names))
		{
			if(!isset($config[$name]))
			{
				return FALSE;
			}

			$config = $config[$name];
		}

		return $config;
	}
}
