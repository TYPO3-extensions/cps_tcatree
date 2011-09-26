<?php

########################################################################
# Extension Manager/Repository config file for ext "cps_tcatree".
#
# Auto generated 26-09-2011 09:27
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Record tree for TCA',
	'description' => 'Adds a new type to tca configuration to display record lists (like tt_news)',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.1.6',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Nicole Cordes',
	'author_email' => 'cordes@cps-it.de',
	'author_company' => 'CPS-IT GmbH (http://www.cps-it.de)',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-0.0.0',
			'typo3' => '4.1.0-0.0.0',
			'cps_devlib' => '0.0.1-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:9:"ChangeLog";s:4:"e7aa";s:23:"class.tx_cpstcatree.php";s:4:"9878";s:12:"ext_icon.gif";s:4:"de80";s:17:"ext_localconf.php";s:4:"9c63";s:14:"ext_tables.php";s:4:"2e85";s:17:"locallang_tca.xml";s:4:"1e49";s:14:"doc/manual.sxw";s:4:"3b40";s:19:"js/tx_cpstcatree.js";s:4:"4e88";s:36:"lib/class.tx_cpstcatree_treeview.php";s:4:"3ff8";}',
);

?>