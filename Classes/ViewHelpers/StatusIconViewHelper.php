<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\ViewHelpers;

use FluidTYPO3\Vhs\Core\ViewHelper\AbstractViewHelper;
use TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

class StatusIconViewHelper extends AbstractViewHelper
{
    public function __construct(private readonly IconFactory $iconFactory)
    {

    }

    public function initializeArguments()
    {
        $this->registerArgument('configuration', 'array', 'The configuration', true);
    }

    public function render(): string
    {
        $configuration = $this->arguments['configuration'];

        $icon = RecordIconOverlayUtility::getRecordIconOverlay($configuration);

        return $this->iconFactory->getIcon($icon, Icon::SIZE_SMALL)->render();
    }
}
