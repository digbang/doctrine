<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

interface CarbonType
{
    const DATETIME   = 'carbondatetimetype';
    const DATETIMETZ = 'carbondatetimetztype';
    const DATE       = 'carbondatetype';
    const TIME       = 'carbontimetype';

    /**
     * @param                  $value
     * @param AbstractPlatform $platform
     *
     * @return \DateTimeInterface|null
     */
    public function getDateTime($value, AbstractPlatform $platform);
}
