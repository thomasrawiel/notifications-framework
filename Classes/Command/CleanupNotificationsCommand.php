<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Command;

use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TRAW\NotificationsFramework\Domain\Model\Reference;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command for cleaning up old and read notifications and processed configurations.
 */
#[AsCommand(
    name: 'notifications:cleanup',
    description: 'Cleanup old and read notifications',
)]
final class CleanupNotificationsCommand extends Command
{
    /**
     * Number of days after which read notifications should be soft-deleted.
     */
    public const MAX_AGE_NOTIFICATIONS = 15;

    /**
     * Multiplier used to determine deletion threshold for all notifications, even if not read.
     */
    public const MAX_AGE_NOTIFICATIONS_FACTOR = 1.5;

    /**
     * Output interface used for CLI feedback.
     *
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @param ConnectionPool $connectionPool TYPO3 database connection pool.
     */
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private bool                    $verbose = false
    )
    {
        parent::__construct();
    }

    /**
     * Executes the cleanup logic for notifications and configurations.
     *
     * @param InputInterface  $input  The input instance.
     * @param OutputInterface $output The output instance.
     *
     * @return int Command exit code.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->verbose = $output->isVerbose();

        $this->removeDeleted();
        $this->removeReferences();
        $this->removeNotifications();

        return Command::SUCCESS;
    }

    /**
     * Permanently deletes all entries previously marked as "deleted".
     *
     * @param bool $verbose Whether to output verbose logging.
     *
     * @return void
     */
    private function removeDeleted(): void
    {
        $references = $this->connectionPool->getConnectionForTable(Reference::TABLE_NAME)
            ->delete(Reference::TABLE_NAME, ['deleted' => 1]);

        if ($this->verbose && $references) {
            $this->output->writeln("<info>Permanently deleted $references references previously marked as deleted.</info>");
        }

        $notifications = $this->connectionPool->getConnectionForTable(Notification::TABLE_NAME)
            ->delete(Notification::TABLE_NAME, ['deleted' => 1]);

        if ($this->verbose && $notifications) {
            $this->output->writeln("<info>Permanently deleted $notifications notifications previously marked as deleted.</info>");
        }

        $configurations = $this->connectionPool->getConnectionForTable(Configuration::TABLE_NAME)
            ->delete(Configuration::TABLE_NAME, ['deleted' => 1]);

        if ($this->verbose && $configurations) {
            $this->output->writeln("<info>Permanently deleted $configurations configurations previously marked as deleted.</info>");
        }
    }

    /**
     * Marks old and read notifications as "deleted".
     *
     * @param bool $verbose Whether to output verbose logging.
     *
     * @return void
     */
    private function removeReferences(): void
    {
        $maxTstampRead = $this->daysAgo(self::MAX_AGE_NOTIFICATIONS);
        $maxTstampAgeAll = $this->daysAgo(self::MAX_AGE_NOTIFICATIONS, self::MAX_AGE_NOTIFICATIONS_FACTOR);

        // Remove read notifications
        $qb = $this->connectionPool->getQueryBuilderForTable(Reference::TABLE_NAME);
        $read = $qb->update(Reference::TABLE_NAME)
            ->set('deleted', 1)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0, ParameterType::INTEGER)),
                $qb->expr()->eq('read', $qb->createNamedParameter(1, ParameterType::INTEGER)),
                $qb->expr()->lte('read_date', $qb->createNamedParameter($maxTstampRead, ParameterType::INTEGER)),
            ))
            ->executeStatement();

        if ($this->verbose && $read) {
            $this->output->writeln("<info>$read read references were marked as deleted.</info>");
        }

        $qb = $this->connectionPool->getQueryBuilderForTable(Reference::TABLE_NAME);
        $all = $qb->update(Reference::TABLE_NAME)
            ->set('deleted', 1)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0, ParameterType::INTEGER)),
                $qb->expr()->lte('tstamp', $qb->createNamedParameter($maxTstampAgeAll, ParameterType::INTEGER)),
            ))
            ->executeStatement();

        if ($this->verbose && $all) {
            $this->output->writeln("<info>$all old references were marked as deleted.</info>");
        }
    }

    private function removeNotifications()
    {
        $maxTstampAgeAll = $this->daysAgo(self::MAX_AGE_NOTIFICATIONS, self::MAX_AGE_NOTIFICATIONS_FACTOR);

        $qb = $this->connectionPool->getQueryBuilderForTable(Notification::TABLE_NAME);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $notifications = $qb->select('uid', 'tstamp', 'configuration')
            ->from(Notification::TABLE_NAME)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0, ParameterType::INTEGER)),
                $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0, ParameterType::INTEGER)),
                $qb->expr()->lte('tstamp', $qb->createNamedParameter($maxTstampAgeAll, ParameterType::INTEGER)),
            ))
            ->executeQuery()->fetchAllAssociative();

        if ($this->verbose && $notifications) {
            $all = $configurations = 0;
            foreach ($notifications as $notification) {
                $qb = $this->connectionPool->getQueryBuilderForTable(Reference::TABLE_NAME);
                $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
                $hasReferences = $qb->select('uid', 'notification')
                    ->from(Reference::TABLE_NAME)
                    ->where(
                        $qb->expr()->eq('notification', $qb->createNamedParameter($notification['uid'], ParameterType::INTEGER)),
                    )
                    ->executeQuery()->rowCount();

                if (!$hasReferences) {
                    $qb = $this->connectionPool->getQueryBuilderForTable(Notification::TABLE_NAME);
                    $all += $qb->update(Notification::TABLE_NAME)
                        ->set('deleted', 1)
                        ->where($qb->expr()->or(
                            $qb->expr()->eq('uid', $qb->createNamedParameter($notification['uid'], ParameterType::INTEGER)),
                            $qb->expr()->eq('l10n_parent', $qb->createNamedParameter($notification['uid'], ParameterType::INTEGER)),
                        ))
                        ->executeStatement();

                    $qb = $this->connectionPool->getQueryBuilderForTable(Configuration::TABLE_NAME);
                    $configurations += $qb->update(Configuration::TABLE_NAME)
                        ->set('deleted', 1)
                        ->where(
                            $qb->expr()->or(
                                $qb->expr()->eq('uid', $qb->createNamedParameter($notification['configuration'], ParameterType::INTEGER)),
                                $qb->expr()->eq('l10n_parent', $qb->createNamedParameter($notification['configuration'], ParameterType::INTEGER)),
                            ),
                        )->executeStatement();
                }
            }
            if ($this->verbose && $all) {
                $this->output->writeln("<info>$all old notifications were marked as deleted because they had no references.</info>");
                $this->output->writeln("<info>$configurations attached configurations were marked as deleted.</info>");
            }
        }
    }

    /**
     * Returns a timestamp X days ago, optionally multiplied by a factor.
     *
     * @param int   $days   Number of days ago.
     * @param float $factor Optional factor to multiply the day count.
     *
     * @return int UNIX timestamp.
     */
    private function daysAgo(int $days, float $factor = 1.0): int
    {
        return time() - (int)round($days * $factor * 86400, 0, PHP_ROUND_HALF_UP);
    }
}
