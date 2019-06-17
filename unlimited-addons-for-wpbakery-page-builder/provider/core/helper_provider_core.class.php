<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class HelperProviderCoreUC_VC{
	
	public static $pathCore;
	public static $urlCore;
	
	
	/**
	 * get general settings filepath
	 */
	public static function getGeneralSettingsFilepath($arrFilepaths){
		
		$filepath = self::$pathCore."settings/general_settings_vc.xml";
		
		$arrFilepaths[] = $filepath;
		
		return($arrFilepaths);
	}
	
	/**
	 * add constant data to addon output
	 */
	public static function addOutputConstantData($data){
		
		$data["uc_platform_title"] = "WPBakery Page Builder";
		$data["uc_platform"] = "vc";
						
		return($data);
	}
	
	
	/**
	 * global init
	 */
	public static function globalInit(){
		
		add_filter(UniteCreatorFilters::FILTER_GET_GENERAL_SETTINGS_FILEPATH ,array("HelperProviderCoreUC_VC","getGeneralSettingsFilepath"));
		add_filter(UniteCreatorFilters::FILTER_ADD_ADDON_OUTPUT_CONSTANT_DATA ,array("HelperProviderCoreUC_VC","addOutputConstantData"));
		
		//set path and url
		self::$pathCore = dirname(__FILE__)."/";
		self::$urlCore = HelperUC::pathToFullUrl(self::$pathCore);
		
	}
	
}