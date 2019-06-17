<?php

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteProviderCoreFrontUC_VC extends UniteProviderFrontUC{
	
	
	
	/**
	 * on before init
	 */
	public static function onBeforeVCInit(){
		
		UniteVcIntegrateUC::integrateVisualComposer();
	}
	
	
	/**
	 *
	 * the constructor
	 */
	public function __construct(){
		
		HelperProviderCoreUC_VC::globalInit();
		
		parent::__construct();
				
		self::addAction("vc_after_init", "onBeforeVCInit");
				
	}
	
	
}
