<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Dashboard\Widgets\DoughnutChartWidget;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    if ($containerBuilder->hasDefinition(DoughnutChartWidget::class)) {
        $services = $configurator->services();

        $services->set('dashboard.widget.fixpunktExtensions')
            ->class(NumberWithIconWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(\Fixpunkt\Backendtools\Widgets\Provider\NumberOfExtensionsDataProvider::class))
            ->arg('$options', [
                'title' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_with_icon_widget.title',
                'subtitle' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_with_icon_widget.subtitle',
                'icon' => 'content-tab'
            ])
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktExtensions',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about extensions',
                    'description' => 'No. of extensions in the typo3conf/ext folder.',
                    'iconIdentifier' => 'content-widget-number',
                    'height' => 'small',
                    'width' => 'small'
                ]
            );

        $services->set('dashboard.widget.fixpunktFiles')
            ->class(NumberWithIconWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(\Fixpunkt\Backendtools\Widgets\Provider\NumberOfFilesDataProvider::class))
            ->arg('$options', [
                'title' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_of_files_widget.title',
                'subtitle' => 'LLL:EXT:backendtools/Resources/Private/Language/locallang.xlf:number_of_files_widget.subtitle',
                'icon' => 'content-gallery'
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
                    'width' => 'small'
                ]
            );

        $services->set('dashboard.widget.fixpunktPages')
            ->class(DoughnutChartWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(\Fixpunkt\Backendtools\Widgets\Provider\PagesDataProvider::class))
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktPages',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about pages',
                    'description' => 'No. of deleted / hidden / visible pages.',
                    'iconIdentifier' => 'content-widget-chart-pie',
                    'height' => 'medium',
                    'width' => 'small'
                ]
            );

        $services->set('dashboard.widget.fixpunktContent')
            ->class(DoughnutChartWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(\Fixpunkt\Backendtools\Widgets\Provider\ContentDataProvider::class))
            ->tag(
                'dashboard.widget',
                [
                    'identifier' => 'fixpunktContent',
                    'groupNames' => 'fixpunkt',
                    'title' => 'Statistic about content elements',
                    'description' => 'No. of deleted / hidden / visible content elements.',
                    'iconIdentifier' => 'content-widget-chart-pie',
                    'height' => 'medium',
                    'width' => 'small'
                ]
            );

    }
};