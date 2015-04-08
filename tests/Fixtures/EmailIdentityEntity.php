<?php namespace Tests\Fixtures;

use Digbang\Doctrine\Auth\EmailIdentityTrait;
use Digbang\Doctrine\SoftDeletable;
use Illuminate\Auth\UserInterface;

class EmailIdentityEntity implements UserInterface, SoftDeletable
{
	use EmailIdentityTrait;

	public function __construct($aVariable, $justToMessUpThings){ }
}
