<?php

class HelperProviderUC{
	
	
	/**
	 * modify memory limit setting
	 */
	private static function modifyGeneralSettings_memoryLimit($objSettings){
		
		$memoryLimit = ini_get('memory_limit');
		$htmlLimit = "<b> {$memoryLimit} </b>";
		
		$setting = $objSettings->getSettingByName("memory_limit_text");
		if(empty($setting))
			UniteFunctionsUC::throwError("Must be memory limit troubleshooter setting");
		
		$setting["text"] = str_replace("[memory_limit]", $htmlLimit, $setting["text"]);
		$objSettings->updateArrSettingByName("memory_limit_text", $setting);
		
		return($objSettings);
	}
	
	
	/**
	 * modify general settings
	 */
	private static function modifyGeneralSettings(UniteSettingsUC $objSettings){
		
		//update memory limit
		$objSettings = self::modifyGeneralSettings_memoryLimit($objSettings);
		
		return($objSettings);
	}
	
	
	/**
	 * set general settings
	 */
	public static function setGeneralSettings(UniteCreatorSettings $objSettings){
		
		//add general settings
		$filepathGeneral = GlobalsUC::$pathProvider."settings/general_settings.xml";
		UniteFunctionsUC::validateFilepath($filepathGeneral, "Provider general settings");
		$objSettings->addFromXmlFile($filepathGeneral);
		
		
		//add platform related settings
		$arrSettingsFilepaths = array();
		$arrSettingsFilepaths = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_GET_GENERAL_SETTINGS_FILEPATH, $arrSettingsFilepaths);
		
		if(empty($arrSettingsFilepaths))
			return($objSettings);
		
		foreach($arrSettingsFilepaths as $filepath){
			UniteFunctionsUC::validateFilepath($filepath, "plugin related settings xml file");
			$objSettings->addFromXmlFile($filepath);
		}
		
		$objSettings = self::modifyGeneralSettings($objSettings);
		
		return($objSettings);
	}
	
	
	/**
	 * check if layout editor plugin exists, or exists addons for it
	 */
	public static function isLayoutEditorExists(){
		
		$classExists = class_exists("LayoutEditorGlobals");
		if($classExists == true)
			return(true);
	
		return(false);
	}
	
	
	/**
	 * register widgets 
	 */
	public static function registerWidgets(){
		
		$isLayouEditorExists = self::isLayoutEditorExists();
		
		if($isLayouEditorExists == true){
			
			register_widget("AddonLibrary_WidgetLayout");
		}
		
	}
	
	/**
	 * global init function that common to the admin and front
	 */
	public static function globalInit(){
		
		add_filter(UniteCreatorFilters::FILTER_MODIFY_GENERAL_SETTINGS, array("HelperProviderUC", "setGeneralSettings") );
		
		//create_function('', 'return register_widget("AddonLibrary_WidgetLayout");'));
		
		//register the addon library widget
		//add_action('widgets_init', array("HelperProviderUC","registerWidgets"));
		
		//dmp("init");exit();
	}
	
	
	/**
	 * on plugins loaded call plugin
	 */
	public static function onPluginsLoadedCallPlugins(){
		
		do_action("addon_library_register_plugins");
		
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_EDIT_GLOBALS);
		
	}
	
	
	/**
	 * register plugins
	 */
	public static function registerPlugins(){
		
		add_action("plugins_loaded",array("HelperProviderUC","onPluginsLoadedCallPlugins"));
		
	}
	
	
	/**
	 * print custom scripts
	 */
	public static function onPrintFooterScripts($isFront = false){
		
		if($isFront == false){
			
			//print inline html
			$arrHtml = UniteProviderFunctionsUC::getInlineHtml();
			if(!empty($arrHtml)){
				foreach($arrHtml as $html){
					echo $html;
				}
			}
			
		}
			
		//print custom script
		$arrScrips = UniteProviderFunctionsUC::getCustomScripts();
		if(!empty($arrScrips)){
			echo "\n<!--   Unlimited Addons Scripts  --> \n";
			
			echo "<script type='text/javascript'>\n";
			foreach ($arrScrips as $script){
				echo $script."\n";
			}
			echo "</script>";
		}
	
		$arrStyles = UniteProviderFunctionsUC::getCustomStyles();
		if(!empty($arrStyles)){
			echo "\n<!--   Unlimited Addons Styles  --> \n";
			
			echo "<style type='text/css'>";
	
			foreach ($arrStyles as $style){
				echo $style."\n";
			}
	
			echo "</style>";
		}
	
	}
	
	
}