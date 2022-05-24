<?php

declare(strict_types = 1);

namespace Fixpunkt\Backendtools\Widgets;

use Fixpunkt\Backendtools\Widgets\Provider\BeLayoutsDataProvider;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class BackendLayoutWidget implements WidgetInterface
{
    /**
     * @var WidgetConfigurationInterface
     */
    private $configuration;

    /**
     * @var StandaloneView
     */
    private $view;

    /**
     * @var array
     */
    private $options;

    /**
     * @var BeLayoutsDataProvider
     */
    private $dataProvider;

    public function __construct(
        WidgetConfigurationInterface $configuration,
        StandaloneView $view,
        BeLayoutsDataProvider $dataProvider,
        array $options = []
    ) {
        $this->configuration = $configuration;
        $this->view = $view;
        $this->dataProvider = $dataProvider;
        $this->options = array_merge(
            [
                'showErrors' => true,
                'showWarnings' => false
            ],
            $options
        );
    }

    public function renderWidgetContent(): string
    {
        $this->view->setTemplate('Widget/BackendLayoutWidget');
        $this->view->assignMultiple([
            'configuration' => $this->configuration,
            'layouts' => $this->dataProvider->getBeLayouts()
        ]);

        return $this->view->render();
    }
}
