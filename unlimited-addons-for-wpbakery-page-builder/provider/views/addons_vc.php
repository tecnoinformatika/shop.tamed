<?php

// no direct access
defined('UNLIMITED_ADDONS_INC') or die;

class UniteCreatorAddonsProviderView{
	
	protected $showButtons = true;
	protected $showHeader = true;
	
	
	
	/**
	 * addons view provider
	 */
	public function __construct(){
		$headerTitle = __("Manage Addons for WPBakery Page Builder", UNLIMITED_ADDONS_TEXTDOMAIN);
		
		$objManager = new UniteCreatorManagerAddons();
		$objManager->setAddonType("vc", "Visual Composer");
		
		require HelperUC::getPathTemplate("addons");
		
	}
	
}

new UniteCreatorAddonsProviderView();
