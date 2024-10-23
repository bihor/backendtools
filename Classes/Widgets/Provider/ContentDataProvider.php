<?php

declare(strict_types=1);

namespace Fixpunkt\Backendtools\Widgets\Provider;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

class ContentDataProvider implements ChartDataProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getChartData(): array
    {
        $visiblePages = $this->getNumberOfContents(0);
        $hiddenPages = $this->getNumberOfContents(2);
        $deletedPages = $this->getNumberOfContents(3);

        return [
            'labels' => [
                'Enabled',
                'Hidden',
                'Deleted',
            ],
            'datasets' => [
                [
                    'backgroundColor' => WidgetApi::getDefaultChartColors(),
                    'data' => [$visiblePages, $hiddenPages, $deletedPages],
                ],
            ],
        ];
    }

    protected function getNumberOfContents(int $mode = 0): int
    {
        $expression = null;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll();
        if (!$mode) {
            $expression = $queryBuilder->expr()->and($queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)), $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0)));
        } elseif ($mode == 2) {
            $expression = $queryBuilder->expr()->and($queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0)), $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(1)));
        } elseif ($mode == 3) {
            $expression = $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(1));
        }
        return (int)$queryBuilder
            ->count('*')
            ->from('tt_content')
            ->where(
                $expression,
            )
            ->executeQuery()
            ->fetchOne();
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
