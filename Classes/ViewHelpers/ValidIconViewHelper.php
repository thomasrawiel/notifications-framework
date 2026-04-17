<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\ViewHelpers;

use FluidTYPO3\Vhs\Core\ViewHelper\AbstractViewHelper;
use TRAW\NotificationsFramework\Utility\ValidationUtility;
use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Core\Imaging\Icon;

class ValidIconViewHelper extends AbstractViewHelper
{
    public function __construct(private readonly ValidationUtility $validationUtility)
    {
    }

    public function initializeArguments()
    {
        $this->registerArgument('value', 'int', 'Integer value returned by Validation', true, 0);
        $this->registerArgument('configuration', 'array', 'The configuration', true);
    }

    public function render(): string
    {
        $valid = $this->arguments['value'];
        $configuration = $this->arguments['configuration'];

        return $this->validationUtility->getAction($valid, $configuration, false, false, Icon::SIZE_SMALL);
    }
}
