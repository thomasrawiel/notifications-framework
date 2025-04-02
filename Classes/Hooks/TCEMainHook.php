<?php

namespace TRAW\NotificationsFramework\Hooks;


use TRAW\NotificationsFramework\Events\Database\AfterDatabaseOperationsEvent;
use TYPO3\CMS\Core\DataHandling\DataHandler;


/**
 * Class TCEmainHook
 */
class TCEMainHook extends AbstractHook
{
    /**
     * @param string      $status
     * @param string      $table
     * @param int         $id
     * @param array       $fieldArray
     * @param DataHandler $pObj
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, DataHandler &$pObj)
    {
        if ($this->settings->getAfterDatabaseOperations()) {
            $this->dispatchEvent(new AfterDatabaseOperationsEvent($this->getBeUserInfo(), $status, $table, $id, $fieldArray, $pObj));
        }
    }
}