<?php namespace Digbang\Doctrine\Auth;

trait RemindableTrait
{
    /**
     * @type string
     */
    private $email;

    public function getReminderEmail()
    {
        return $this->email;
    }
}
