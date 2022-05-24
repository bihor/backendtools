.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

This extension provides some useful tools in the “Admin tools” section of the backend of TYPO3.
It contains up to 10 tools for extensions, pages, slugs, redirects, files, images and links.

Tool 1: shows you all pages where you use extensions. You can see where you use which extension.
You can search for non-extensions too, e.g. if you set Ctype='mailform'.

Tool 2: shows recently modified pages and content elements.

Tool 3: helps you to delete unused files. Go to “Admin tools” → DB check” → “Database Relations” to find files which you can delete.
This tool helps you to delete these files.

Tool 4: helps you to find pages where layouts or backend layouts are in use.

Tool 5: helps you to find pages that link to another page. You can find any content element with a link to a specific page.

Tool 6: helps you to find images with no title- oder alternative-text. This tool can set that values for you too.

Tool 7: you can import simple redirect rules form your .htaccess to the table sys_redirect.

Tool 8: you can check all your entries of sys_redirect, if the target links are reachable.

Tool 9: shows differences between RealURL-pagepath (in tx_realurl_pathdata) and Slug (in pages).
Note: the old RealURL-tables must be present!
Note: this tool was removed in version 4.0.0.

Tool 10: unzip a zig file in the fileadmin-folder.
Note: this tool was removed in version 3.0.0.

Since version 4.2.0 there are some dashboard widgets available too.


.. _screenshots:

Screenshots
-----------

Some screenshots from the backend module.

.. figure:: ../Images/backendtools1.jpg
   :width: 712px
   :alt: Resized Screenshot 1 of the Backendtools

.. figure:: ../Images/backendtools2.jpg
   :width: 700px
   :alt: Resized Screenshot 2 of the Backendtools

.. figure:: ../Images/backendtools3.jpg
   :width: 712px
   :alt: Resized Screenshot 3 of the Backendtools

.. figure:: ../Images/backendtools4.jpg
   :width: 654px
   :alt: Screenshot 4 of the Backendtools

.. _made-in:

Made in...
----------

Thanks to the
`fixpunkt werbeagentur gmbh, Bonn <https://www.fixpunkt.com/webentwicklung/typo3/>`_
for giving me the possibility to realize
`this extension <https://www.fixpunkt.com/webentwicklung/typo3/typo3-extensions/>`_
and share it with the TYPO3 community.
