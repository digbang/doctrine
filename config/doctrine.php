<?php
return [
	/**
	 * Proxies are used by doctrine to do lazy loading of relations.
	 */
    'proxies' => [
	    /**
	     * Autogenerate proxy classes.
	     * This should be turned off in production mode for better performance.
	     */
	    'autogenerate' => true,

	    /**
	     * Generated proxies directory. Make sure apache has write permissions!
	     */
        'directory' => storage_path('app/proxies')
    ],
	/**
	 * Migrations may be generated by a Doctrine command to take more control over
	 * deployment of database changes.
	 */
    'migrations' => [
	    /**
	     * Directory where to generate migration files.
	     */
        'directory'  => null,

	    /**
	     * Migration files namespace.
	     */
        'namespace'  => null,

	    /**
	     * Migrations table name. Defaults to "migrations".
	     */
        'table_name' => null
    ],
	/**
	 * Entity cache (second-level cache) may need to generate lock files
	 * in READ-WRITE mode.
	 */
	'lock_files' => [
		/**
		 * Directory where the lock files will be generated.
		 */
		'directory' => storage_path('app/locks')
	]
];
