<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterUtility
{
    /**
     * Filters elements according to push and target audience rules.
     *
     * Remove configurations when:
     *  - the element has push=0
     *  - the target audience is invalid (not in Configuration::AUDIENCE)
     *  - the target audience is empty when allowEveryone is false
     *  - or if the target audience does not match the content of feUsers/feGroups:
     *      * 'users' requires at least one feUser
     *      * 'groups' requires at least one feGroup
     *      * 'mixed' requires at least one feUser or one feGroup
     *
     *
     * @param array<Configuration> $configurations
     *
     * @return array<Configuration>
     */
    public static function filterConfigurations(array $configurations): array
    {
        $settingsUtility = GeneralUtility::makeInstance(SettingsUtility::class);
        $validation = GeneralUtility::makeInstance(ConfigurationValidation::class);
        return array_values(array_filter($configurations, function (Configuration $configuration) use ($settingsUtility, $validation) {
            if (!$configuration->isPush()) {
                return false;
            }

            if($settingsUtility->storeNotificationsOnRecordPid() === false
                    && $settingsUtility->isPidValid($configuration->getPid()) === false) {
                return false;
            }

            if(!$validation->validateConfiguration($configuration)) {
                return false;
            }

            return self::isValidForAudience($configuration, $settingsUtility->sendToEveryoneIfNoAudienceIsSelected());
        }));
    }

    private static function isValidForAudience(Configuration $configuration, bool $allowEveryone): bool
    {
        $target = $configuration->getTargetAudience();

        return match ($target) {
            '' => $allowEveryone,
            'mixed' => !empty($configuration->getFeGroups()) || !empty($configuration->getFeUsers()),
            'users' => !empty($configuration->getFeUsers()),
            'groups' => !empty($configuration->getFeGroups()),
            //Treat placeholders as always valid, because I expect the event handlers to do the work
            default => in_array($target, Configuration::PLACEHOLDER_AUDIENCES, true),
        };
    }

    /**
     * Takes one or more CSV strings, splits them, trims them, removes empty values,
     * keeps only numeric entries, and returns a unique array.
     *
     * @param string ...$csvStrings One or more CSV strings
     * @param string $sep           Separator (default ",")
     *
     * @return array<int, string>
     */
    public static function filterUniqueAndJoin($sep = ',', string ...$csvStrings): array
    {
        $allValues = [];

        foreach ($csvStrings as $csv) {
            $parts = GeneralUtility::trimExplode($sep, $csv, true);
            $allValues = array_merge($allValues, $parts);
        }

        return array_unique(
            array_filter(
                $allValues,
                'is_numeric'
            )
        );
    }

    public static function filterUniqueByUid(array $elements): array
    {
        $seen = [];
        $filtered = [];

        foreach ($elements as $element) {
            if (empty($element)) {
                continue;
            }
            if (method_exists($element, 'getUid')) {
                $uid = $element->getUid();
            } elseif (method_exists($element, '_getProperty')) {
                $uid = $element->_getProperty('uid');
            } elseif (property_exists($element, 'uid')) {
                $uid = $element->uid;
            } elseif (is_array($element)) {
                $uid = $element['uid'];
            } else {
                throw new \Exception('Could not determine uid');
            }

            if (!isset($seen[$uid])) {
                $seen[$uid] = true;
                $filtered[] = $element;
            }
        }

        usort($filtered, function ($a, $b) {
            return $a->getUid() <=> $b->getUid();
        });

        return $filtered;
    }
}
