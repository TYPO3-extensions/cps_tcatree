<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Nicole Cordes <cordes@cps-it.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * This function displays a selector styled as tree
 * The original code is borrowed from the extension "News" (tt_news) author: Rupert Germann <rupi@gmx.li>
 *
 * @author	Nicole Cordes <cordes@cps-it.de>
 * @package TYPO3
 * @subpackage cps_tcatree
 */

require_once(t3lib_extMgm::extPath('cps_tcatree').'lib/class.tx_cpstcatree_treeview.php');

class tx_cpstcatree {
	var $PA = array();

	var $table;
	var $field;
	var $row;
	var $fieldConfig;
	var $itemFormElName;
	var $parentField;

	var $divObj;
	var $selectedItems = array();

	function init(&$PA) {


		$this->PA = &$PA;
		$this->itemFormElName = $this->PA['itemFormElName'];
		$this->table = $PA['table'];
		$this->row = $PA['row'];
		$this->fieldConfig = $PA['fieldConf']['config'];
		if (($this->table == 'tt_content') AND ($PA['field'] == 'pi_flexform')) {
			$ELNameArray = t3lib_div::trimExplode('[', $this->itemFormElName);
			$this->field = substr($ELNameArray[7], 0, -1);
			$this->fieldConfig['piFlexFormSheet'] = substr($ELNameArray[5], 0, -1);
			$this->fieldConfig['piFlexFormLang'] = substr($ELNameArray[6], 0, -1);
			$this->fieldConfig['piFlexFormValue'] = substr($ELNameArray[8], 0, -1);
		} else {
			$this->field = $PA['field'];
		}
		$this->setSelectedItems();
	}

	function getTree(&$PA, &$fobj) {

		$fobj->additionalCode_pre['tx_cpstcatree'] = '
<script src="'.t3lib_extMgm::extRelPath('cps_tcatree').'js/tx_cpstcatree.js" type="text/javascript"></script>';

		$this->init($PA);

    if (isset($this->fieldConfig['trueMaxItems'])) $this->fieldConfig['maxitems'] = $this->fieldConfig['trueMaxItems'];
		$maxitems = t3lib_div::intInRange($this->fieldConfig['maxitems'], 0, 2000000000, 1000);
		$minitems = t3lib_div::intInRange($this->fieldConfig['minitems'], 0);
		$size = t3lib_div::intInRange($this->fieldConfig['size'],0, 2000000000, 1);

		$selectedItems = $fobj->addSelectOptionsToItemArray($fobj->initItemArray($this->PA['fieldConf']), $this->PA['fieldConf'], $fobj->setTSconfig($this->table, $this->row), $this->field);
		$selectedItems = $fobj->addItems($selectedItems, $this->PA['fieldTSConfig']['addItems.']);

		$removeItems = t3lib_div::trimExplode(',', $this->PA['fieldTSConfig']['removeItems'], 1);

		foreach($selectedItems as $key => $item)	{
			if (in_array($item[1], $removeItems))	{
				unset($selectedItems[$key]);
			}
		}

		if (isset($this->PA['fieldTSConfig']['noMatchingValue_label'])) {
			$nMV_label = $GLOBALS['LANG']->sL($this->PA['fieldTSConfig']['noMatchingValue_label']);
		} else {
			$nMV_label = '[ '.$fobj->getLL('l_noMatchingValue').' ]';
		}
		$nMV_label = @sprintf($nMV_label, $this->PA['itemFormElValue']);

		$this->registerRequiredProperty('range', $this->itemFormElName, array($minitems, $maxitems, 'imgName' => $this->table.'_'.$this->row['uid'].'_'.$this->field), $fobj);

		$content .= '<input type="hidden" name="'.$this->itemFormElName.'_mul" value="'.($this->fieldConfig['multiple'] ? 1 : 0).'" />';

		if ($this->fieldConfig['foreign_table']) {
			$treeContent = '<span id="'.$this->table.'_'.$this->fieldConfig['foreign_table'].'_tree">'.$this->renderTree().'</span>';
			$thumbnails = '<div name="'.$this->itemFormElName.'_selTree" class="tree-div" style="position: relative; border: 1px solid #999; background: #fff; left: 0px; top: 0px; width: 350px; margin-bottom: 5px; padding: 0 10px 10px 0;">';
			$thumbnails .= $treeContent;
			$thumbnails .= '</div>';
		}

		$itemArray = t3lib_div::trimExplode(',', $this->PA['itemFormElValue'], 1);
		foreach($itemArray as $key => $item) {
			$item = explode('|', $item, 2);
			$evalValue = rawurldecode($item[0]);
			if ((in_array($evalValue, $removeItems)) AND (!$this->PA['fieldTSConfig']['disableNoMatchingValueElement']))	{
				$item[1] = $nMV_label;
			}
			$item[1] = rawurldecode($item[1]);
			$itemArray[$key] = implode('|', $item);
		}

		$params = array(
			'size' => $size,
			'autoSizeMax' => $this->fieldConfig['autoSizeMax'],
			'style' => ' style="width: 200px;"',
			'dontShowMoveIcons' => ($maxitems < 2),
			'maxitems' => $maxitems,
			'info' => '',
			'headers' => array(
				'selector' => $fobj->getLL('l_selected').':<br />',
				'items' => $fobj->getLL('l_items').':<br />'
			),
			'noBrowser' => 1,
			'thumbnails' => $thumbnails
		);
		$content .= $fobj->dbFileIcons($this->itemFormElName, '', '', $itemArray, '', $params, $this->PA['onFocus']);

		$altItem = '<input type="hidden" name="'.$this->itemFormElName.'" value="'.htmlspecialchars($this->PA['itemFormElValue']).'" />';
		$content = $fobj->renderWizards(array($content, $altItem), $this->fieldConfig['wizards'], $this->table, $this->row, $this->field, $this->PA, $this->itemFormElName, array());

		if ((in_array('required', t3lib_div::trimExplode(',', $this->fieldConfig['eval'], 1))) AND ($this->NA_Items)) {
			$this->registerRequiredProperty(
				'range',
				'data['.$this->table.']['.$this->row['uid'].'][noDisallowedCategories]',
				array(1, 1, 'imgName'=>$this->table.'_'.$this->row['uid'].'_noDisallowedCategories'),
				$fobj);
			$content .= '<input type="hidden" name="data['.$this->table.']['.$this->row['uid'].'][noDisallowedCategories]" value="'.($this->NA_Items ? '' : '1').'" />';
		}

		return $content;
	}

	function ajaxExpandCollapse($params, &$ajaxObj) {

		$this->table = trim(t3lib_div::_GP('tceFormsTable'));
		$this->field = trim(t3lib_div::_GP('tceFormsField'));
		$this->recID = trim(t3lib_div::_GP('recID'));

		if (intval($this->recID) == $this->recID) {
			$this->row = t3lib_BEfunc::getRecord($this->table, $this->recID);
		}
		if (is_array($this->row)) {

			t3lib_div::loadTCA($this->table);
			if ($this->table == 'tt_content') {
				$this->field = t3lib_div::trimExplode(',', $this->field, 1);

				$flexFormArray = t3lib_BEfunc::getFlexFormDS($GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config'], $this->row, $this->table);
				$this->fieldConfig = $flexFormArray['sheets'][$this->field[1]]['ROOT']['el'][$this->field[0]]['TCEforms']['config'];
				$this->PA['itemFormElName'] = 'data['.$this->table.']['.$this->recID.'][pi_flexform][data]['.$this->field[1].']['.$this->field[2].']['.$this->field[0].']['.$this->field[3].']';
				$this->fieldConfig['piFlexFormSheet'] = $this->field[1];
				$this->fieldConfig['piFlexFormLang'] = $this->field[2];
				$this->fieldConfig['piFlexFormValue'] = $this->field[3];
				$this->field = $this->field[0];
			} else {
				$this->fieldConfig = $GLOBALS['TCA'][$this->table]['columns'][$this->field]['config'];
				$this->PA['itemFormElName'] = 'data['.$this->table.']['.$this->recID.']['.$this->field.']';
			}
			$this->itemFormElName = $this->PA['itemFormElName'];

			if (isset($this->fieldConfig['MM'])) {
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign', $this->fieldConfig['MM'], 'uid_local='.$this->row['uid']);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$this->selectedItems[] = $row['uid_foreign'];
				}
			} else {
				$this->setSelectedItems();
			}
		} else {
			$this->row = array('uid' => $this->recID);
		}

		$tree = $this->renderTree();

		$ajaxObj->addContent('tree', $tree);
	}

	function getItemRootline () {
		$selectedItemsArrayParents = array();
		foreach($this->selectedItems as $item) {
			$uid = $item;
			$loopCheck = 100;
			$rootLine = array();
			while (($uid != 0) AND ($loopCheck > 0))	{
				$row = t3lib_BEfunc::getRecord($this->fieldConfig['foreign_table'], $uid, $this->parentField);
				if ((is_array($row)) AND ($row[$this->parentField] > 0))	{
					$uid = $row[$this->parentField];
					$rootLine[] = $uid;
				} else {
					$uid = 0;
				}
				$loopCheck--;
			}
			$selectedItemsArrayParents[$item] = $rootLine;
		}
		return $selectedItemsArrayParents;
	}

	function registerNestedElement($itemName, &$fobj) {
		$dynNestedStack = $fobj->getDynNestedStack();
		$match = array();
		if ((count($dynNestedStack)) AND (preg_match('/^(.+\])\[(\w+)\]$/', $itemName, $match))) {
			array_shift($match);
			$fobj->requiredNested[$itemName] = array('parts' => $match, 'level' => $dynNestedStack);
		}
	}

	function registerRequiredProperty($type, $name, $value, &$fobj) {
		if (($type == 'field') AND (is_string($value))) {
			$fobj->requiredFields[$name] = $value;
			$itemName = $value;
		} elseif (($type == 'range') AND (is_array($value))) {
			$fobj->requiredElements[$name] = $value;
			$itemName = $name;
		}
		$this->registerNestedElement($itemName, $fobj);
	}

	function renderTree() {
		t3lib_div::loadTCA($this->fieldConfig['foreign_table']);
		$orderBy = $this->fieldConfig['foreign_table'].'.'.(($GLOBALS['TCA'][$this->fieldConfig['foreign_table']]['ctrl']['sortby']) ? $GLOBALS['TCA'][$this->fieldConfig['foreign_table']]['ctrl']['sortby'] : substr($GLOBALS['TCA'][$this->fieldConfig['foreign_table']]['ctrl']['default_sortby'], 9));
		$this->parentField = $GLOBALS['TCA'][$this->fieldConfig['foreign_table']]['ctrl']['treeParentField'];
		if (!$this->parentField) $this->parentField = 'pid';

		$treeViewObj = t3lib_div::makeInstance('tx_cpstcatree_treeview');
		$treeViewObj->thisScript = 'class.tx_cpstcatree.php';
		$treeViewObj->title = $GLOBALS['LANG']->sL($GLOBALS['TCA'][$this->fieldConfig['foreign_table']]['ctrl']['title']);
		$treeViewObj->treeName = $this->table.'_'.$this->field.'_tree';
		$treeViewObj->table = $this->fieldConfig['foreign_table'];
		$treeViewObj->parentField = $this->parentField;
		//$treeViewObj->fieldArray = array('uid', 'title', 'hidden');
		$treeViewObj->fieldArray = array('*');
		$treeViewObj->tceFormsTable = $this->table;
		if ($this->table == 'tt_content') {
			$treeViewObj->tceFormsField = $this->field.','.$this->fieldConfig['piFlexFormSheet'].','.$this->fieldConfig['piFlexFormLang'].','.$this->fieldConfig['piFlexFormValue'];
		} else {
			$treeViewObj->tceFormsField = $this->field;
		}
		$treeViewObj->tceFormsRecID = $this->row['uid'];
		$treeViewObj->ext_IconMode = '0';
		$treeViewObj->treeView = $this->fieldConfig['treeView'];
		$treeViewObj->expandable = $this->fieldConfig['expandable'];
		$treeViewObj->expandFirst = $this->fieldConfig['expandFirst'];
		$treeViewObj->expandAll = $this->fieldConfig['expandAll'];
		$treeViewObj->init('', $orderBy);

		$treeViewObj->TCEforms_itemFormElName = $this->itemFormElName;
		if ($this->table == $this->fieldConfig['foreign_table']) {
			$treeViewObj->TCEforms_nonSelectableItemsArray[] = $this->row['uid'];
		}
		$treeViewObj->TCEforms_selectedItemsArray = $this->selectedItems;
		$treeViewObj->selectedItemsArrayParents = $this->getItemRootline();

		$treeContent = $treeViewObj->getBrowsableTree();

		return $treeContent;
	}

	function setSelectedItems() {
		$selectedItems = array();

		if (isset($this->row[$this->field])) {
			$selectedItems = t3lib_div::trimExplode(',', $this->row[$this->field], 1);
		} else {
			if (($this->table == 'tt_content') AND ($this->row['pi_flexform'])) {
				$xmlArray = t3lib_div::xml2array($this->row['pi_flexform']);
				$selectedItems = t3lib_div::trimExplode(',', $xmlArray['data'][$this->piFlexForm['sheet']][$this->piFlexForm['lang']][$this->field][$this->piFlexForm['value']], 1);
			}
		}

		foreach($selectedItems as $item) {
			$item = t3lib_div::trimExplode('|', $item, 1);
			$this->selectedItems[] = $item[0];
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_tcatree/class.tx_cpstcatree.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cps_tcatree/class.tx_cpstcatree.php']);
}
?>