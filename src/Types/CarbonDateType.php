<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateType;

class CarbonDateType extends DateType implements CarbonType
{
	use CarbonTypeTrait;

    public function getName()
    {
        return CarbonType::DATE;
    }

    public function getDateTime($value, AbstractPlatform $platform)
    {
        return parent::convertToPHPValue($value, $platform);
    }
}
