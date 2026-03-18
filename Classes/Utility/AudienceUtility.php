<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalAudienceEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalGroupsCsvEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalUsersCsvEvent;
use TRAW\NotificationsFramework\Events\Audience\GetAdditionalUsersEvent;
use TRAW\NotificationsFramework\Events\Audience\GetGenericUsersEvent;
use TRAW\NotificationsFramework\Events\Audience\GetSubscribedUsersEvent;
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
        $targetAudience = $configuration->getTargetAudience();
        $users = $groups = '';
        if (empty($targetAudience) || !in_array($targetAudience, self::getValidTargetAudiences(), true)) {
            return [];
        }
        if ($targetAudience === 'users' || $targetAudience === 'mixed') {
            $users = $configuration->getFeUsers();
        }
        if ($targetAudience === 'groups' || $targetAudience === 'mixed') {
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
        $additionalUsers = [];

        $target = $configuration->getTargetAudience();

        switch ($target) {
            case 'subscribers':
                //You need to implement your own logic how to determine if a user is subscribed by creating an event listener for GetSubscribedUsersEvent
                $additionalUsers = $this->eventDispatcher
                    ->dispatch(new GetSubscribedUsersEvent($configuration))
                    ->getAdditionalUsers();
                break;
            case 'mixed':
            case 'users':
            case 'groups':
                //in case there's another "subscription" mechanic dispatch the event so you can add more users with your own logic
                $additionalUsers = $this->eventDispatcher
                    ->dispatch(new GetAdditionalUsersEvent($configuration))
                    ->getAdditionalUsers();
                break;
            case '':
                // All users are already selected already if $this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected() is true
                if ($this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected()) {
                    break;
                }
            // else fall through to generic audience event
            case 'admins':
            case 'moderators':
            case 'genericList':
            case 'specialList':
            case 'myList':
            case 'myList2':
            case 'myList3':
                //these are placeholders, add your own logic using the GetGenericUsersEvent
                $additionalUsers = $this->eventDispatcher
                    ->dispatch(new GetGenericUsersEvent($configuration))
                    ->getAdditionalUsers();
                break;
            default:
                throw new \InvalidArgumentException('Invalid audience: ' . $target);
        }

        $users = FilterUtility::filterUniqueByUid([
            ...$users,
            ...$additionalUsers,
        ]);

        return $users;
    }

    public function getUsersCountFromConfiguration(Configuration $configuration): int
    {
        return count($this->getUsersFromConfiguration($configuration));
    }

    public static function getValidTargetAudiences(): array
    {
        return array_merge(Configuration::AUDIENCE, Configuration::PLACEHOLDER_AUDIENCES);
    }
}
