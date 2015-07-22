<?php namespace Tests\Fixtures;

use Digbang\Doctrine\Auth\IntIdentityTrait;
use Digbang\Doctrine\SoftDeletable;
use Illuminate\Contracts\Auth\Authenticatable;

class IntIdentityEntity implements Authenticatable, SoftDeletable
{
	use IntIdentityTrait;

	public function __construct($aVariable, $justToMessUpThings){ }
}
