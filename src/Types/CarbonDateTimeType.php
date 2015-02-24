<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;

class CarbonDateTimeType extends DateTimeType implements CarbonType
{
    use CarbonTypeTrait;

    public function getName()
    {
        return CarbonType::DATETIME;
    }

    public function getDateTime($value, AbstractPlatform $platform)
    {
        return parent::convertToPHPValue($value, $platform);
    }
}
