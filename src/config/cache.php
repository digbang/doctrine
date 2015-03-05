<?php
return [
	/**
	 * Master switch. If disabled, no cache will be used.
	 */
	'enabled'   => false,
	/**
	 * Doctrine's hydration cache. Useful for read-only situations.
	 */
	'hydration' => false,
	/**
	 * Doctrine's DQL to SQL query cache.
	 */
	'query'     => true,
	/**
	 * Doctrine's result cache. This caches the raw result set without it
	 * being hydrated into an Entity
	 */
	'result'    => true,
	/**
	 * Doctrine's metadata cache.
	 * It caches mappings to avoid parsing them on each request.
	 */
	'metadata'  => true
];
