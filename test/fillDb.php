<?php
use \SeanMorris\RouteTest\Patient;

require '/app/vendor/autoload.php';

$faker   = \Faker\Factory::create();

for($i = 0; $i < 1000; $i++)
{
	$gender = rand() > 0.5 ? 'male' : 'female';

	Patient::create([
		'title'        => $faker->title($gender)
		, 'first-name' => $faker->firstName($gender)
		, 'last-name'  => $faker->lastName()
		, 'phone'      => $faker->phoneNumber()
		, 'last-visit' =>  $faker->dateTimeThisYear->format('Y-m-d')
	]);
}
