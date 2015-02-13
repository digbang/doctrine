<?php namespace Digbang\Doctrine\Auth;

use Digbang\Doctrine\SoftDeleteTrait;
use Digbang\Doctrine\TimestampsTrait;

trait IntIdentityTrait
{
    use PasswordProtectedTrait;
    use RememberTokenTrait;
    use RemindableTrait;
    use TimestampsTrait;
    use SoftDeleteTrait;

    /**
     * @type int
     */
	private $id;

    /**
     * Get the unique identifier for the user.
     *
     * @return int
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }
}
