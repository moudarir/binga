<?php
namespace Moudarir\Binga;

use Exception;

class Common {

    /**
     * @param float|int $amount
     * @return string
     */
    public static function formatAmount ($amount): string {
        return bcadd(round($amount, 2), '0', 2);
    }

    /**
     * @param int $expireDays
     * @param string $format
     * @return string|null
     */
    public static function formatExpirationDate (int $expireDays = 7, string $format = 'Y-m-d\TH:i:se'): ?string {
        try {
            $daysNumber = $expireDays > 0 ? $expireDays : 7;
            $date = new \DateTime('now', new \DateTimeZone(Config::TIMEZONE));
            return $date->add(new \DateInterval('P'.$daysNumber.'D'))->format($format);
        } catch (Exception $e) {
            return null;
        }
    }

}