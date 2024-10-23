<?php

declare(strict_types=1);

use Fixpunkt\Backendtools\Widgets\Provider\ContentDataProvider;
use Fixpunkt\Backendtools\Widgets\Provider\ImagesDataProvider;
use Fixpunkt\Backendtools\Widgets\Provider\NumberOfFilesDataProvider;
use Fixpunkt\Backendtools\Widgets\Provider\PagesDataProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Dashboard\Widgets\DoughnutChartWidget;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    if ($containerBuilder->hasDefinition(DoughnutChartWidget::class)) {
        $services = $configurator->services();

        $services->set('dashboard.widget.fixpunktFiles')
            ->class(NumberWithIconWidget::class)
            ->arg('$dataProvider', new Reference(NumberOfFilesDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->arg('$options', [
                'title' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_of_files_widget.title',
                'subtitle' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_of_files_widget.subtitle',
                'icon' => 'content-gallery',
            ])
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktFiles',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about files',
                    'description' => 'No. of missing files registered in sys_file.',
                    'iconIdentifier' => 'content-widget-number',
                    'height' => 'small',
                    'width' => 'small',
                ],
            );

        $services->set('dashboard.widget.fixpunktImages')
            ->class(DoughnutChartWidget::class)
            ->arg('$dataProvider', new Reference(ImagesDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktImages',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about image meta tags',
                    'description' => 'Images with and without alt- and title-tags.',
                    'iconIdentifier' => 'content-widget-chart-pie',
                    'height' => 'medium',
                    'width' => 'small',
                ],
            );

        $services->set('dashboard.widget.fixpunktPages')
            ->class(DoughnutChartWidget::class)
            ->arg('$dataProvider', new Reference(PagesDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktPages',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about pages',
                    'description' => 'No. of deleted / hidden / visible pages.',
                    'iconIdentifier' => 'content-widget-chart-pie',
                    'height' => 'medium',
                    'width' => 'small',
                ],
            );

        $services->set('dashboard.widget.fixpunktContent')
            ->class(DoughnutChartWidget::class)
            ->arg('$dataProvider', new Reference(ContentDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class))
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktContent',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about content elements',
                    'description' => 'No. of deleted / hidden / visible content elements.',
                    'iconIdentifier' => 'content-widget-chart-pie',
                    'height' => 'medium',
                    'width' => 'small',
                ],
            );

    }
};
