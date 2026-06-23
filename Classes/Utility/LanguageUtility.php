<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;

class LanguageUtility
{
    public static function translate(string $input): string
    {
        return self::getLanguageService()->sL($input);
    }

    private static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
