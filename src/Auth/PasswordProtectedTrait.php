<?php namespace Digbang\Doctrine\Auth;

trait PasswordProtectedTrait
{
    /**
     * @type string
     */
    private $password;

    /**
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }
}
