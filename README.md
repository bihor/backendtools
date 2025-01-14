# backendtools

Version 6.0.4

8 admin tools for extensions, pages, (backend) layouts, slug, redirects, files, images and links:
extension-list, recent pages and content elements, used (backend) layouts, import redirects, check redirects,
show where missing files are used, images with no title/alt-text and linklist.

You find the documentation for this extension at typo3.org:
https://docs.typo3.org/p/fixpunkt/backendtools/master/en-us/


Version 6.0:
- Version for TYPO3 12 and 13 LTS.
- Refactored with the rector-extension.
- Support for the gridelements-extension and switchableControllerActions dropped.
- Tool file-deletion (in uploads folder) deleted.
- Bugfix: replace-button for empty alt/title-tags fixed.
- "Flush TYPO3 and PHP Cache" must be clicked before first use.

Version 6.0.2:
- Source=target check added to redirects-check.
- Bugfix: styles added again.

Version 6.0.3:
- Access of the module changed from invalid value to "admin".
- Dashboard requirement removed. TYPO3 requirement changed from 12.5 to 12.4.

Version 6.0.4:
- Compatibility for PHP 8.4.
