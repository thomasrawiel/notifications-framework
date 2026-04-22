<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\ViewHelpers;

use FluidTYPO3\Vhs\Core\ViewHelper\AbstractViewHelper;
use TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

class TypeIconViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('type', 'string', '', true);
    }

    public function render(): string
    {
        $items = $GLOBALS['TCA']['tx_notifications_framework_configuration']['columns']['type']['config']['items'];

        $type = $this->arguments['type'];

        $result = array_values(array_filter(
            $items,
            static fn(array $item): bool => ($item['value'] ?? null) === $type
        ))[0] ?? null;

        if($result !== null) {
            return $result['icon'];
        }else {
            return 'default-not-found';
        }
    }
}
