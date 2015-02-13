<?php namespace Digbang\Doctrine\Auth;

use Digbang\Doctrine\SoftDeleteTrait;
use Digbang\Doctrine\TimestampsTrait;

trait UsernameIdentityTrait
{
    use PasswordProtectedTrait;
    use RememberTokenTrait;
    use RemindableTrait;
    use TimestampsTrait;
    use SoftDeleteTrait;

    /**
     * @type string
     */
    private $username;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifier()
    {
        return $this->username;
    }
}
