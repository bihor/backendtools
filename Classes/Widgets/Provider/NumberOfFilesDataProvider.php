<?php

declare(strict_types=1);

namespace Fixpunkt\Backendtools\Widgets\Provider;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class NumberOfFilesDataProvider implements NumberWithIconDataProviderInterface
{

    public function getNumber(int $secondsBack = 86400): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
        return (int)$queryBuilder
            ->count('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->eq('missing', $queryBuilder->createNamedParameter(1))
            )
            ->executeQuery()
            ->fetchOne();
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
