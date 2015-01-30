<?php
/**
 * Mapping configuration
 *
 * Here you will map your entities and embbedables.
 * Autoloading will be used, and the EntityMapping class
 * will be instantiated through the IoC container, so you
 * can depend on anything the IoC container could resolve.
 */
return [
	'entities' => [
		/**
		 * Array of entity mapping classes.
		 *
		 * Example:
		 *
		 * App\DataSources\Mappings\UserMapping::class,
		 * App\DataSources\Mappings\FooMapping::class,
		 */
	],
	'embeddables' => [
		/**
		 * Array of embeddable (value object) mapping classes.
		 *
		 * Example:
		 *
		 * App\DataSources\Mappings\EmailMapping::class,
		 * App\DataSources\Mappings\DateIntervalMapping::class,
		 */
	]
];
 