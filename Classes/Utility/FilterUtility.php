<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
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
        $allowEveryone = $settingsUtility->sendToEveryoneIfNoAudienceIsSelected();

        $validTargetAudiences = Configuration::AUDIENCE;

        return array_values(array_filter($configurations, function (Configuration $configuration) use ($allowEveryone, $validTargetAudiences) {
            if (!$configuration->isPush() || !in_array($configuration->getTargetAudience(), $validTargetAudiences, true)) {
                return false;
            }
            return self::isValidForAudience($configuration, $allowEveryone);
        }));
    }

    private static function isValidForAudience(Configuration $configuration, bool $allowEveryone): bool
    {
        return match ($configuration->getTargetAudience()) {
            '' => $allowEveryone,
            'mixed' => !empty($configuration->getFeGroups()) || !empty($configuration->getFeUsers()),
            'users' => !empty($configuration->getFeUsers()),
            'groups' => !empty($configuration->getFeGroups()),
            default => false,
        };
    }

    public static function filterUnique(string $unfilteredCsv, string $sep = ','): array
    {
        $csvArray = GeneralUtility::trimExplode($sep, $unfilteredCsv, true);

        return array_unique(
            array_filter(
                $csvArray,
                'is_numeric'
            )
        );
    }

    public static function filterUniqueByUid(array $elements): array
    {
        $seen = [];
        $filtered = [];

        foreach ($elements as $element) {
            $uid = $element->getUid();

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
