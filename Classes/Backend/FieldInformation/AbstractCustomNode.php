<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractCustomNode extends AbstractFormElement
{
    protected $iconFactory;

    public function __construct(?NodeFactory $nodeFactory = null, array $data = [])
    {
        parent::__construct($nodeFactory, $data);
        if (empty($this->iconFactory)) {
            $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        }
    }

    protected function callout(string $title = '', string $body = '', string $action = '', string $type = ''): string
    {
        $pattern = '<div class="t3js-infobox callout callout-sm callout-%s"><div class="media"><div class="media-left"><span class="icon-emphasized">%s</span></div><div class="media-body"><div class="callout-title"><strong>%s</strong></div><div class="callout-body"><p class="mt-2">%s</p>%s</div></div></div></div>';
        $icon = $this->getCalloutIcon($type, $action);
        return sprintf($pattern, $type, $icon, $title, $body, $action);
    }

    protected function getCalloutIcon(string $type = '', string $action = ''): string
    {
        $icon = $this->iconFactory->getIcon('actions-info', Icon::SIZE_SMALL);

        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $this->iconFactory->getIcon('actions-info', Icon::SIZE_SMALL);

        if ($type === 'danger') {
            $icon = $this->iconFactory->getIcon('actions-exclamation', Icon::SIZE_SMALL);
        }

        if ($type === 'success') {
            $icon = $this->iconFactory->getIcon('actions-check', Icon::SIZE_SMALL);
        }

        if ($action !== '') {
            $icon = $this->iconFactory->getIcon('actions-question', Icon::SIZE_SMALL);
        }

        return $icon->render();
    }

    protected function infoMsg(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'info');
    }

    protected function successMsg(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'success');
    }

    protected function warningMsg(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'warning');
    }

    protected function errorMsg(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'danger');
    }

    protected function renderHtml(string $fieldInformationHtml, array $nodeHtmlLines): string
    {
        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] = $fieldInformationHtml;
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-element">';
        $html[] = '<div class="form-control-wrap">';
        $html[] = implode(LF, $nodeHtmlLines);
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        return implode(LF, $html);
    }
}
