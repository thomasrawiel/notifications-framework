<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Doctrine\DBAL\ParameterType;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
    )
    {
    }

    public function updateConfigurationWithSuggestion(ServerRequestInterface $request): ResponseInterface
    {
        $field = $request->getParsedBody()['field']
            ?? throw new \InvalidArgumentException(
                'Please provide a fieldname',
                1580585107,
            );
        $value = $request->getParsedBody()['value']
            ?? throw new \InvalidArgumentException(
                'Please provide a value',
                1580585107,
            );

        $table = $request->getParsedBody()['table'] ?? null;

        $uid = (int)$request->getParsedBody()['uid']
            ?? throw new \InvalidArgumentException(
                'Please provide a record uid',
                1580585107,
            );

        $valid = $this->validate($field, $value, $uid, $table ?? 'tx_notifications_framework_configuration');
        $update = $valid ? $this->update($field, $value, $uid, $table ?? 'tx_notifications_framework_configuration') : false;

        $result = ['success' => $valid && $update];

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(
            json_encode($result, JSON_THROW_ON_ERROR),
        );

        if ($result['success']) {
            (GeneralUtility::makeInstance(FlashMessageService::class))
                ->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE)
                ->addMessage(GeneralUtility::makeInstance(FlashMessage::class,
                    "$field was set to value '$value'",
                    'Record updated successfully',
                    $result['success'] ? ContextualFeedbackSeverity::OK : ContextualFeedbackSeverity::WARNING,
                    true
                ));
        }

        $this->cacheManager->flushCachesByTag('tx_notifications_framework_validation_record_'.$uid);
        $this->cacheManager->flushCachesByTag('tx_notifications_framework_audience_record_'.$uid);

        return $response;
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

    private function recordExists(int $uid, string $table): bool
    {
        $qb = $this->connectionPool->getQueryBuilderForTable($table);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return 0 < $qb->count('uid', $table, ['uid' => $uid]);
    }

    /**
     * return affected rows
     */
    private function update(string $field, string $value, int $uid, string $table): bool
    {
        $qb = $this->connectionPool->getQueryBuilderForTable($table);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

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
