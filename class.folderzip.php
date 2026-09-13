<?php
/***************************************************************
* Extension: folderzip
* Adds "Download as ZIP" links below a folder's document list.
***************************************************************/

/**
 * init() runs on every request once the extension is enabled.
 * Registers a hook for SeedDMS's ViewFolder view.
 */
class SeedDMS_ExtFolderZip extends SeedDMS_ExtBase {
	function init() { /* {{{ */
		$GLOBALS['SEEDDMS_HOOKS']['view']['viewFolder'][] = new SeedDMS_ExtFolderZip_ViewFolderHook();
	} /* }}} */
}

/**
 * Hook object for the ViewFolder view.
 * - additionalFolderInfos(): adds a "Download" row to the
 *   Informationen table of the CURRENTLY OPEN folder (top of the
 *   page, next to ID/Besitzer/Erstellt am/...). Plain, non-recursive
 *   download: only this folder's own documents, no subfolders, no
 *   wrapping top-level directory -- same behaviour as the very first
 *   version of this extension.
 * - folderRowAction(): adds a download icon to the action column of
 *   each SUBFOLDER row in a folder listing (next to delete/edit/
 *   clipboard). This one IS recursive and wraps the result in a
 *   top-level directory named after that subfolder, since it's used
 *   to grab a whole subfolder without opening it first.
 */
class SeedDMS_ExtFolderZip_ViewFolderHook {

	function additionalFolderInfos($view, $folder) { /* {{{ */
		// Relative link, matching the convention used throughout
		// SeedDMS core (e.g. "../out/out.ViewFolder.php?..."), since
		// this page itself is rendered from out/out.ViewFolder.php.
		// Plain, non-recursive download: only this folder's own
		// documents, no subfolders, no wrapping directory.
		$url = '../ext/folderzip/out/out.FolderZip.php?folderid=' . $folder->getID();
		$link = '<a href="' . $url . '" title="Als ZIP herunterladen"><i class="fa fa-download"></i> Dokumente als ZIP herunterladen</a>';

		return array(
			array('Download', $link),
		);
	} /* }}} */

	function folderRowAction($view, $subFolder, $actions) { /* {{{ */
		// Recursive + wrapped: grabs the whole subfolder (incl. its
		// own subfolders), nested inside a top-level directory named
		// after it in the resulting ZIP.
		$url = '../ext/folderzip/out/out.FolderZip.php?folderid=' . $subFolder->getID() . '&recursive=1&wrap=1';
		$actions['folderzip_download'] = array(
			'link' => $url,
			'title' => 'Als ZIP herunterladen (inkl. Unterordner)',
			'label' => 'Als ZIP herunterladen (inkl. Unterordner)',
			'icon' => 'download',
			'confirmmsg' => 'Wirklich alle Dokumente inkl. Unterordner als ZIP herunterladen? Bei großen Ordnerbäumen kann das lange dauern oder abgebrochen werden.',
		);
		return $actions;
	} /* }}} */
}
