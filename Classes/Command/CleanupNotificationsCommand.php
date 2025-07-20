<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Core\Database\ConnectionPool;

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
     * Number of days after which configurations marked as "done" should be soft-deleted.
     */
    public const MAX_AGE_CONFIGURATIONS = 30;

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
    ) {
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
        $verbose = $output->isVerbose();

        $this->removeDeleted($verbose);
        $this->removeNotifications($verbose);
        $this->removeConfigurations($verbose);

        return Command::SUCCESS;
    }

    /**
     * Permanently deletes all entries previously marked as "deleted".
     *
     * @param bool $verbose Whether to output verbose logging.
     *
     * @return void
     */
    private function removeDeleted(bool $verbose = false): void
    {
        $notifications = $this->connectionPool->getConnectionForTable(Notification::TABLE_NAME)
            ->delete(Notification::TABLE_NAME, ['deleted' => 1]);

        if ($verbose && $notifications) {
            $this->output->writeln("<info>Permanently deleted $notifications notifications previously marked as deleted.</info>");
        }

        $configurations = $this->connectionPool->getConnectionForTable(Configuration::TABLE_NAME)
            ->delete(Configuration::TABLE_NAME, ['deleted' => 1]);

        if ($verbose && $configurations) {
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
    private function removeNotifications(bool $verbose = false): void
    {
        $maxTstampRead = $this->daysAgo(self::MAX_AGE_NOTIFICATIONS);
        $maxTstampAgeAll = $this->daysAgo(self::MAX_AGE_NOTIFICATIONS, self::MAX_AGE_NOTIFICATIONS_FACTOR);

        // Remove read notifications
        $qb = $this->connectionPool->getQueryBuilderForTable(Notification::TABLE_NAME);
        $read = $qb->update(Notification::TABLE_NAME)
            ->set('deleted', 1)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0)),
                $qb->expr()->eq('read', $qb->createNamedParameter(1)),
                $qb->expr()->lte('read_date', $qb->createNamedParameter($maxTstampRead)),
            ))
            ->executeStatement();

        if ($verbose && $read) {
            $this->output->writeln("<info>$read read notifications were marked as deleted.</info>");
        }

        // Important: Create new QueryBuilder instance, as previous one may retain internal state
        $qb = $this->connectionPool->getQueryBuilderForTable(Notification::TABLE_NAME);
        $all = $qb->update(Notification::TABLE_NAME)
            ->set('deleted', 1)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0)),
                $qb->expr()->lte('tstamp', $qb->createNamedParameter($maxTstampAgeAll)),
            ))
            ->executeStatement();

        if ($verbose && $all) {
            $this->output->writeln("<info>$all old notifications were marked as deleted.</info>");
        }
    }

    /**
     * Marks old processed configurations as "deleted".
     *
     * @param bool $verbose Whether to output verbose logging.
     *
     * @return void
     */
    private function removeConfigurations(bool $verbose = false): void
    {
        $maxTstampDone = $this->daysAgo(self::MAX_AGE_CONFIGURATIONS);

        $qb = $this->connectionPool->getQueryBuilderForTable(Configuration::TABLE_NAME);
        $configurations = $qb->update(Configuration::TABLE_NAME)
            ->set('deleted', 1)
            ->where($qb->expr()->and(
                $qb->expr()->eq('deleted', $qb->createNamedParameter(0)),
                $qb->expr()->eq('done', $qb->createNamedParameter(1)),
                $qb->expr()->lte('tstamp', $qb->createNamedParameter($maxTstampDone)),
            ))
            ->executeStatement();

        if ($verbose && $configurations) {
            $this->output->writeln("<info>$configurations processed configurations were marked as deleted.</info>");
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