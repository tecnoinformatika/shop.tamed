<?php

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteProviderFrontUC{
	
	private static $t;
	const ACTION_FOOTER_SCRIPTS = "wp_print_footer_scripts";
	const ACTION_AFTER_SETUP_THEME = "after_setup_theme";
	
	
	/**
	 *
	 * add some wordpress action
	 */
	protected static function addAction($action,$eventFunction){
	
		add_action( $action, array(self::$t, $eventFunction) );
	}
	
	
	/**
	 * add shortcodes of all active addons
	 */
	private function addShortcodes(){
		$objAddons = new UniteCreatorAddons();
		$arrAddons = $objAddons->getArrAddons();
		foreach($arrAddons as $addon){
			$shortcode = $addon->getName();
			UniteFunctionsWPUC::addShortcode($shortcode, "uc_run_shortcode");
		}
		
	}
	
	
	
	/**
	 * check and disable wp filters
	 */
	private function disableWpFilters(){
		
		//disable filters
		$disableFilters = HelperUC::getGeneralSetting("disable_autop_filters");
		$disableFilters = UniteFunctionsUC::strToBool($disableFilters);
		
		if($disableFilters == true){
			remove_filter( 'the_content', 'wpautop' );
		}
		
	}
	
	
	/**
	 *
	 * the constructor
	 */
	public function __construct(){
		
		self::$t = $this;
				
		HelperProviderUC::globalInit();
		
		self::addAction(self::ACTION_FOOTER_SCRIPTS, "onPrintFooterScripts");
		
		$this->disableWpFilters();
		
		//$this->addShortcodes();
	}
	
	
	/**
	 * print footer scripts
	 */
	public static function onPrintFooterScripts(){
		
		HelperProviderUC::onPrintFooterScripts(true);
	
	}
	
	
		
}


?>