<?php

namespace TRAW\NotificationsFramework\Form;

use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\DateUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NotificationPreview extends AbstractFormElement
{

    public function render()
    {
        $result = $this->initializeResultArray();

        $result['html'] = $this->renderNotificationPreview();

        return $result;
    }

    private function renderNotificationPreview(): string
    {
        $type = $this->data['databaseRow']['type'][0]
            ?? $this->data['databaseRow']['type']
            ?? 'default';

        $typeClass = GeneralUtility::makeInstance(Type::class);
        $recordTypes = $typeClass->getTypesWithRecordField();
        $customTypes = $typeClass->getTypesWithCustomMessage();

        if (in_array($type, $recordTypes, true)) {
            $previewData = $this->renderRecordPreview();
        }

        if (in_array($type, $customTypes, true)) {
            $previewData = $this->renderCustomPreview();
        }



        $content = '<div class="notification-item__preview">'

            . sprintf('<div class="notification-item__preview__media">
                      <img src="%s" alt="%s" width="100" />
                    </div>
                    <div class="notification-item__preview__content">
                      <strong class="notification-item__preview__title">%s</strong>
                      <p class="notification-item__preview__text">%s</p>
                      <p class="notification-item__preview__timestamp">%s</p>
                      <div class="notification-item__preview__link">
                        <div>Link: %s</div>


                        </div>
                    </div>',
                "https://picsum.photos/300/300", $previewData['title'], $previewData['title'], $previewData['text'], $previewData['timestamp'], $previewData['url'])
            . '</div>';

        return $content;


    }

    private function renderCustomPreview(): array
    {
        $isoDate = new \Datetime();
        $isoDate->setTimestamp($this->data['databaseRow']['tstamp']);

        return [
            'title' => $this->data['databaseRow']['label'],
            'text' => $this->data['databaseRow']['message'],
            'url' => $this->data['databaseRow']['url'],
            'timestamp' => DateUtility::getTimeAgo($isoDate),
        ];
    }

    private function renderRecordPreview(string $recordIdentifier): array
    {

    }
}
