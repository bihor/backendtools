# backendtools

Version 6.0.2

8 admin tools for extensions, pages, (backend) layouts, slug, redirects, files, images and links:
extension-list, recent pages and content elements, used (backend) layouts, import redirects, check redirects, 
show where missing files are used, images with no title/alt-text and linklist.

You find the documentation for this extension at typo3.org:
https://docs.typo3.org/p/fixpunkt/backendtools/master/en-us/

Version 5.0:
- First version for TYPO3 12 LTS.

Version 5.1.2:
- New tool: show where missing files are used.
- Bugfix: show meta-data again in "Show images without title- or alt-text".

Version 5.2.0:
- Refactored with the rector-tool.
- setup.txt and constants.txt renamed to .typoscript.

Version 5.3.0:
- Possibility added, to delete missing image-entries.
- Image-preview added to "Find used images without title- or alt-text".

Version 5.3.1/2/3:
- Widget "No. of extensions" deleted. Widget "Statistic about image meta tags" added.

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