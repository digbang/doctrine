<?php
// Mapping configuration
// Here you will map your entities, embbedables and
// Autoloading will be used, and the EntityMapping class
// will be instantiated through the IoC container, so you
// can depend on anything the IoC container could resolve.
return [
	// -------------------------------------
	// Mapping of entities.
	// This maps entities to their EntityMapping mapping class
	// -------------------------------------
	'entities' => [
		'Acme\Entities\User' => 'Acme\Mappings\UserMapping'
	],
	'embeddables' => [

	]
];
 