<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Domain\Model\BackendUserInfo;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class AfterDatabaseOperationsEvent
 */
final class AfterDatabaseOperationsEvent extends AbstractEvent
{
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
    public function __construct(private BackendUserInfo $backendUser, private $status, private $table, private $id, private array $fieldArray, private \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj)
    {
        parent::__construct($backendUser);
        $this->status = $status;
        $this->table = $table;
        $this->id = $id;
        $this->fieldArray = $fieldArray;
        $this->pObj = $pObj;
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
     * @return mixed
     */
    public function getPObj(): \TYPO3\CMS\Core\DataHandling\DataHandler
    {
        return $this->pObj;
    }
}