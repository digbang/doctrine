<?php namespace Digbang\Doctrine\Metadata;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * This is only syntactic sugar over Doctrine's long and complex constants.
 * No need to implement this anywhere!
 *
 * @package Digbang\Doctrine\Metadata
 */
interface Inheritance
{
    /**
     * Set inheritance to NONE, or basically undo setting inheritance.
     */
    const NONE = ClassMetadataInfo::INHERITANCE_TYPE_NONE;

    /**
     * Set inheritance to single table mode.
     * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#single-table-inheritance Doctine documentation
     */
	const SINGLE = ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE;

    /**
     * Set inheritance to class table mode.
     * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html#class-table-inheritance Doctine documentation
     */
    const JOINED = ClassMetadataInfo::INHERITANCE_TYPE_JOINED;
}
