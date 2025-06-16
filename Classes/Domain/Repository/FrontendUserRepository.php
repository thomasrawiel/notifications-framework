<?php

namespace TRAW\NotificationsFramework\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class FrontendUserRepository extends Repository
{
    public function findUsersByGroups(array $groups): array
    {
        $users = [];

        foreach ($groups as $group) {
            $users = array_merge($users, $this->findUserByGroup($group));
        }

        return $users;
    }

    public function findUserByGroup(int $groupId): array
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false)
        );
        return $query->matching(
            $query->contains('usergroup', $groupId)
        )
            ->execute()
            ->toArray();
    }
}