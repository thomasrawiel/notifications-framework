<?php

/*
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace TRAW\NotificationsFramework\Domain\Model;

use In2code\Powermail\Domain\Factory\FileFactory;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * File Reference
 */
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{/**
 * Various properties of the FileReference. Note that these information can be different
 * to the ones found in the originalFile.
 *
 * @var array
 */
    protected $propertiesOfFileReference = [];

    /**
     * Reference to the original File object underlying this FileReference.
     *
     * @var FileInterface
     */
    protected $originalFile;

    /**
     * Properties merged with the parent object (File) if
     * the value is not defined (NULL). Thus, FileReference properties act
     * as overlays for the defined File properties.
     *
     * @var array
     */
    protected $mergedProperties = [];

    /**
     * Obsolete when foreign_selector is supported by ExtBase persistence layer
     *
     * @var int
     */
    protected $uidLocal = 0;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $alternative = '';

    /**
     * @var string
     */
    protected $link = '';
    /**
     * @var string
     */
    protected string $crop = '';

    /**
     * This empty constructor is necessary so class is fully
     * extensible by other extensions that might want to define
     * an own __construct() method
     */
    public function __construct() {

    }

    /**
     * Set File uid
     *
     * @param int $fileUid
     */
    public function setFileUid($fileUid): void
    {
        $this->uidLocal = $fileUid;
    }

    /**
     * Get File UID
     *
     * @return int
     */
    public function getFileUid(): int
    {
        return $this->uidLocal;
    }

    /**
     * Set alternative
     *
     * @param string $alternative
     */
    public function setAlternative($alternative): void
    {
        $this->alternative = $alternative;
    }

    /**
     * Get alternative
     *
     * @return string
     */
    public function getAlternative(): string
    {
        return (string)($this->alternative !== '' ? $this->alternative : $this->getOriginalResource()->getAlternative());
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string)($this->description !== '' ? $this->description : $this->getOriginalResource()->getDescription());
    }

    /**
     * Set link
     *
     * @param string $link
     */
    public function setLink($link): void
    {
        $this->link = $link;
    }

    /**
     * Get link
     *
     * @return mixed
     */
    public function getLink()
    {
        return (string)($this->link !== '' ? $this->link : $this->getOriginalResource()->getLink());
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return (string)($this->title !== '' ? $this->title : $this->getOriginalResource()->getTitle());
    }

    public function hasProperty($key)
    {
        return array_key_exists($key, $this->getProperties());
    }

    /**
     * Gets a property, falling back to values of the parent.
     *
     * @param string $key The property to be looked up
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getProperty($key)
    {
        if (!$this->hasProperty($key)) {
            throw new \InvalidArgumentException('Property "' . $key . '" was not found in file reference or original file.', 1314226805);
        }
        $properties = $this->getProperties();
        return $properties[$key];
    }

    public function getProperties()
    {
        if (empty($this->mergedProperties)) {
            if(empty($this->originalFile)) {
                $this->originalFile = $this->getFileObject($this->uidLocal);
            }
            $this->mergedProperties = $this->propertiesOfFileReference;
            ArrayUtility::mergeRecursiveWithOverrule(
                $this->mergedProperties,
                $this->originalFile->getProperties(),
                true,
                true,
                false
            );
            array_walk($this->mergedProperties, [$this, 'restoreNonNullValuesCallback']);
        }

        return $this->mergedProperties;
    }

    private function getFileObject(int $uidLocal, ?ResourceFactory $factory = null): FileInterface
    {
        if ($factory === null) {
            $factory = GeneralUtility::makeInstance(ResourceFactory::class);
        }
        return $factory->getFileObject($uidLocal);
    }

    protected function restoreNonNullValuesCallback(&$value, $key)
    {
        if (array_key_exists($key, $this->propertiesOfFileReference) && $this->propertiesOfFileReference[$key] !== null) {
            $value = $this->propertiesOfFileReference[$key];
        }
    }


    public function getOriginalFile(): FileInterface {
        if(empty($this->originalFile)) {
            $this->originalFile = $this->getFileObject($this->uidLocal);
        }
        return $this->originalFile;
    }

    public function getCrop(): string
    {
        return $this->crop;
    }

    public function setCrop(string $crop): void
    {
        $this->crop = $crop;
    }
}
