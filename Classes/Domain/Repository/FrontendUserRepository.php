<?php

namespace TRAW\NotificationsFramework\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class FrontendUserRepository extends Repository
{
    public function findUsersByUids(array $uids)
    {
        $users = [];
        foreach ($uids as $uid) {
            $users[] = $this->findByUid($uid);
        }

        return $users;
    }


    public function findUsersByGroups(array $groups): array
    {
        $users = [];

        foreach ($groups as $group) {
            foreach ($this->findUserByGroup($group) as $user) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function findAllUsers($pids = []): array
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false)
        );

        if (!empty($pids)) {
            $query->matching(
                $query->in('pid', $pids)
            );
        }

        return $query
            ->execute()
            ->toArray();
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
