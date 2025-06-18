<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class DateUtility
 */
final class DateUtility
{
    /**
     * @param \DateTime $past
     *
     * @return string
     */
    public static function getTimeAgo(\DateTime $past): ?string
    {
        $now = new \DateTime();
        $diff = $now->diff($past);

        if ($diff->y > 0) {
            $key = 'datediff.year_' . self::getCase($diff->y);
            $count = $diff->y;
        }elseif ($diff->m > 0) {
            $key = 'datediff.month_' . self::getCase($diff->m);
            $count = $diff->m;
        }elseif ($diff->d > 0) {
            $key = 'datediff.day_' . self::getCase($diff->d);
            $count = $diff->d;
        }elseif ($diff->h > 0) {
            $key = 'datediff.hour_' . self::getCase($diff->h);
            $count = $diff->h;
        }elseif ($diff->i > 0) {
            $key = 'datediff.minute_' . self::getCase($diff->i);
            $count = $diff->i;
        }elseif ($diff->s > 0) {
            $key = 'datediff.minute_' . self::getCase($diff->s);
            $count = $diff->s;
        }else {
            $key = 'datediff.now';
            $count = 0;
        }

        $translated = LocalizationUtility::translate($key, 'notifications_framework', [$count]);

        // Fallback: return ISO string if translation is missing
        return $translated ?? $past->format(DATE_ATOM);

    }

    private static function getCase(int $diff): string
    {
        return $diff === 1 ? 'singular' : 'plural';
    }

}