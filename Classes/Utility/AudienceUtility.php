<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use Microsoft\Graph\Model\Filter;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalAudienceEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalGroupsCsvEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalUsersCsvEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalUsersEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AudienceUtility
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly FrontendUserRepository  $frontendUserRepository,
        private readonly SettingsUtility         $settingsUtility,
        private readonly EventDispatcher         $eventDispatcher
    )
    {
    }

    public function getAudienceFromConfiguration(Configuration $configuration): array
    {
        $validTargetAudiences = Configuration::AUDIENCE;
        $targetAudience = $configuration->getTargetAudience();
        $users = $groups = '';
        if (empty($targetAudience) || !in_array($targetAudience, $validTargetAudiences, true)) {
            return [];
        }
        if ($configuration->getTargetAudience() === 'users' || $configuration->getTargetAudience() === 'mixed') {
            $users = $configuration->getFeUsers();
        }
        if ($configuration->getTargetAudience() === 'groups' || $configuration->getTargetAudience() === 'mixed') {
            $groups = $configuration->getFeGroups();
        }

        $additionalUsers = $this->eventDispatcher
            ->dispatch(new GetAdditionalUsersCsvEvent($configuration))
            ->getAdditionalUsersCsv();
        $additionalGroups = $this->eventDispatcher
            ->dispatch(new GetAdditionalGroupsCsvEvent($configuration))
            ->getAdditionalGroupsCsv();

        return [
            'users' => FilterUtility::filterUniqueAndJoin(',', $users, $additionalUsers),
            'groups' => FilterUtility::filterUniqueAndJoin(',', $groups, $additionalGroups),
        ];
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

        $users = $this->getUsersFromAudience($audience);
        $additionalUsers = $this->eventDispatcher
            ->dispatch(new GetAdditionalUsersEvent($configuration))
            ->getAdditionalUsers();
        if (!empty($additionalUsers)) {
            $users = FilterUtility::filterUniqueByUid([...$users, ...$additionalUsers]);
        }
        return $users;
    }

    public function getUsersCountFromConfiguration(Configuration $configuration): int
    {
        return count($this->getUsersFromConfiguration($configuration));
    }
}
