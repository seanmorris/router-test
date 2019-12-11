<?php
namespace SeanMorris\RouteTest;
class PatientsMetricsController extends Controller
{
	protected const MODEL = Patient::class;

	public function list($match)
	{
		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		$metrics = [];

		foreach($model->metrics as $k => $v)
		{
			$metrics[] = [$k => $v];
		}

		return $metrics;
	}

	public function create($match)
	{
		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		if(!$metrics = static::parseInput())
		{
			return FALSE;
		}

		return $model->update($metrics);
	}

	public function get($match)
	{
		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		if(!$metric = static::parseMetric($match))
		{
			return FALSE;
		}

		if(isset($model->metrics[$metric]))
		{
			return $model->metrics[$metric];
		}

		return FALSE;
	}

	public function update($match)
	{
		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		if(!$metric = static::parseMetric($match))
		{
			return FALSE;
		}

		if(!isset($model->metrics[$metric]))
		{
			return FALSE;
		}

		if(($metricValue = static::parseInput()) === FALSE)
		{
			return FALSE;
		}

		foreach($match->replacements as $class => $property)
		{
			if(!is_a($class, 'SeanMorris\RouteTest\Metric', TRUE))
			{
				continue;
			}

			if($model->update([$property => $metricValue]))
			{
				return $metricValue;
			}
		}

		return FALSE;
	}

	public function delete($match)
	{
		if(!$model = static::loadModel($match))
		{
			return FALSE;
		}

		if(!$metric = static::parseMetric($match))
		{
			return FALSE;
		}

		if(!isset($model->metrics[$metric]))
		{
			return FALSE;
		}

		if($model->update([], [$metric]))
		{
			return $model->metrics[$metric];
		}

		return FALSE;
	}

	protected static function parseMetric($match)
	{
		foreach($match->replacements as $class => $property)
		{
			if(is_a($class, 'SeanMorris\RouteTest\Metric', TRUE))
			{
				return $property;
			}
		}
	}
}
