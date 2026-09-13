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
 * Hook object for the ViewFolder view. Implements postContent(),
 * which SeedDMS echoes at the very end of the folder page content
 * (views/bootstrap/class.ViewFolder.php), below the document/
 * subfolder listing -- a compact, non-intrusive spot.
 */
class SeedDMS_ExtFolderZip_ViewFolderHook {

	function postContent($view) { /* {{{ */
		$folder = method_exists($view, 'getParam') ? $view->getParam('folder') : null;
		if (!$folder) {
			return null;
		}
		$folderid = $folder->getID();

		// Relative link, matching the convention used throughout
		// SeedDMS core (e.g. "../out/out.ViewFolder.php?..."), since
		// this page itself is rendered from out/out.ViewFolder.php.
		$url = '../ext/folderzip/out/out.FolderZip.php?folderid=' . $folderid;
		$urlRecursive = $url . '&recursive=1';

		$html = '<div style="text-align:right; margin-top:12px; font-size:0.9em;">';
		$html .= '<a href="' . $url . '">Als ZIP herunterladen</a>';
		$html .= ' &middot; ';
		$html .= '<a href="' . $urlRecursive . '" onclick="return confirm(\'Wirklich alle Dokumente inkl. Unterordner als ZIP herunterladen? Bei großen Ordnerbäumen kann das lange dauern oder abgebrochen werden.\');">inkl. Unterordner</a>';
		$html .= '</div>';

		return $html;
	} /* }}} */
}
