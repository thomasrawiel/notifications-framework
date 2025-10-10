<?php

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterUtility
{
    public static function filterConfigurations(array $elements): array
    {
        return array_values(array_filter($elements, function ($element) {
            $validTargetAudiences = Configuration::AUDIENCE;
            $targetAudience = $element->getTargetAudience();
            if (!$element->isPush()) {
                return false;
            }
            if (empty($targetAudience) || !in_array($targetAudience, $validTargetAudiences, true)) {
                return false;
            }
            if ($targetAudience === 'mixed') {
                return !empty($element->getFeGroups()) && !empty($element->getFeUsers());
            }
            if ($targetAudience === 'users') {
                return !empty($element->getFeUsers());
            }
            if ($targetAudience === 'groups') {
                return !empty($element->getFeGroups());
            }
            return false;
        }));
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
