<?php
/***************************************************************
* Extension: folderzip
*
* Streams all documents of a folder (optionally including all
* subfolders) as a ZIP archive.
*
* Usage (relative to your SeedDMS webroot):
*
*   ext/folderzip/out/out.FolderZip.php?folderid=123
*   ext/folderzip/out/out.FolderZip.php?folderid=123&recursive=1
*   ext/folderzip/out/out.FolderZip.php?folderid=123&recursive=1&wrap=1
*
* recursive=1 also includes documents from all subfolders,
* preserving the folder structure inside the ZIP.
* wrap=1 additionally nests everything inside a top-level directory
* named after the folder itself, instead of placing its documents
* directly at the ZIP root.
*
* This follows the exact same bootstrap pattern used by SeedDMS's
* own out/out.*.php scripts: including inc.ClassUI.php sets up the
* session, database connection, current $user and $dms object, so
* this script only runs for an authenticated, permitted user.
***************************************************************/

// Path to the core bootstrap files. out/out.*.php scripts inside
// SeedDMS itself live one directory below the webroot (webroot/out/)
// and use this exact include chain. This script lives two
// directories further down (webroot/ext/folderzip/out/), so only
// the FIRST include needs a path adjusted for that extra depth --
// inc.Settings.php changes PHP's working directory to the SeedDMS
// webroot internally, so every subsequent include below uses the
// exact same webroot-relative paths as the core scripts do.
if (!isset($settings))
	require_once("../../../inc/inc.Settings.php");
require_once("inc/inc.Utils.php");
require_once("inc/inc.LogInit.php");
require_once("inc/inc.Language.php");
require_once("inc/inc.Init.php");
require_once("inc/inc.Extension.php");
require_once("inc/inc.DBInit.php");
require_once("inc/inc.Authentication.php");
require_once("inc/inc.ClassUI.php");

if (!isset($_GET["folderid"]) || !is_numeric($_GET["folderid"]) || intval($_GET["folderid"]) < 1) {
	UI::exitError(
		getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),
		getMLText("invalid_folder_id")
	);
}
$folderid = intval($_GET["folderid"]);

$folder = $dms->getFolder($folderid);
if (!is_object($folder)) {
	UI::exitError(
		getMLText("folder_title", array("foldername" => getMLText("invalid_folder_id"))),
		getMLText("invalid_folder_id")
	);
}

// Same permission check the core folder view uses.
if ($folder->getAccessMode($user) < M_READ) {
	UI::exitError(
		getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),
		getMLText("access_denied")
	);
}

if (!class_exists('ZipArchive')) {
	UI::exitError(
		getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),
		"The PHP zip extension (ZipArchive) is not installed on this server."
	);
}

$recursive = isset($_GET['recursive']) && $_GET['recursive'] == '1';
$wrap = isset($_GET['wrap']) && $_GET['wrap'] == '1';

// Safety limit: stops runaway downloads if someone accidentally
// triggers a recursive download on a huge folder tree (e.g. the
// DMS root). Configurable in Admin Tools -> Extension Manager ->
// folderzip -> "Max. Anzahl Dateien pro ZIP"; falls back to 500.
$folderzipMaxFiles = 500;
if (isset($settings->_extensions['folderzip']['maxfiles']) && is_numeric($settings->_extensions['folderzip']['maxfiles']) && $settings->_extensions['folderzip']['maxfiles'] > 0) {
	$folderzipMaxFiles = (int) $settings->_extensions['folderzip']['maxfiles'];
}
define('FOLDERZIP_MAX_FILES', $folderzipMaxFiles);

$tmpfile = tempnam(sys_get_temp_dir(), 'sdmszip_');
$zip = new ZipArchive();
if ($zip->open($tmpfile, ZipArchive::OVERWRITE) !== true) {
	UI::exitError(
		getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),
		"Could not create zip archive."
	);
}

/**
 * Thrown internally when the file limit is exceeded, so the
 * recursive traversal can be aborted from any nesting depth.
 */
class FolderZipLimitExceeded extends Exception {}

/**
 * Recursively adds all documents of $folder (and, if $recursive is
 * true, of every subfolder) to the given ZipArchive. Only documents
 * the current user is allowed to read are included, and only the
 * latest version of each document.
 *
 * @param SeedDMS_Core_Folder $folder    folder to process
 * @param SeedDMS_Core_User   $user      current user (for access checks)
 * @param ZipArchive          $zip       target zip archive
 * @param bool                $recursive whether to descend into subfolders
 * @param string              $basepath  path prefix inside the zip (used for recursion)
 */
function folderzip_add_folder($folder, $user, ZipArchive $zip, $recursive, $settings, $basepath = '') {
	foreach ($folder->getDocuments() as $document) {
		if ($zip->numFiles >= FOLDERZIP_MAX_FILES) {
			throw new FolderZipLimitExceeded();
		}
		if ($document->getAccessMode($user) < M_READ) {
			continue;
		}
		$content = $document->getLatestContent();
		if (!$content) {
			continue;
		}
		// getPath() returns a path relative to the DMS content directory,
		// which itself is contentDir + contentOffsetDir combined (see
		// inc.DBInit.php: "new SeedDMS_Core_DMS($db, $settings->_contentDir.$settings->_contentOffsetDir)").
		$filepath = rtrim($settings->_contentDir, '/') . '/' . trim($settings->_contentOffsetDir, '/') . '/' . ltrim($content->getPath(), '/');
		if ($filepath && is_readable($filepath)) {
			// Build a readable file name: document name + original extension.
			$ext = $content->getFileType();
			$name = $document->getName();
			if ($ext && !preg_match('/\.' . preg_quote(ltrim($ext, '.'), '/') . '$/i', $name)) {
				$name .= '.' . ltrim($ext, '.');
			}
			$entryname = $basepath . folderzip_sanitize_name($name);
			// Avoid collisions if two documents end up with the same name.
			$entryname = folderzip_unique_entry($zip, $entryname);
			$zip->addFile($filepath, $entryname);
		}
	}

	if ($recursive) {
		foreach ($folder->getSubFolders() as $subfolder) {
			if ($subfolder->getAccessMode($user) < M_READ) {
				continue;
			}
			$subpath = $basepath . folderzip_sanitize_name($subfolder->getName()) . '/';
			folderzip_add_folder($subfolder, $user, $zip, true, $settings, $subpath);
		}
	}
}

/**
 * Strips characters that are problematic inside zip entry names /
 * on common filesystems.
 */
function folderzip_sanitize_name($name) {
	return preg_replace('/[\/\\\\:*?"<>|]/', '_', $name);
}

/**
 * Returns $entryname unchanged if it does not yet exist in $zip,
 * otherwise appends a running counter before the extension until
 * a free name is found.
 */
function folderzip_unique_entry(ZipArchive $zip, $entryname) {
	if ($zip->locateName($entryname) === false) {
		return $entryname;
	}
	$pathinfo = pathinfo($entryname);
	$dir = isset($pathinfo['dirname']) && $pathinfo['dirname'] !== '.' ? $pathinfo['dirname'] . '/' : '';
	$filename = $pathinfo['filename'];
	$ext = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
	$i = 1;
	do {
		$candidate = $dir . $filename . ' (' . $i . ')' . $ext;
		$i++;
	} while ($zip->locateName($candidate) !== false);
	return $candidate;
}

$limitExceeded = false;
try {
	$initialBasepath = $wrap ? folderzip_sanitize_name($folder->getName()) . '/' : '';
	folderzip_add_folder($folder, $user, $zip, $recursive, $settings, $initialBasepath);
} catch (FolderZipLimitExceeded $e) {
	$limitExceeded = true;
}
$numfiles = $zip->numFiles;
$zip->close();

if ($limitExceeded) {
	unlink($tmpfile);
	UI::exitError(
		getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),
		"This folder (including subfolders) contains more than " . FOLDERZIP_MAX_FILES . " documents. "
		. "Please choose a smaller folder, or download without \"inkl. Unterordner\"."
	);
}

if ($numfiles === 0) {
	unlink($tmpfile);
	UI::exitError(
		getMLText("folder_title", array("foldername" => htmlspecialchars($folder->getName()))),
		"This folder does not contain any documents you have access to."
	);
}

$zipname = folderzip_sanitize_name($folder->getName());
if ($zipname === '') {
	$zipname = 'folder-' . $folderid;
}
$zipname .= '.zip';

// Make sure nothing else has written to the output buffer yet.
if (ob_get_level()) {
	ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipname . '"');
header('Content-Length: ' . filesize($tmpfile));
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

readfile($tmpfile);
unlink($tmpfile);
exit;
