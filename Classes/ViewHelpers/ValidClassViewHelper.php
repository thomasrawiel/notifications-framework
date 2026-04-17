<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\ViewHelpers;

use FluidTYPO3\Vhs\Core\ViewHelper\AbstractViewHelper;
use TRAW\NotificationsFramework\Utility\ValidationUtility;

class ValidClassViewHelper extends AbstractViewHelper
{
    public function __construct(private readonly ValidationUtility $validationUtility){}

    public function initializeArguments()
    {
        $this->registerArgument('value', 'int', 'Returns the css class equivalent to a validation value', true, 0);
        $this->registerArgument('configuration', 'array', 'The configuration', true);
    }

    public function render(): string
    {
        $valid = $this->arguments['value'];
        $configuration = $this->arguments['configuration'];

        return $this->validationUtility->getNotificationLevel($valid);
    }
}
