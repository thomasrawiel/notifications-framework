<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Data;

use TYPO3\CMS\Core\Resource\FileReference;

class NotificationPreviewImageCropVariantEvent
{
    public function __construct(private string $cropVariantIdentifier = 'default', private ?FileReference $fileReference = null)
    {
    }

    public function getCropVariantIdentifier(): string
    {
        return $this->cropVariantIdentifier;
    }

    public function setCropVariantIdentifier(string $cropVariantIdentifier): void
    {
        $this->cropVariantIdentifier = $cropVariantIdentifier;
    }

    public function getFileReference(): ?FileReference
    {
        return $this->fileReference;
    }
}
