<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Domain\Model\BackendUserInfo;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class AfterDatabaseOperationsEvent
 */
final class AfterDatabaseOperationsEvent extends AbstractEvent
{
    /**
     * @var string
     */
    protected string $type = 'afterDatabaseOperation';

    /**
     * AfterDatabaseOperationsEvent constructor.
     *
     * @param BackendUserInfo $backendUser
     * @param                 $status
     * @param                 $table
     * @param                 $id
     * @param array           $fieldArray
     * @param DataHandler     $pObj
     */
    public function __construct(private BackendUserInfo $backendUser, private $status, private $table, private $id, private array $fieldArray, private \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        parent::__construct($backendUser);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getFieldArray(): array
    {
        return $this->fieldArray;
    }

    /**
     * @return DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }

    /**
     * @return string|null
     */
    public function getRecordIdentifier(): ?string
    {
        if($this->status === 'new') {
            if (!isset($this->dataHandler->substNEWwithIDs_table[$this->id]) || !isset($this->dataHandler->substNEWwithIDs[$this->id])) {
                return null;
            }

            return $this->dataHandler->substNEWwithIDs_table[$this->id]
                . '_'
                . $this->dataHandler->substNEWwithIDs[$this->id];
        }
        
        return $this->table
            . '_' 
            . $this->id;
        
    }
}