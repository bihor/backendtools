<?php
namespace Fixpunkt\Backendtools\ViewHelpers;

class EditLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	
	/**
	 * @var string
	 */
	protected $tagName = 'a';
	
	/**
	 * Initialize arguments
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments()
	{
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
		$this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
	}
	
	/**
	 * renders <ex:editLink>
     * Crafts a link to edit a database record or create a new one
     *
     * @param string $action Action to perform (new, edit)
     * @param string $table Name of the related table
     * @param int $uid Id of the record to edit
     * @param string $columnsOnly Comma-separated list of fields to restrict editing to
     * @param array $defaultValues List of default values for some fields (key-value pairs)
     * @param string $returnUrl URL to return to
     * @return string The <a> tag
     * @see \TYPO3\CMS\Backend\Utility::editOnClick()
     */
    public function render($action, $table, $uid, $columnsOnly = '', $defaultValues = array(), $returnUrl = '')
    {

        // Edit all icon:
        $urlParameters = [
                'edit' => [
                        $table => [
                                $uid => $action
                        ]
                ],
                'columnsOnly' => $columnsOnly,
                'createExtension' => 0,
                'returnUrl' => \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI')
        ];
        if (count($defaultValues) > 0) {
            $urlParameters['defVals'] = $defaultValues;
        }
        $uri = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('record_edit', $urlParameters);

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }
}
?>