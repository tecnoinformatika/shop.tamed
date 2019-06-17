<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorTestAddonView{
	
	protected $showToolbar = true;
	protected $showHeader = true;
	protected $addon;
	protected $addonID;
	protected $isPreviewMode;	
	protected $isTestData1;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->putHtml();
	}
	
	
	/**
	 * get header text
	 */
	protected function getHeader(){
		
		$addonTitle = $this->addon->getTitle();
		
		$headerTitle = __("Test Addon",ADDONLIBRARY_TEXTDOMAIN);
		$headerTitle .= " - ".$addonTitle;
		
		return($headerTitle);
	}
	
	/**
	 * put header html
	 */
	protected function putHeaderHtml(){
		
		$headerTitle = $this->getHeader();
		require HelperUC::getPathTemplate("header");
		
	}
	
	
	/**
	 * put html
	 */
	private function putHtml(){
		
		$addonID = UniteFunctionsUC::getGetVar("id","",UniteFunctionsUC::SANITIZE_ID);
		
		if(empty($addonID))
			UniteFunctionsUC::throwError("Addon ID not given");
		
		$this->addonID = $addonID;
		
		$addon = new UniteCreatorAddon();
		$addon->initByID($addonID);
		
		$this->addon = $addon;
		
		$objAddons = new UniteCreatorAddons();
		
		$isNeedHelperEditor = $objAddons->isHelperEditorNeeded($addon);
		
		
		$addonTitle = $addon->getTitle();
		
		$addonType = $addon->getType();
		
		$urlEditAddon = HelperUC::getViewUrl_EditAddon($addonID);
		
		$urlTestWithData = HelperUC::getViewUrl_TestAddon($addonID, "loaddata=test");
		
		//init addon config
		$addonConfig = new UniteCreatorAddonConfig();
		$addonConfig->setStartAddon($addon);
		
		$this->isTestData1 = $addon->isTestDataExists(1);
		
		//get addon data
		$addonData = null;
		$isLoadData = UniteFunctionsUC::getGetVar("loaddata","",UniteFunctionsUC::SANITIZE_NOTHING);
		
		if($isLoadData == "test" && $this->isTestData1 == true)
			$addon->setValuesFromTestData(1);
		
		$isPreviewMode = UniteFunctionsUC::getGetVar("preview","",UniteFunctionsUC::SANITIZE_KEY);
		$isPreviewMode = UniteFunctionsUC::strToBool($isPreviewMode);
		
		$addonConfig->startWithPreview($isPreviewMode);
		
		$this->isPreviewMode = $isPreviewMode;
		
		require HelperUC::getPathTemplate("test_addon");
				
	}
	
	
}


$pathProviderAddon = GlobalsUC::$pathProvider."views/test_addon.php";

if(file_exists($pathProviderAddon) == true){
	require_once $pathProviderAddon;
	new UniteCreatorTestAddonViewProvider();
}
else{
	new UniteCreatorTestAddonView();
}
