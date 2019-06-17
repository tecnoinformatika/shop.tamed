<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


	class UniteCreatorAdmin extends UniteBaseAdminClassUC{
		
		const DEFAULT_VIEW = "addons";
		
		private static $isScriptsIncluded_settingsBase = false;
		
		
		/**
		 * 
		 * the constructor
		 */
		public function __construct(){
						
			parent::__construct();
		}
		
		
		/**
		 * 
		 * init all actions
		 */
		protected function init(){
			
			//some init content
		}

		
		/**
		 * add must scripts for any view
		 */
		private static function addMustScripts($specialSettings = ""){
			
			UniteProviderFunctionsUC::addScriptsFramework($specialSettings);
			
			HelperUC::addScript("farbtastic","unite-farbtastic","js/farbtastic");
			HelperUC::addStyle("farbtastic","unite-farbtastic","js/farbtastic");
			
			HelperUC::addScript("jquery.tipsy","tipsy-js");
			
			//font awsome
			HelperUC::addStyle("font-awesome.min","font-awesome","css/font-awsome/css");
			
			HelperUC::addScript("settings", "unitecreator_settings");
			HelperUC::addScript("admin","unitecreator_admin");
			HelperUC::addStyle("admin","unitecreator_admin_css");
						
			HelperUC::addScriptAbsoluteUrl(GlobalsUC::$url_provider."assets/provider_admin.js", "provider_admin_js");
			
		}
		
		
		/**
		 * 
		 * a must function. adds scripts on the page
		 * add all page scripts and styles here.
		 * pelase don't remove this function
		 * common scripts even if the plugin not load, use this function only if no choise.
		 */
		public static function onAddScripts(){
			
			self::addMustScripts();
			
			HelperUC::addScript("unitecreator_assets", "unitecreator_assets");
			
			HelperUC::addStyle("unitecreator_styles","unitecreator_css","css");
			
			//include dropzone
			switch (self::$view){
				case GlobalsUC::VIEW_EDIT_ADDON:
				case GlobalsUC::VIEW_ASSETS:
					
					HelperUC::addScript("jquery.dialogextend.min", "jquery-ui-dialogextend","js/dialog_extend", true);
					
					HelperUC::addScript("dropzone", "dropzone_js","js/dropzone");
					HelperUC::addStyle("dropzone", "dropzone_css","js/dropzone");
					
					//include codemirror
					HelperUC::addScript("codemirror.min", "codemirror_js","js/codemirror");
					HelperUC::addScript("htmlmixed", "codemirror_html","js/codemirror/mode/htmlmixed");
					HelperUC::addScript("css", "codemirror_cssjs","js/codemirror/mode/css");
					HelperUC::addScript("javascript", "codemirror_javascript","js/codemirror/mode/javascript");
					HelperUC::addScript("xml", "codemirror_xml","js/codemirror/mode/xml");
					
					HelperUC::addStyle("codemirror", "codemirror_css","js/codemirror");
					
					HelperUC::addScript("unitecreator_includes", "unitecreator_includes");
					HelperUC::addScript("unitecreator_params_dialog", "unitecreator_params_dialog");
					HelperUC::addScript("unitecreator_params_editor", "unitecreator_params_editor");
					HelperUC::addScript("unitecreator_params_panel", "unitecreator_params_panel");
					HelperUC::addScript("unitecreator_variables", "unitecreator_variables");					
					HelperUC::addScript("unitecreator_admin", "unitecreator_view_admin");
				break;
				case GlobalsUC::VIEW_TEST_ADDON:
					
					UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_ITEMS_INLINE);
					
					HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
					HelperUC::addStyle("unitecreator_admin_front","unitecreator_admin_front_css");
					HelperUC::addScript("unitecreator_testaddon_admin");
					HelperUC::addStyle("unitecreator_browser","unitecreator_browser_css");
					
				break;
				case GlobalsUC::VIEW_ADDON_DEFAULTS:
					UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_ITEMS_INLINE);
					
					HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
					HelperUC::addStyle("unitecreator_admin_front","unitecreator_admin_front_css");
					HelperUC::addScript("unitecreator_addondefaults_admin");
					HelperUC::addStyle("unitecreator_browser","unitecreator_browser_css");
					
				break;
				case GlobalsUC::VIEW_SETTINGS:
				case GlobalsUC::VIEW_LAYOUTS_SETTINGS:
					HelperUC::addScript("unitecreator_admin_generalsettings", "unitecreator_admin_generalsettings");
				break;
				case GlobalsUC::VIEW_LAYOUTS_LIST:
					
					self::onAddScriptsBrowser();
					
					HelperUC::addScript("unitecreator_admin_layouts", "unitecreator_admin_layouts");
					
				break;
				case GlobalsUC::VIEW_LAYOUT_IFRAME:
					self::onAddScriptsGridEditor();
				break;
				case GlobalsUC::VIEW_LAYOUT:
					
					self::onAddScriptsGridEditor(true);
					
				break;
				case GlobalsUC::VIEW_TEMPLATE:
					
					UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_PAGES);
					
					HelperUC::addScript("unitecreator_admin_template", "unitecreator_admin_template");
				
				break;
				default:
				case GlobalsUC::VIEW_ADDONS_LIST:
					UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_ADDONS);
				break;
				case "sort_pages":
					UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_PAGES);
				break;
			}

			//provider admin css always comes to end
			HelperUC::addStyleAbsoluteUrl(GlobalsUC::$url_provider."assets/provider_admin.css", "provider_admin_css");
			
			UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_ADD_ADMIN_SCRIPTS);
			
		}
		
		
		/**
		 * add settings base options
		 */
		public static function addScripts_settingsBase($specialSettings = ""){
			
			//include those scripts only once
			if(self::$isScriptsIncluded_settingsBase == true)
				return(false);
			
			self::addMustScripts($specialSettings);
			
			HelperUC::addStyle("unitecreator_admin_front","unitecreator_admin_front_css");
			
			UniteCreatorManager::putScriptsIncludes(UniteCreatorManager::TYPE_ITEMS_INLINE);
			
			self::$isScriptsIncluded_settingsBase = true;
		}
		
		
		/**
		 * add scripts only for the browser
		 */
		public static function onAddScriptsBrowser(){
			
			self::addScripts_settingsBase();
			
			HelperUC::addStyle("unitecreator_browser","unitecreator_browser_css");
			HelperUC::addScript("unitecreator_browser", "unitecreator_browser");			
			HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
			
		}
		
		
		/**
		 * add grid editor scripts. include the browser scripts in them
		 */
		public static function onAddScriptsGridEditor($isOuter = false){
			
			if($isOuter == true){
				
				HelperUC::addScript("unitecreator_page_builder", "unitecreator_page_builder");
			}
			
			self::onAddScriptsBrowser();
			
			HelperUC::putAnimationIncludes(true);
						
			//HelperUC::addScript("rasterizeHTML.allinone", "rasterizeHTML.allinone","js/html2canvas");
			
			HelperUC::addScript("unitecreator_grid_builder", "unitecreator_grid_editor");
			HelperUC::addScript("unitecreator_grid_actions_panel", "unitecreator_grid_actions_panel");
			HelperUC::addScript("unitecreator_grid_panel", "unitecreator_grid_panel");
			HelperUC::addScript("unitecreator_grid_objects", "unitecreator_grid_objects");
			
		}
		
		
		/**
		 * 
		 * admin main page function.
		 */
		public function adminPages(){
			
			if(self::$view != GlobalsUC::VIEW_MEDIA_SELECT)
				self::setMasterView("master_view");
			
			self::requireView(self::$view);
			
		}
		
		
		
		/**
		 * 
		 * onAjax action handler
		 */
		public static function onAjaxAction(){
			
			$objActions = new UniteCreatorActions();
			$objActions->onAjaxAction();
			
		}
		
	}
	
	
?>