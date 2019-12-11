<?php
namespace SeanMorris\RouteTest;
class PatientsController extends Controller
{
	protected const MODEL = Patient::class;

	public function list($match)
	{
		$key = (static::MODEL)::maxKey();
		$models = [];

		// Listing models is slow, which is why I would never
		// ever use this as a storage engine in production.
		while($key > 0)
		{
			if($model = (static::MODEL)::read($key))
			{
				$models[$key] = $model;
			}

			$key--;
		}

		return $models;
	}

	public function create($match)
	{
		if(!$metrics = static::parseInput())
		{
			return FALSE;
		}

		return (static::MODEL)::create($metrics);
	}

	public function get($match)
	{
		return $this::loadModel($match);
	}

	public function update($match)
	{
		if(!$metrics = static::parseInput())
		{
			return FALSE;
		}

		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		if($model->update($metrics))
		{
			return $model;
		}

		return FALSE;
	}

	public function delete($match)
	{
		if(!$model = $this::loadModel($match))
		{
			return FALSE;
		}

		if($model->delete())
		{
			return $model;
		}

		return FALSE;
	}
}
