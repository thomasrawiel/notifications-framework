<?php

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility
    ;
use TYPO3\CMS\Core\Utility\StringUtility;

class ImageUtility
{
    public static function getProcessedImage(\TRAW\NotificationsFramework\Domain\Model\FileReference $fileReference): FileInterface
    {
        $cropVariantCollection = CropVariantCollection::create($fileReference->getCrop());
        $cropVariantName = 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariantName);
        $crop = $cropArea->makeAbsoluteBasedOnFile($fileReference->getOriginalFile());

        $processingConfiguration = [
            'crop' => $crop,
            'maxWidth' => 400,
        ];

        // The image needs to be processed if:
        //  - the image width is greater than the defined maximum width, or
        //  - there is a cropping other than the full image (starts at 0,0 and has a width and height of 100%) defined
        $needsProcessing = $fileReference->getProperty('width') > $processingConfiguration['maxWidth']
            || !$cropArea->isEmpty();
        if (!$needsProcessing) {
            return $fileReference->getOriginalFile();
        }

        return $fileReference->getOriginalFile()->process(
            ProcessedFile::CONTEXT_IMAGECROPSCALEMASK,
            $processingConfiguration
        );
    }

    public function createFileReferenceForNotification(Notification $notification, FileReference $configurationFileReference)
    {
        $referenceProperties = $configurationFileReference->getReferenceProperties();
       foreach(['uid','crdate','tstamp','uid_local','tablenames','uid_foreign','fieldname','pid'] as $removeProperty) {
           unset($referenceProperties[$removeProperty]);
       }

        $newId = StringUtility::getUniqueId('NEW');
        $fileObject = $configurationFileReference->getOriginalFile();

        $referenceProperties = array_replace($referenceProperties, [
            'uid_local' => $fileObject->getUid(),
            'tablenames' => Notification::TABLE_NAME,
            'uid_foreign' => $notification->getUid(),
            'fieldname' => Notification::IMAGE_FIELD,
            'pid' => $notification->getPid(),
        ]);

        $data = [];
        $data['sys_file_reference'][$newId] = $referenceProperties;
        $data[Notification::TABLE_NAME][$notification->getUid()] = [
            'pid' => $notification->getPid(),
            Notification::IMAGE_FIELD => $newId,
        ];
        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

        // Process the DataHandler data
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        // Error or success reporting
        if ($dataHandler->errorLog === []) {
            // ... handle success
        } else {
            // ... handle errors
        }
    }

    public static function getProcessedImageUri(Filereference $fileReference) {
        return self::getProcessedImage($fileReference)->getOriginalFile()->getUri();
    }
}
