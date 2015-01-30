<?php
return [
	/**
	 * Namespace of the repository.
	 * The IoC container will be used to instantiate repositories, so an interface could
	 * be used here. If its not the case, point to your concrete repositories
	 */
	'namespace' => 'App\Contracts\Repositories',

	/**
	 * The repository class will be made as {$namespace}\{$className}Repository{$suffix}
	 * For instance, a Place entity, given a Foo\Repositories namespace with Interface as suffix
	 * would look for a Foo\Repositories\PlaceRepositoryInterface class.
	 *
	 * If using concrete classes, leave this empty
	 */
	'suffix' => 'Interface',
];
