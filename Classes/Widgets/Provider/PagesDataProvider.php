<?php

declare(strict_types=1);

namespace Fixpunkt\Backendtools\Widgets\Provider;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

class PagesDataProvider implements ChartDataProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getChartData(): array
    {
        $visiblePages = $this->getNumberOfPages(0);
        $hideInMenuPages = $this->getNumberOfPages(1);
        $hiddenPages = $this->getNumberOfPages(2);
        $deletedPages = $this->getNumberOfPages(3);

        return [
            'labels' => [
                'Enabled',
                'Hidden',
                'Deleted',
                'Hidden in menu'
            ],
            'datasets' => [
                [
                    'backgroundColor' => WidgetApi::getDefaultChartColors(),
                    'data' => [$visiblePages, $hiddenPages, $deletedPages, $hideInMenuPages],
                ],
            ],
        ];
    }

    protected function getNumberOfPages(int $mode = 0): int
    {
        $expression = null;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder
            ->getRestrictions()
            ->removeAll();
        if (!$mode) {
            $expression = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('nav_hide', $queryBuilder->createNamedParameter(0))
            );
        } elseif ($mode == 1) {
            $expression = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('nav_hide', $queryBuilder->createNamedParameter(1))
            );
        } elseif ($mode == 2) {
            $expression = $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(1))
            );
        } elseif ($mode == 3) {
            $expression = $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(1));
        }
        return (int)$queryBuilder
            ->count('*')
            ->from('pages')
            ->where(
                $expression
            )
            ->executeQuery()
            ->fetchOne();
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
