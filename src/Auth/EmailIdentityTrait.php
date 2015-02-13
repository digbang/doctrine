<?php namespace Digbang\Doctrine\Auth;

use Digbang\Doctrine\SoftDeleteTrait;
use Digbang\Doctrine\TimestampsTrait;

trait EmailIdentityTrait
{
    use PasswordProtectedTrait;
    use RememberTokenTrait;
    use RemindableTrait;
    use TimestampsTrait;
    use SoftDeleteTrait;

    /**
     * Get the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifier()
    {
        return $this->email;
    }
}
