<?php
namespace SeanMorris\RouteTest;
class Formatter
{
	const CONTENT_TYPE = 'application/json';

	public function format($data)
	{
		$formatted = [];

		if($data === FALSE)
		{
			$formatted['header']['error'] = 'Not found';
		}
		else if(is_array($data))
		{
			$formatted['header']['count'] = count($data);

			foreach($data as $index => $model)
			{
				if(!is_object($model))
				{
					$formatted['body'][$index] = $model;
					continue;
				}

				if(!($model instanceof Model))
				{
					if($model instanceof iterable || $model instanceof stdClass)
					{
						// If its an object, make sure the reference is broken
						$_model = [];
						foreach($model as $k => $v)
						{
							$_model[$k] = $v;
						}

						$formatted['body'][$index] = $_model;
						continue;
					}
				}

				$formatted['body'][ $model->id ] = $model->export();
			}
		}
		else if($data instanceof Model)
		{
			$formatted['header']['count'] = 1;
			$formatted['header']['model_id'] = $data->id;

			$formatted['body'] = $data->export();
		}
		else
		{
			$formatted['header']['count'] = 1;

			$formatted['body'] = $data;
		}

		return $formatted;
	}

	public function encode($data)
	{
		return json_encode($data);
	}

	public function contentType()
	{
		return static::CONTENT_TYPE;
	}
}