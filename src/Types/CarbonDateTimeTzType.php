<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeTzType;

class CarbonDateTimeTzType extends DateTimeTzType implements CarbonType
{
    use CarbonTypeTrait;

    public function getName()
    {
        return CarbonType::DATETIMETZ;
    }

    public function getDateTime($value, AbstractPlatform $platform)
    {
        return parent::convertToPHPValue($value, $platform);
    }
}
