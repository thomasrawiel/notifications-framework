<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Doctrine\DBAL\ParameterType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AjaxRoutesController
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ConnectionPool           $connectionPool,
        private readonly CacheManager             $cacheManager,
        private readonly ConfigurationRepository  $configurationRepository,
    )
    {
    }

    public function updateConfigurationWithSuggestion(ServerRequestInterface $request): ResponseInterface
    {
        $data = (array)$request->getParsedBody();

        $field = $data['field'] ?? throw new \InvalidArgumentException('Please provide a fieldname', 1580585107);
        $value = $data['value'] ?? throw new \InvalidArgumentException('Please provide a value', 1580585107);
        $table = $data['table'] ?? null;

        $uid = isset($data['uid'])
            ? (int)$data['uid']
            : throw new \InvalidArgumentException('Please provide a record uid', 1580585107);

        $messageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);

        $success = false;
        $reload = false;

        $beUser = $GLOBALS['BE_USER'];
        $isAdmin = $beUser->isAdmin();
        $canModifyTable = $isAdmin
            || $beUser->check('tables_modify', $table);

        $isExcludeField = (bool)($GLOBALS['TCA'][$table]['columns'][$field]['exclude'] ?? false);

        $canModifyField = $isAdmin
            || !$isExcludeField
            || $beUser->check('non_exclude_fields', $table . ':' . $field);

        if (!$canModifyTable || !$canModifyField) {
            // @extensionScannerIgnoreLine
            $messageQueue->addMessage(
                GeneralUtility::makeInstance(
                    FlashMessage::class,
                    "You don't have permission to modify this field of this record.",
                    "Permission denied.",
                    ContextualFeedbackSeverity::ERROR,
                    true
                )
            );

            return $this->jsonResponse([
                'success' => false,
                'reload' => true,
            ]);
        }

        $tableName = $table ?? Configuration::TABLE_NAME;

        $valid = $this->validate($field, $value, $uid, $tableName);
        $updated = $valid ? $this->update($field, $value, $uid, $tableName) : false;

        $success = $valid && $updated;


        if ($success) {
            // @extensionScannerIgnoreLine
            $messageQueue->addMessage(
                GeneralUtility::makeInstance(
                    FlashMessage::class,
                    "$field was set to value '$value'",
                    "Record updated successfully",
                    ContextualFeedbackSeverity::OK,
                    true
                )
            );
        }

        $flushUids = $this->resolveFlushUids($tableName, $uid);
        $this->flushCacheForConfigurations($flushUids);

        return $this->jsonResponse([
            'success' => $success,
            'reload' => true,
        ]);
    }

    private function jsonResponse(array $data): ResponseInterface
    {
        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response;
    }

    private function resolveFlushUids(string $tableName, int $uid): array
    {
        if ($tableName === Configuration::TABLE_NAME) {
            return [$uid];
        }

        $demand = ['record' => $tableName . '_' . $uid,];
        return array_column(
            $this->configurationRepository->getConfigurationsByDemand($demand),
            'uid'
        );
    }

    private function flushCacheForConfigurations(array $uids): void
    {
        foreach ($uids as $uid) {
            $this->cacheManager->flushCachesByTag('tx_notifications_framework_validation_record_' . $uid);
            $this->cacheManager->flushCachesByTag('tx_notifications_framework_audience_record_' . $uid);
        }
    }

    private function validate(string $field, string $value, int $uid, string $table): bool
    {
        if (!isset($GLOBALS['TCA'][$table])) {
            return false;
        }

        $configuration = $GLOBALS['TCA'][$table]['columns'];

        if (!isset($configuration[$field])) {
            return false;
        }

        if ($configuration[$field]['config']['type'] === 'select' && !in_array($value, array_column($configuration[$field]['config']['items'], 'value'))) {
            return false;
        }

        return $this->recordExists($uid, $table);
    }

    private function getQb(string $table): QueryBuilder
    {
        $qb = $this->connectionPool->getQueryBuilderForTable($table);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $qb;
    }

    private function recordExists(int $uid, string $table): bool
    {
        $qb = $this->getQb($table);

        return 0 < $qb->count('uid', $table, ['uid' => $uid]);
    }

    private function update(string $field, string $value, int $uid, string $table): bool
    {
        $qb = $this->getQb($table);

        return 0 < $qb->update($table)
                ->where(
                    $qb->expr()->or(
                        $qb->expr()->eq('uid', $qb->createNamedParameter($uid, ParameterType::INTEGER)),
                        $qb->expr()->eq('l10n_parent', $qb->createNamedParameter($uid, ParameterType::INTEGER)),
                    )
                )
                ->set($field, $value)
                ->executeStatement();
    }
}
