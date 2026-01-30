<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;

#[AsEventListener(
    identifier: 'traw-notifications/notifcation-json-data',
)]
class NotificationJsonDataEventListener
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly ImageUtility            $imageUtility
    )
    {
    }

    public function __invoke(NotificationJsonDataEvent $event)
    {
        $data = $event->getData();

        if (empty($data['media'])) {
            $configuration = $this->fetchConfiguration($event->getConfiguration()['configuration']);
            $data['media'] = $this->attachConfigurationImageUrl($configuration);
        }

        $event->setData($data);
    }

    protected function attachConfigurationImageUrl(?Configuration $configuration): ?string
    {
        if (empty($configuration)) {
            return null;
        }

        $lookupTable = $configuration->getTable();
        $lookupUid = $configuration->getUid();

        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);

        if (!empty($configuration->getRecord())) {
            //tx_news_domain_model_news_12345 => (int)12345
            $lookupUid = (int)substr($configuration->getRecord(), strlen($configuration->getTable()) + 1);
            foreach ($this->imageUtility->guessImageField($lookupTable) as $field) {
                $fileObjects = $fileRepository->findByRelation($lookupTable, $field, $lookupUid);
                if (!empty($fileObjects)) {
                    return $this->getProcessedImageUrl($fileObjects[0]);
                }
            }
        } else {
            $fileObjects = $fileRepository->findByRelation($lookupTable, Configuration::IMAGE_FIELD, $lookupUid);
            if (!empty($fileObjects)) {
                return $this->getProcessedImageUrl($fileObjects[0]);
            }
        }


        return null;
    }


    private function getProcessedImageUrl(FileReference $fileReference): ?string
    {
        $processedImage = $this->imageUtility->getProcessedImage($fileReference);
        if ($processedImage instanceof ProcessedFile) {
            return PathUtility::getAbsoluteWebPath($processedImage->getPublicUrl());
        }
        return null;
    }

    private function fetchConfiguration(int $configurationUid)
    {
        return $this->configurationRepository->findByUid($configurationUid);
    }
}
