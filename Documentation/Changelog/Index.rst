﻿.. include:: /Includes.rst.txt

.. _changelog:

Changelog
=========
Version 6.0.6:
Bugfix: bottom-buttons are now working again.

Version 6.0.5:
Refactoring, Documentation.

Version 6.0.3:
Access of the module changed from invalid value to "admin".
Dashboard requirement removed. TYPO3 requirement changed from 12.5 to 12.4.

Version 6.0.2:
Source=target check added to redirects-check.
Bugfix: styles added again.

Version 6.0:
Version for TYPO3 12 and 13 LTS.
Refactored with the rector-extension.
Support for the gridelements-extension and switchableControllerActions dropped.
Tool file-deletion (in uploads folder) deleted.
Bugfix: replace-button for empty alt/title-tags fixed.
"Flush TYPO3 and PHP Cache" must be clicked before first use.

Version 5.3.3:
Widget "No. of extensions" deleted. Widget "Statistic about image meta tags" added.

Version 5.3.0:
Possibility added, to delete missing image-entries.
Image-preview added to "Find used images without title- or alt-text".

Version 5.2.0:
Refactored with the rector-tool.
setup.txt and constants.txt renamed to .typoscript.

Version 5.1.0/2:
New tool: show where missing files are used.
Bugfix: show meta-data again in "Show images without title- or alt-text".

Version 5.0:
Version for TYPO3 12 LTS.

Version 4.3.0/1:
More infos and search in tx_gridelements_backend_layout.
Bugfix (for PHP 8).

Version 4.2.0:
New tool: shows you where (backend) layouts are in use.
4 dashboard widgets added.
Bugfix for PHP 8.

Version 4.1.0:
New tool: show recently modified pages and content elements.
Layout adapted to TYPO3 11. Runs now with PHP 8.1 too.
Checking of start- and endtime added.

Version 4.0:
Now for TYPO3 10 and 11.
Breaking: action realurl removed.

Version 3.0:
List views: search in the rootline and selectbox in the extension-list added.
Extension-key added to composer.json.
Breaking: action unzip removed.

Version 2.1.0:
Redirects check tool added.
misc field added in the list of used extensions.
Bugfix: Redirects import.

Version 2.0.6:
Order by added to the extension list view.
Bugfix: use translated values when L>0.
Bugfix: edit page links when L>0.
Bugfix: domain + language configuration.
Bugfix: icons for TYPO3 10.

Version 2.0:
Refactoring: large modification of the queries.
FE-links: domain + language entry point added, L removed.
Link added to all page-titles + the csv-view.
