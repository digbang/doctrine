<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TimeType;

class CarbonTimeType extends TimeType
{
	use CarbonTypeTrait;

    public function getName()
    {
        return CarbonType::TIME;
    }

    public function getDateTime($value, AbstractPlatform $platform)
    {
        return parent::convertToPHPValue($value, $platform);
    }
}
