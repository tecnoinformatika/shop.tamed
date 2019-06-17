<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

	class GlobalsUC{
		
		public static $inDev = false;
		
		const SHOW_TRACE = false;
		const SHOW_TRACE_FRONT = false;
		
		const ENABLE_TRANSLATIONS = false;
		
		const PLUGIN_TITLE = "Addon Library";
		const PLUGIN_NAME = "unitecreator";
		
		const TABLE_ADDONS_NAME = "addonlibrary_addons";
		const TABLE_LAYOUTS_NAME = "addonlibrary_layouts";
		const TABLE_CATEGORIES_NAME = "addonlibrary_categories";
		const TABLE_TEMPLATES_NAME = "addonlibrary_templates";
		
		const VIEW_ADDONS_LIST = "addons";
		const VIEW_EDIT_ADDON = "addon";
		const VIEW_ASSETS = "assets";
		const VIEW_SETTINGS = "settings";
		const VIEW_TEST_ADDON = "testaddon";
		const VIEW_ADDON_DEFAULTS = "addondefaults";
		const VIEW_MEDIA_SELECT = "mediaselect";
		const VIEW_LAYOUTS_LIST = "layouts";
		const VIEW_LAYOUT = "layout_outer";
		const VIEW_LAYOUT_IFRAME = "layout";
		const VIEW_LAYOUT_PREVIEW = "layout_preview";
		const VIEW_TEMPLATES_LIST = "templates";
		const VIEW_TEMPLATE = "template";
		const VIEW_TEMPLATE_PREVIEW = "template_preview";
		
		const VIEW_LAYOUTS_SETTINGS = "layouts_settings";
		
		const DEFAULT_JPG_QUALITY = 81;
		const THUMB_WIDTH = 300;
		const THUMB_WIDTH_LARGE = 700;
		
		const THUMB_SIZE_NORMAL = "size_normal";
		const THUMB_SIZE_LARGE = "size_large";
		
		const DIR_THUMBS = "unitecreator_thumbs";
		const DIR_THUMBS_ELFINDER = "elfinder_tmb";
		
		const DIR_THEME_ADDONS = "al_addons";
		
		const URL_API = "http://api.bloxbuilder.me/index.php";
		//const URL_API = "http://localhost/dev/blox_API/";
		
		const URL_BUY = "http://unitecms.net/blox#get-pro";
		const URL_SUPPORT = "http://unitecms.ticksy.com/blox#get-pro";
		
		public static $permisison_add = false;
		public static $blankWindowMode = false;
		
		public static $view_default;
		
		public static $table_addons;
		public static $table_categories;
		public static $table_layouts;
		public static $table_templates;
		
		public static $pathSettings;
		public static $filepathItemSettings;
		public static $pathPlugin;
		public static $pathTemplates;
		public static $pathViews;
		public static $pathViewsObjects;
		public static $pathLibrary;
		public static $pathAssets;
		public static $pathProvider;
		public static $pathProviderViews;
		public static $pathProviderTemplates;
		
		public static $current_host;
		public static $current_page_url;
		
		public static $url_base;
		public static $url_images;
		public static $url_component_client;
		public static $url_component_admin;
		public static $url_component_admin_nowindow;
		public static $url_ajax;
		public static $url_ajax_front;
		public static $url_default_addon_icon;
		
		public static $urlPlugin;
		public static $url_provider;
		public static $url_assets;
		public static $url_assets_libraries;
		public static $url_assets_internal;
		
		public static $is_admin;
		public static $isLocal;		//if website located in localhost
		
		public static $is_ssl;
		public static $path_base;
		public static $path_cache;
		public static $path_images;
		
		public static $layoutShortcodeName = "blox_page";
		public static $layoutsAddonType = null;
		
		public static $arrClientSideText = array();
		public static $arrServerSideText = array();
		
		public static $isProductActive = false;
		public static $defaultAddonType = "";
		public static $enableWebCatalog = true;
		
		
		/**
		 * init globals
		 */
		public static function initGlobals(){
			
			UniteProviderFunctionsUC::initGlobalsBase();
						
			self::$current_host = UniteFunctionsUC::getVal($_SERVER, "HTTP_HOST");
			self::$current_page_url = self::$current_host.UniteFunctionsUC::getVal($_SERVER, "REQUEST_URI");
			
			self::$pathProvider = self::$pathPlugin."provider/";
			self::$pathTemplates = self::$pathPlugin."views/templates/";
			self::$pathViews = self::$pathPlugin."views/";
			self::$pathViewsObjects = self::$pathPlugin."views/objects/";
			self::$pathLibrary = self::$pathPlugin."library/";
			self::$pathSettings = self::$pathPlugin."settings/";
			
			self::$pathProviderViews = self::$pathProvider."views/";
			self::$pathProviderTemplates = self::$pathProvider."views/templates/";
			
			self::$filepathItemSettings = self::$pathSettings."item_settings.php";
									
			//check for wp version
			UniteFunctionsUC::validateNotEmpty(GlobalsUC::$url_assets_internal, "assets internal");
			
			self::$isLocal = UniteFunctionsUC::isLocal();
			
			/*
			$action = UniteFunctionsUC::getGetVar("maxaction", "", UniteFunctionsUC::SANITIZE_KEY);
			if($action == "showvars")
				GlobalsUC::printVars();
			*/
			
			//GlobalsUC::printVars();
		}
		
		
		/**
		 * init after the includes done
		 */
		public static function initAfterIncludes(){
			
			$webAPI = new UniteCreatorWebAPI();
			
			self::$isProductActive = $webAPI->isProductActive();
			
		}
		
		
		/**
		 * print all globals variables
		 */
		public static function printVars(){
			
			$methods = get_class_vars( "GlobalsUC" );
			dmp($methods);
			exit();
		}
		
	}

	//init the globals
	GlobalsUC::initGlobals();
	
?>
