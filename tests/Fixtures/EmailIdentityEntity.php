<?php namespace Tests\Fixtures;

use Digbang\Doctrine\Auth\EmailIdentityTrait;
use Digbang\Doctrine\SoftDeletable;
use Illuminate\Contracts\Auth\Authenticatable;

class EmailIdentityEntity implements Authenticatable, SoftDeletable
{
	use EmailIdentityTrait;

	public function __construct($aVariable, $justToMessUpThings){ }
}
