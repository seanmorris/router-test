<?php
namespace SeanMorris\RouteTest;
class Model
{
	protected $id;
	protected $metrics = [];

	protected static $redis, $cache;

	const KEY = 'id';

	use ReadOnly;

	protected function __construct(iterable $data)
	{
		// If its an object, make sure the reference is broken
		foreach($data as $k => $v)
		{
			$this->metrics[ $k ] = $v;
		}
	}

	public static function create(iterable $data)
	{
		$instance = new static($data);

		$redis = static::redis();

		$id = $redis->incr(static::KEY);

		$redis->hmset(
			get_called_class() . '-' . $id
			, $instance->metrics
		);

		$instance->id = $id;

		static::$cache[get_called_class()][$id] = $instance;

		return $instance;
	}

	public static function read($id)
	{
		if(isset(static::$cache[get_called_class()], static::$cache[get_called_class()][$id]))
		{
			return static::$cache[get_called_class()][$id];
		}

		$redis = static::redis();

		$redisKey = get_called_class() . '-' . $id;

		if(!$metrics = $redis->hgetall($redisKey))
		{
			return FALSE;
		}

		$instance = new static($metrics);

		$instance->id = $id;

		static::$cache[get_called_class()][$id] = $instance;

		return $instance;
	}

	public function update(iterable $metrics, iterable $remove = NULL)
	{
		$redis = static::redis();

		foreach($metrics as $k => $v)
		{
			$this->metrics[ $k ] = $v;
		}

		$redisKey = get_called_class() . '-' . $this->id;

		$redis->hmset($redisKey, $this->metrics);

		foreach($remove as $rem)
		{
			$redis->hdel($redisKey, $rem);
		}

		return $this;
	}

	public function delete()
	{
		return static::redis()->del(get_called_class() . '-' . $this->id);
	}

	public function export()
	{
		return $this->metrics;
	}

	public static function maxKey()
	{
		return (int) static::redis()->get(static::KEY);
	}

	protected static function redis()
	{
		if(static::$redis)
		{
			return static::$redis;
		}

		$env = getenv();

		if(!isset($env['RT_REDIS_HOST'], $env['RT_REDIS_PORT']))
		{
			throw new \Exception(
				'Please ensure the following environment variables are set: RT_REDIS_HOST or RT_REDIS_PORT'
			);
		}

		$redis = new \Redis();
		$redis->connect($env['RT_REDIS_HOST'], $env['RT_REDIS_PORT']);

		static::$redis = $redis;

		return $redis;
	}
}
