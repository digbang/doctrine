<?php namespace Tests\Fixtures;

use Digbang\Doctrine\Auth\IntIdentityTrait;
use Digbang\Doctrine\SoftDeletable;
use Illuminate\Auth\UserInterface;

class IntIdentityEntity implements UserInterface, SoftDeletable
{
	use IntIdentityTrait;

	public function __construct($aVariable, $justToMessUpThings){ }
}
