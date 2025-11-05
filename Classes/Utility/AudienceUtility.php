<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AudienceUtility
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly FrontendUserRepository  $frontendUserRepository,
        private readonly SettingsUtility         $settingsUtility
    )
    {
    }

    public function getAudienceFromConfiguration(Configuration $configuration): array
    {
        $validTargetAudiences = Configuration::AUDIENCE;
        $targetAudience = $configuration->getTargetAudience();
        $audience = [];
        if (empty($targetAudience) || !in_array($targetAudience, $validTargetAudiences, true)) {
            return $audience;
        }
        if ($configuration->getTargetAudience() === 'users' || $configuration->getTargetAudience() === 'mixed') {
            $audience['users'] = FilterUtility::filterUnique($configuration->getFeUsers());
        }
        if ($configuration->getTargetAudience() === 'groups' || $configuration->getTargetAudience() === 'mixed') {
            $audience['groups'] = FilterUtility::filterUnique($configuration->getFeGroups());
        }
        return $audience;
    }

    public function getUsersFromAudience(array $audience): array
    {
        $users = [];

        if (empty($audience) && $this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected()) {
            $pidList = $this->settingsUtility->getFeUserLookupUids();
            $recursive = $this->settingsUtility->getFeUserLookupRecursive();
            if ($pidList !== [] && $recursive > 0) {
                $treeListUtility = GeneralUtility::makeInstance(TreeListUtility::class);
                $pidList = $treeListUtility->getTreeListArrayFromArray($pidList, $recursive);
            }

            return $this->frontendUserRepository->findAllUsers($pidList);
        }

        if (!empty($audience['users'])) {
            $users = $this->frontendUserRepository->findUsersByUids($audience['users']);
        }

        if (!empty($audience['groups'])) {
            $groupUsers = $this->frontendUserRepository->findUsersByGroups($audience['groups']);
            $users = [...$users, ...$groupUsers];
        }

        return FilterUtility::filterUniqueByUid($users);
    }

    public function getUsersFromConfiguration(Configuration $configuration): array
    {
        $audience = $this->getAudienceFromConfiguration($configuration);

        return $this->getUsersFromAudience($audience);
    }

    public function getUsersCountFromConfiguration(Configuration $configuration): int
    {
        return count($this->getUsersFromConfiguration($configuration));
    }
}
