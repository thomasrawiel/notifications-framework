<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Utility\TreeListUtility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsController]
final class SettingsController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly UriBuilder            $uriBuilder,
        protected readonly SettingsUtility       $settingsUtility,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);

        $this->moduleTemplate->assignMultiple([
            'settings' => $this->settingsUtility->getSettings(),
            'allowedTables' => $this->getAllowedTables(),
            'feUserLookupUids' => $this->getFeUserLookupUids(),
            'notificationStorage' => !$this->settingsUtility->storeNotificationsOnRecordPid() ? $this->getNotificationStorage() : [],
        ]);

        return $this->moduleTemplate->renderResponse('Backend/Settings');
    }

    private function getAllowedTables(): array
    {
        $tables = [];
        foreach ($this->settingsUtility->getAllowedTables() as $table) {
            $tables[$table] = [
                'title' => $this->translate($GLOBALS['TCA'][$table]['ctrl']['title']),
                'iconfile' => $GLOBALS['TCA'][$table]['ctrl']['iconfile'] ?? null,
                'icon' => $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes']['default'] ?? null,
            ];
        }

        return $tables;
    }

    private function getFeUserLookupUids(): array
    {
        return $this->getPagesArray(
            $this->settingsUtility->getFeUserLookupUids(),
            $this->settingsUtility->getFeUserLookupRecursive()
        );
    }

    private function getNotificationStorage(): array
    {
        return $this->getPagesArray(
            $this->settingsUtility->getNotificationStorage(),
            $this->settingsUtility->getNotificationStorageRecursive()
        );
    }

    private function getPagesArray(array $pidList, int $recursive): array
    {
        $treeList = $pidList;
        $treeListUtility = GeneralUtility::makeInstance(TreeListUtility::class);
        if ($treeList !== [] && $recursive > 0) {
            $treeList = $treeListUtility->getTreeListArrayFromArray($treeList, $recursive);
        }

        $pages = [];
        foreach ($treeList as $pid) {
            if ($pid > 0) {
                $page = BackendUtility::getRecord('pages', $pid, 'uid,pid,title,doktype,hidden,is_siteroot,nav_hide,module');

                $icon = $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$page['doktype']];

                if ($page['nav_hide']) {
                    $icon = $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$page['doktype'] . '-hideinmenu'] ?? $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['default'];
                }
                if ($page['is_siteroot']) {
                    $icon = $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'][$page['doktype'] . '-root'] ?? $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['default'];
                }
                if ($page['module']) {
                    $icon = $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-' . $page['module']] ?? $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['default'];
                }
                $pages[$pid] = [
                    'uid' => $page['uid'],
                    'pid' => $page['pid'],
                    'title' => $page['title'],
                    'icon' => $icon,
                    'inSettings' => in_array($pid, $pidList),
                    'iconOverlay' => !$page['hidden'] ? false : 'overlay-hidden',
                ];
            } else {
                $pages[$pid] = [
                    'uid' => $pid,
                    'pid' => $pid,
                    'title' => 'Rootpage',
                    'inSettings' => in_array($pid, $pidList),
                    'icon' => 'actions-brand-typo3',
                ];
            }


        }

        return $treeListUtility->buildTree($pages);
    }


    private function translate(string $input): string
    {
        return $this->getLanguageService()->sL($input);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
