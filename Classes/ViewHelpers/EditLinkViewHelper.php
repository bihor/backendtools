<?php
namespace Fixpunkt\Backendtools\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditLinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {
	
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
		$this->registerTagAttribute('action', 'string', 'Action to perform (new, edit)');
		$this->registerTagAttribute('table', 'string', 'Name of the related table');
		$this->registerTagAttribute('uid', 'integer', 'Id of the record to edit');
		$this->registerTagAttribute('language', 'integer', 'sys language uid', false, 0);
		$this->registerTagAttribute('returnUrl', 'string', 'URL to return to', false, '');
	}
	
	/**
	 * renders <ex:editLink>
     * Crafts a link to edit a database record or create a new one
     *
     * @return string The <a> tag
     * @see \TYPO3\CMS\Backend\Utility::editOnClick()
     */
    public function render()
    {
        // Edit all icon:
        $urlParameters = [
            'edit' => [
              	$this->arguments['table'] => [
              		$this->arguments['uid'] => $this->arguments['action']
                ]
            ],
        	'columnsOnly' => '',
            'createExtension' => 0
        ];
        if ($this->arguments['language'] > 0) {
            $urlParameters['overrideVals']['pages']['sys_language_uid'] = $this->arguments['language'];
        }
        $urlParameters['returnUrl'] = GeneralUtility::getIndpEnv('REQUEST_URI');
        $uriBuilder = GeneralUtility::makeInstance('TYPO3\CMS\Backend\Routing\UriBuilder');
        $uri = $uriBuilder->buildUriFromRoute('record_edit', $urlParameters);
      //  $uri = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('record_edit', $urlParameters);
      //  das hier funktioniert überhaupt nicht: 
      //  $uriBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
      //  $uri = $uriBuilder->buildUriFromRoute('record_edit', $urlParameters);

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }
}