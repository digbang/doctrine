<?php namespace Digbang\Doctrine\Auth;

trait RememberTokenTrait
{
    /**
     * @type string
     */
    private $rememberToken;

    /**
     * @return string
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * @param string $rememberToken
     */
    public function setRememberToken($rememberToken)
    {
        $this->rememberToken = $rememberToken;
    }

    /**
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'rememberToken';
    }
}
