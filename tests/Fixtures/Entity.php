<?php namespace Tests\Fixtures;

use Digbang\Doctrine\SoftDeleteTrait;
use Digbang\Doctrine\TimestampsTrait;

class Entity
{
	use SoftDeleteTrait;
    use TimestampsTrait;
}
