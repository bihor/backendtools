<?php

declare(strict_types=1);

namespace Fixpunkt\Backendtools\Widgets\Provider;

use Fixpunkt\Backendtools\Domain\Repository\SessionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

class ImagesDataProvider implements ChartDataProviderInterface
{
    public function __construct(protected SessionRepository $sessionRepository) {}

    /**
     * @inheritDoc
     */
    public function getChartData(): array
    {
        $sessionRepository = GeneralUtility::makeInstance(SessionRepository::class);
        $images1 = count($sessionRepository->getImagesWithout(1, 1));
        $images2 = count($sessionRepository->getImagesWithout(2, 1));
        $images4 = count($sessionRepository->getImagesWithout(4, 1));
        $images5 = count($sessionRepository->getImagesWithout(5, 1));

        return [
            'labels' => [
                'without alt-tag',
                'without title-tag',
                'with alt-tag',
                'with title-tag',
            ],
            'datasets' => [
                [
                    'backgroundColor' => WidgetApi::getDefaultChartColors(),
                    'data' => [$images1, $images2, $images4, $images5],
                ],
            ],
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
