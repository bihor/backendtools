<?php

declare(strict_types=1);

namespace Fixpunkt\Backendtools\Widgets\Provider;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class NumberOfExtensionsDataProvider implements NumberWithIconDataProviderInterface
{
    private array $options = [];
    public function getNumber(int $secondsBack = 86400): int
    {
        $dir = Environment::getPublicPath() . '/typo3conf/ext/*';
        return count(array_filter(glob($dir), 'is_dir'));
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
