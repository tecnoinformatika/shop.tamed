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
				
		$headerTitle = __("Manage Addons for Elementor Page Builder", UNLIMITED_ADDONS_TEXTDOMAIN);
		
		$objManager = new UniteCreatorManagerAddons();
		$objManager->setAddonType("elementor", "Elementor");
		
		require HelperUC::getPathTemplate("addons");
		
	}
	
}

new UniteCreatorAddonsProviderView();
