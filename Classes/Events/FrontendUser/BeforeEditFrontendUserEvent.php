<?php

namespace TRAW\NotificationsFramework\Events\FrontendUser;

use LINGNER\LinImpleniaUserprofile\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class BeforeEditFrontendUserEvent extends AbstractFrontendUserEvent
{
}