<?php

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteProviderCoreAdminUC_VC extends UniteProviderAdminUC{
	
	const ADDON_TYPE = "vc";
	
	/**
	 *
	 * the constructor
	 */
	public function __construct($mainFilepath){
		
		$this->textBuy = "Go Unlimited";
		$this->linkBuy = "https://codecanyon.net/item/unlimited-addons-mega-bundle-for-visual-composer/19602316?ref=unitecms&utm_source=wp&utm_campaign=gounlimited";
		$this->coreAddonType = GlobalsProviderUC::ADDONSTYPE_VC;
		$this->coreAddonsView = GlobalsProviderUC::VIEW_ADDONS_VC;
		$this->pluginTitle = "Unlimited Addons";
				
		HelperProviderCoreUC_VC::globalInit();
		
		parent::__construct($mainFilepath);
	}
	
	/**
	 * on before init
	 */
	public function onBeforeVCInit(){
		
		UniteVcIntegrateUC::integrateVisualComposer();
		
	}
	
	
	/**
	 * on add outside scripts
	 */
	public function onAddOutsideScripts(){
		
		parent::onAddOutsideScripts();
		
		UniteVcIntegrateUC::onAddOutsideScripts();
	}
	
	
	/**
	 * modify addons manager
	 */
	public function validateGeneralSettings($arrValues){
		
		$vcFolder = UniteFunctionsUC::getVal($arrValues, "vc_folder");
		UniteFunctionsUC::validateNotEmpty($vcFolder, "visual composer folder");
		
	}
	
	
	/**
	 * import package addons
	 */
	protected function importPackageAddons(){
		
		//install starter pack only if no addons installed
		$objAddons = new UniteCreatorAddons();
		$numAddons = $objAddons->getNumAddons(null, null, self::ADDON_TYPE);
		if($numAddons > 0)
			return(false);
		
		$pathAddons = HelperProviderCoreUC_VC::$pathCore."addons_install/";
		
		$isImported = false;
		if(is_dir($pathAddons)){
			$arrFiles = UniteFunctionsUC::getFileList($pathAddons);
			$isImported = !empty($arrFiles);
			$this->installAddonsFromPath($pathAddons);
		}
		
		$isImportedParent = parent::importPackageAddons();
		
		if($isImported == false)
			$isImported = $isImportedParent;
		
		return($isImported);
	}
	
	
	/**
	 * init
	 */
	protected function init(){
				
		parent::init();
		
		//$this->addAction("vc_before_init", "onBeforeVCInit");
		$this->addAction("vc_mapper_init_after", "onBeforeVCInit");
		
		$this->addAction(UniteCreatorFilters::ACTION_VALIDATE_GENERAL_SETTINGS, "validateGeneralSettings");
		
	}
	
	
	
}