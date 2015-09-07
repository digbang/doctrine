<?php namespace Digbang\Doctrine\Types;

use Carbon\Carbon;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class CarbonTypeTrait
 *
 * @package Digbang\Doctrine\Types
 */
trait CarbonTypeTrait
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $dt = $this->getDateTime($value, $platform);

        if ($dt === null)
        {
            return $dt;
        }

        return Carbon::instance($dt);
    }
    
	public function requiresSQLCommentHint(AbstractPlatform $platform)
	{
		return true;
	}

	public function canRequireSQLConversion()
	{
		return true;
	}

    abstract public function getDateTime($value, AbstractPlatform $platform);
}
