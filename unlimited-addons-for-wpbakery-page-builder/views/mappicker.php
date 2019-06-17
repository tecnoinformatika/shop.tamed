<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

	
	$filepathPickerObject = GlobalsUC::$pathViewsObjects."mappicker_view.class.php";
	require $filepathPickerObject;
	
	//input
	//$data = UniteFunctionsUC::getPostGetVariable("mapdata", "", UniteFunctionsUC::SANITIZE_NOTHING);
	$objView = new UniteCreatorMappickerView();
	
	/*
	if(!empty($data)){
		$arrData = UniteFunctionsUC::decodeContent($data);
		if(empty($arrData))
			UniteFunctionsUC::throwError("Wrong map data given");
		
		$objView->setData($arrData);
	}
	*/
	
	
	$objView->putHtml();

