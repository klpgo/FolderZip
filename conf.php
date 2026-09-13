<?php
/***************************************************************
* SeedDMS Extension: folderzip
*
* Downloads all documents of a folder (optionally including all
* subfolders, recursively) as a single ZIP archive.
*
* Place this whole "folderzip" directory below the "ext" directory
* of your SeedDMS installation, e.g.:
*
*   <seeddms-webroot>/ext/folderzip/conf.php
*   <seeddms-webroot>/ext/folderzip/class.folderzip.php
*   <seeddms-webroot>/ext/folderzip/out/out.FolderZip.php
*
* Then enable it in Admin Tools -> Extension Manager.
***************************************************************/

$EXT_CONF['folderzip'] = array(
	'title' => 'Folder as ZIP',
	'description' => 'Adds the ability to download all documents of a folder (optionally including subfolders) as a single ZIP archive.',
	'disable' => false,
	'version' => '1.1.0',
	'releasedate' => '2026-09-13',
	'author' => array(
		'name' => 'Klaus Gottschalk',
		'email' => 'klaus.gottschalk@kgem.de',
		'company' => '',
	),
	'config' => array(
		'maxfiles' => array(
			'title' => 'Max. Anzahl Dateien pro ZIP',
			'type' => 'input',
			'size' => 6,
		),
	),
	'constraints' => array(
		'depends' => array('php' => '7.2.0-', 'seeddms' => '5.1.0-'),
	),
	'icon' => 'icon.png',
	'changelog' => 'changelog.md',
	'changes' => array(
		'1.1.0' => array(
			'Download icon added to each subfolder row action list (list view of a parent folder)',
                        'Moved non-recursive link in folder to metadata list on top',
		),
		'1.0.0' => array(
			'Initial version',
			'Download all documents of a folder (optionally incl. subfolders) as ZIP',
			'Confirmation dialog for recursive download',
			'Configurable safety limit for max. number of files per ZIP',
		),
	),
	'class' => array(
		'file' => 'class.folderzip.php',
		'name' => 'SeedDMS_ExtFolderZip',
	),
);
