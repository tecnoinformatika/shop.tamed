<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorAddonDefaultsView{
	
	protected $showToolbar = true;
	protected $showHeader = true;
	protected $addon;
	protected $addonID;
	protected $isPreviewMode;	
	protected $isDataExists;
	
	
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
		
		$headerTitle = __("Addon Defaults",ADDONLIBRARY_TEXTDOMAIN);
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
	 * get options
	 */
	private function getOptions($addon){
		
		$pathAssets = $addon->getPathAssets();
		
		$options = array();
		$options["path_assets"] = $pathAssets;
		
		return($options);
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
		
		$arrOptions = $this->getOptions($addon);
		
		
		//init addon config
		$addonConfig = new UniteCreatorAddonConfig();
		$addonConfig->setStartAddon($addon);
		
		$this->isDataExists = $addon->isDefaultDataExists();
		
		$isPreviewMode = UniteFunctionsUC::getGetVar("preview","",UniteFunctionsUC::SANITIZE_KEY);
		$isPreviewMode = UniteFunctionsUC::strToBool($isPreviewMode);
		
		$addonConfig->setSourceAddon();
		$addonConfig->startWithPreview($isPreviewMode);
		
		$this->isPreviewMode = $isPreviewMode;
		
		require HelperUC::getPathTemplate("addon_defaults");
				
	}
	
}


$pathProviderAddon = GlobalsUC::$pathProvider."views/addon_defaults.php";

if(file_exists($pathProviderAddon) == true){
	require_once $pathProviderAddon;
	new UniteCreatorAddonDefaultsViewProvider();
}
else{
	new UniteCreatorAddonDefaultsView();
}
