<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


/**
 * 
 * creator helper functions class
 *
 */
	class HelperUC extends UniteHelperBaseUC{

		private static $db;
		public static $operations;
		private static $arrFontPanelData;
		private static $arrAdminNotices = array();
		private static $isPutAnimations = false;
		private static $arrLogMemory = array();
		private static $arrHashCache = array();
		
		
		public static function a________________GENERAL______________(){}
		
		
		/**
		 * get the database
		 */
		public static function getDB(){
			
			if(empty(self::$db))
				self::$db = new UniteCreatorDB();
			
			return(self::$db);
		}
		
		
		/**
		 * include all plugins
		 */
		public static function includeAllPlugins(){
			
			$objUCPlugins = new UniteCreatorPlugins();
			$objUCPlugins->initPlugins();
		}
		
		
		/**
		 * run provider function
		 */
		public static function runProviderFunc($func){
			
			$args = func_get_args();
			array_shift($args);
			
			$exists = method_exists("UniteProviderFunctionsUC",$func);
			
			if(!$exists)
				return(false);
			
			call_user_func_array(array("UniteProviderFunctionsUC",$func), $args);
			
		}
		
		
		/**
		 * get font panel fields
		 */
		public static function getFontPanelData(){
			
			if(!empty(self::$arrFontPanelData))
				return(self::$arrFontPanelData);
			
			require GlobalsUC::$pathSettings."font_panel_data.php";
						
			self::$arrFontPanelData = $arrData;
			
			return(self::$arrFontPanelData);
		}
		
		
		/**
		 * get text by key
		 */
		public static function getText($textKey){
			
			$searchKey = strtolower($textKey);
			
			if(array_key_exists($searchKey, GlobalsUC::$arrServerSideText))
				return(GlobalsUC::$arrServerSideText[$textKey]);
			
			if(array_key_exists($searchKey, GlobalsUC::$arrClientSideText))
				return(GlobalsUC::$arrClientSideText[$textKey]);
			
			return($textKey);
		}
		
		/**
		 * put text by key
		 */
		public static function putText($textKey){
			
			echo self::getText($textKey);
		}
		
		
		/**
		 * get settings object by name from settings folder
		 */
		public static function getSettingsObject($settingsName, $path=null){
			
			$pathSettings = self::getPathSettings($settingsName, $path);
			
			$objSettings = new UniteCreatorSettings();
			$objSettings->loadXMLFile($pathSettings);
			
			return($objSettings);
		}
		
		/**
		 * get current admin view
		 */
		public static function getAdminView(){
			
			if(UniteProviderFunctionsUC::isAdmin() == false)
				return(null);
			
			$view = UniteCreatorAdmin::getView();
			
			return($view);
		}
		
		public static function a________________NOTICES______________(){}
		
		/**
		 * add notice that will be showen on plugin pages
		 */
		public static function addAdminNotice($strNotice){
			self::$arrAdminNotices[] = $strNotice;
		}
		
		/**
		 * add notice that will be showen on plugin pages
		 */
		public static function getAdminNotices(){
			return(self::$arrAdminNotices);
		}
		
		
		public static function a________________MEMORY______________(){}
				
		
		/**
		 * store memory log
		 * state - start, end
		 */
		public static function logMemoryUsage($operation, $isUpdateOption = false){
			
			$usage = memory_get_usage();
			
			$diff = 0;
			if(!empty(self::$arrLogMemory)){
				$lastArrUsage = self::$arrLogMemory[count(self::$arrLogMemory)-1];
				$lastUsage = $lastArrUsage["usage"];
				$diff = $usage - $lastUsage;
			}
			
			$arrLogItem = array("oper"=>$operation,"usage"=>$usage,"diff"=>$diff, "time"=>time());
			
			//log the page
			if(empty(self::$arrLogMemory)){
				$arrLogItem["current_page"] = GlobalsUC::$current_page_url;
			}
			
			self::$arrLogMemory[] = $arrLogItem;
			
			if($isUpdateOption == true)
				UniteProviderFunctionsUC::updateOption("unite_creator_memory_usage_log", self::$arrLogMemory);
			
		}
		
		
		/**
		 * get last memory usage
		 */
		public static function getLastMemoryUsage(){
			
			$arrLog = UniteProviderFunctionsUC::getOption("unite_creator_memory_usage_log");
			
			return($arrLog);
		}

		
		public static function a________________STATE______________(){}
		
		
		/**
		 * remember state
		 */
		public static function setState($name, $value){
			
			$optionName = "untecreator_state";
			
			$arrState = UniteProviderFunctionsUC::getOption($optionName);
			if(empty($arrState) || is_array($arrState) == false)
				$arrState = array();
			
			$arrState[$name] = $value;
			UniteProviderFunctionsUC::updateOption($optionName, $arrState);
		}
		
		
		/**
		 * get remembered state
		 */
		public static function getState($name){
			
			$optionName = "untecreator_state";
			
			$arrState = UniteProviderFunctionsUC::getOption($optionName);
			$value = UniteFunctionsUC::getVal($arrState, $name, null);
			
			return($value);
		}
		
		/**
		 * print general settings and exit all
		 */
		public static function printGeneralSettings(){
			$arrSettings = self::$operations->getGeneralSettings();
			dmp($arrSettings);
			exit();
		}
		
		/**
		 * get general setting value
		 */
		public static function getGeneralSetting($name){
			$arrSettings = self::$operations->getGeneralSettings();
			//dmp($arrSettings);exit();
			
			if(array_key_exists($name,$arrSettings) == false)
				UniteFunctionsUC::throwError("General setting: {$name} don't exists");
			
			$value = $arrSettings[$name];
			
			return($value);
		}
		
		
		public static function a________________URL_AND_PATH______________(){}
		
		
		/**
		 * convert url to full url
		 */
		public static function URLtoFull($url, $urlBase = null){
			
			if(is_numeric($url))		//protection for image id
				return($url);
			
			if(getType($urlBase) == "boolean")
				UniteFunctionsUC::throwError("the url base should be null or string");
			
			if(is_array($url))
				UniteFunctionsUC::throwError("url can't be array");
			
			$url = trim($url);
			
			if(empty($url))
				return("");
				
			$urlLower = strtolower($url);
			
			if(strpos($urlLower, "http://") !== false || strpos($urlLower, "https://") !== false)
				return($url);
			
			
			if(empty($urlBase))
				$url = GlobalsUC::$url_base.$url;
			else{
				
				$convertUrl = GlobalsUC::$url_base;
				
				//preserve old format:
				$filepath = self::pathToAbsolute($url);
				if(file_exists($filepath) == false)
					$convertUrl = $urlBase;
				
				$url = $convertUrl.$url;
			}
			
			return($url);
		}
		
		/**
		 * convert some url to relative
		 */
		public static function URLtoRelative($url, $isAssets = false){
			
			$replaceString = GlobalsUC::$url_base;
			if($isAssets == true)
				$replaceString = GlobalsUC::$url_assets;
			
			//in case of array take "url" from the array
			if(is_array($url)){
				
				if(array_key_exists("url", $url) == false)
					UniteFunctionsUC::throwError("URLtoRelative error: url key not found in array");
				
				$strUrl = UniteFunctionsUC::getVal($url, "url");
				if(empty($strUrl))
					return($url);
				
				$url["url"] = str_replace($replaceString, "", $strUrl);
				
				return($url);
			}
			
			$url = str_replace($replaceString, "", $url);
		
			return($url);
		}
		
		
		/**
		 * change url to assets relative
		 */
		public static function URLtoAssetsRelative($url){
			$url = str_replace(GlobalsUC::$url_assets, "", $url);
			
			return($url);
		}

		
		/**
		 * convert url to path, if wrong path, return null
		 */
		public static function urlToPath($url){
			$urlRelative = self::URLtoRelative($url);
			$path = GlobalsUC::$path_base.$urlRelative;
			if(file_exists($path) == false)
				return(null);
			
			return($path);
		}
		
		
		/**
		 * convert url array to relative
		 */
		public static function arrUrlsToRelative($arrUrls, $isAssets = false){
			if(!is_array($arrUrls))
				return($arrUrls);
			
			foreach($arrUrls as $key=>$url){
				$arrUrls[$key] = self::URLtoRelative($url, $isAssets);
			}
			
			return($arrUrls);
		}
		
		
		/**
		 * convert url's array to full
		 */
		public static function arrUrlsToFull($arrUrls){
			if(!is_array($arrUrls))
				return($arrUrls);
			
			foreach($arrUrls as $key=>$url){
				$arrUrls[$key] = self::URLtoFull($url);
			}
			
			return($arrUrls);
		}

		
		/**
		 * strip base path part from the path
		 */
		public static function pathToRelative($path, $addDots = true){

			$realpath = realpath($path);
			if(!$realpath)
				return($path);
			
			$isDir = is_dir($realpath);
			
			$len = strlen($realpath);
			$realBase = realpath(GlobalsUC::$path_base);
			$relativePath = str_replace($realBase, "", $realpath);
			
			//add dots
			if($addDots == true && strlen($relativePath) != strlen($realpath))
				$relativePath = "..".$relativePath;				
			
			$relativePath = UniteFunctionsUC::pathToUnix($relativePath);
			
			if($addDots == false)
				$relativePath = ltrim($relativePath, "/");
			
			//add slash to end
			if($isDir == true)
				$relativePath = UniteFunctionsUC::addPathEndingSlash($relativePath);
			
			return $relativePath;
		}
		
		
		/**
		 * convert relative path to absolute path
		 */
		public static function pathToAbsolute($path){
			
			$basePath = GlobalsUC::$path_base;
			$basePath = UniteFunctionsUC::pathToUnix($basePath);
			
			$path = UniteFunctionsUC::pathToUnix($path);
						
			$realPath = UniteFunctionsUC::realpath($path, false);
			
			if(!empty($realPath))
				return($path);
			
			if(UniteFunctionsUC::isPathUnderBase($path, $basePath)){
				$path = UniteFunctionsUC::pathToUnix($path);
				return($path);
			}
			
			$path = $basePath."/".$path;
			$path = UniteFunctionsUC::pathToUnix($path);
			
			return($path);
		}
		
		
		/**
		 * turn path to relative url
		 */
		public static function pathToRelativeUrl($path){
									
			$path = self::pathToRelative($path, false);
			$url = str_replace('\\', '/', $path);
			
			//remove starting slash
			$url = ltrim($url, '/');
			
			return($url);
		}
		
		
		
		/**
		 * convert path to absolute url
		 */
		public static function pathToFullUrl($path){
			if(empty($path))
				return("");
			
			$url = self::pathToRelativeUrl($path);
			$url = self::URLtoFull($url);
			return($url);
		}
		
		
		/**
		 * get details of the image by the image url.
		 */
		public static function getImageDetails($urlImage){
		
			$info = UniteFunctionsUC::getPathInfo($urlImage);
			$urlDir = UniteFunctionsUC::getVal($info, "dirname");
			if(!empty($urlDir))
				$urlDir = $urlDir."/";
		
			$arrInfo = array();
			$arrInfo["url_full"] = GlobalsUC::$url_base.$urlImage;
			$arrInfo["url_dir_image"] = $urlDir;
			$arrInfo["url_dir_thumbs"] = $urlDir.GlobalsUC::DIR_THUMBS."/";
		
			$filepath = GlobalsUC::$path_base.urldecode($urlImage);
			$filepath = realpath($filepath);
		
			$path = dirname($filepath)."/";
			$pathThumbs = $path.GlobalsUC::DIR_THUMBS."/";
		
			$arrInfo["filepath"] = $filepath;
			$arrInfo["path"] = $path;
			$arrInfo["path_thumbs"] = $pathThumbs;
		
			return($arrInfo);
		}
		
		
		/**
		 * convert title to handle
		 */
		public static function convertTitleToHandle($title, $removeNonAscii = true){
			
			$handle = strtolower($title);
			
			$handle = str_replace(array("ä", "Ä"), "a", $handle);
			$handle = str_replace(array("å", "Å"), "a", $handle);
			$handle = str_replace(array("ö", "Ö"), "o", $handle);
			
			if($removeNonAscii == true){
				
				// Remove any character that is not alphanumeric, white-space, or a hyphen
				$handle = preg_replace("/[^a-z0-9\s\_]/i", " ", $handle);
			
			}
			
			// Replace multiple instances of white-space with a single space
			$handle = preg_replace("/\s\s+/", " ", $handle);
			// Replace all spaces with underscores
			$handle = preg_replace("/\s/", "_", $handle);
			// Replace multiple underscore with a single underscore
			$handle = preg_replace("/\_\_+/", "_", $handle);
			// Remove leading and trailing underscores
			$handle = trim($handle, "_");
			
			return($handle);
		}
		
		/**
		 * convert title to alias
		 */
		public static function convertTitleToAlias($title){
			
			$handle = self::convertTitleToHandle($title);
			$alias = str_replace("_", "-", $handle);
			
			return($alias);
		}
		
		
		/**
		 * get url handle
		 */
		public static function getUrlHandle($url, $addonName = null){
			
			$urlNew = HelperUC::URLtoAssetsRelative($url);
			if($urlNew != $url){	//is inside assets
				$urlNew = "uc_assets_".$urlNew;
				
				//make handle by file name and size
				$path = self::urlToPath($url);
				if(!empty($path)){
					$arrInfo = pathinfo($path);
					$filename = UniteFunctionsUC::getVal($arrInfo, "basename");
					$filesize = filesize($path);
					
					$urlNew = "ac_assets_file_".$filename."_".$filesize;
				}
				
			}else{
				$urlNew = HelperUC::URLtoRelative($url);
				if($urlNew != $url)
					$urlNew = "uc_".$urlNew;
				else
					$urlNew = $url;
			}
			
			$url = strtolower($urlNew);
			$url = str_replace("https://","",$url);
			$url = str_replace("http://","",$url);
			
			if(strpos($url,"uc_") !== 0)
				$url = "uc_".$url;
			
			$handle = self::convertTitleToHandle($url);
			
			return($handle);
		}
		
		
		/**
		 * convert shortcode to url assets
		 */
		public static function convertFromUrlAssets($value, $urlAssets){
			if(empty($urlAssets))
				return($value);
			
			$value = str_replace("[url_assets]/", $urlAssets, $value);
			$value = str_replace("{{url_assets}}/", $urlAssets, $value);
			
			return($value);
		}
		
		/**
		 * if the website is ssl - convert url to ssl
		 */
		public static function urlToSSLCheck($url){
			
			if(GlobalsUC::$is_ssl == true)
				$url = UniteFunctionsUC::urlToSsl($url);
			
			return($url);
		}
		
		
		/**
		 * download file given from some url to cache folder
		 * return filepath
		 */
		public static function downloadFileToCacheFolder($urlFile){
			
			$info = pathinfo($urlFile);
			$filename = UniteFunctionsUC::getVal($info, "basename");
			if(empty($filename))
				UniteFunctionsUC::throwError("no file given");
			
			$ext = $info["extension"];
			
			if($ext != "zip")
				UniteFunctionsUC::throwError("wrong file given");
			
			$pathCache = GlobalsUC::$path_cache;
			UniteFunctionsUC::mkdirValidate($pathCache, "cache folder");
			
			$pathCacheImport = $pathCache."import/";
			UniteFunctionsUC::mkdirValidate($pathCacheImport, "cache import folder");
			
			$filepath = $pathCacheImport.$filename;
			
			$content = @file_get_contents($urlFile);
			if(empty($content))
				UniteFunctionsUC::throwError("Can't dowonload file from url: $urlFile");
			
			UniteFunctionsUC::writeFile($content, $filepath);
			
			return($filepath);
		}
		
		
		public static function a________________VIEW_TEMPLATE______________(){}
		
		
		/**
		 * get ajax url for export
		 */
		public static function getUrlAjax($action, $params = ""){
			
			$urlAjax = GlobalsUC::$url_ajax;
			
			$urlAjax = UniteFunctionsUC::addUrlParams($urlAjax, "action=unitecreator_ajax_action&client_action={$action}");
			
			if(!empty($params))
				$urlAjax .= "&".$params;
			
			return($urlAjax);
		}
		
		
		/**
		 *
		 * get url to some view.
		 */
		public static function getViewUrl($viewName, $urlParams="", $isBlankWindow = false, $isFront = false){
			
			$params = "&view=".$viewName;
			
			if(!empty($urlParams))
				$params .= "&".$urlParams;
			
			if($isFront == false)
				$link = GlobalsUC::$url_component_admin.$params;
			else
				$link = GlobalsUC::$url_component_client.$params;
							
			if($isBlankWindow == true)
				$link = UniteProviderFunctionsUC::convertUrlToBlankWindow($link);
			
			$link = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MODIFY_URL_VIEW, $link, $viewName, $params);
			
			return($link);
		}
		
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_Addons($type = ""){
			
			if(empty($type))
				return self::getViewUrl(GlobalsUC::VIEW_ADDONS_LIST);
			
			$urlView = UniteProviderAdminUC::getUrlViewAddonsByType($type);
			
			return($urlView);
		}
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_Default($params = ""){
		
			return self::getViewUrl(GlobalsUC::$view_default, $params);
		}
		
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_LayoutsList($params = array()){
		
			$url = self::getViewUrl(GlobalsUC::VIEW_LAYOUTS_LIST);
			
			$url = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_URL_LAYOUTS_LIST, $url, $params);
						
			return($url);
		}
		
		
		/**
		 * get some object url
		 */
		private static function getUrlViewObject($view, $objectID, $optParams, $isBlankWindow = false){
			
			$params = "";
			if(!empty($objectID))
				$params = "id=$objectID";
			
			if(!empty($optParams)){
				if(!empty($params))
					$params .= "&";
				
				$params .= $optParams;
			}
			
			return(self::getViewUrl($view, $params, $isBlankWindow));
		} 
		
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_Layout($layoutID = null, $optParams = ""){
			
			return self::getUrlViewObject(GlobalsUC::VIEW_LAYOUT, $layoutID, $optParams, true);
		}

		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_Template($templateID = null, $optParams = ""){
			
			return self::getUrlViewObject(GlobalsUC::VIEW_TEMPLATE, $templateID, $optParams);
		}
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_Templates_List(){
			
			return self::getViewUrl(GlobalsUC::VIEW_TEMPLATES_LIST);
		}
		
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_LayoutPreview($layoutID, $isBlankWindow = false){
			
			$layoutID = (int)$layoutID;
			
			UniteFunctionsUC::validateNotEmpty($layoutID, "layout id");
			
			$params = "id=$layoutID";
			
			//if($isBlankWindow == true)
			$url = self::getViewUrl(GlobalsUC::VIEW_LAYOUT_PREVIEW, $params, $isBlankWindow, true);
			
			return($url);
		}
		
		
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_EditAddon($addonID){
		
			return(self::getViewUrl(GlobalsUC::VIEW_EDIT_ADDON, "id={$addonID}"));
		}

		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_TestAddon($addonID, $optParams=""){
			$params = "id={$addonID}";
			if(!empty($optParams))
				$params .= "&".$optParams;
			
			return(self::getViewUrl(GlobalsUC::VIEW_TEST_ADDON, $params));
		}
		
		/**
		 * get addons view url
		 */
		public static function getViewUrl_AddonDefaults($addonID, $optParams=""){
			$params = "id={$addonID}";
			if(!empty($optParams))
				$params .= "&".$optParams;
			
			return(self::getViewUrl(GlobalsUC::VIEW_ADDON_DEFAULTS, $params));
		}
		
		
		
		/**
		 * get filename title from some url
		 * used to get item title from image url
		 */
		public static function getTitleFromUrl($url, $defaultTitle = "item"){
		
			$info = pathinfo($url);
			$filename = UniteFunctionsUC::getVal($info, "filename");
			$filename = urldecode($filename);
		
			$title = $defaultTitle;
			if(!empty($filename))
				$title = $filename;
		
		
			return($title);
		}
		
		
		/**
		 * get file path
		 * @param  $filena
		 */
		private static function getPathFile($filename, $path, $defaultPath, $validateName, $ext="php"){
			
			if(empty($path))
				$path = $defaultPath;
			
			$filepath = $path.$filename.".".$ext;
			UniteFunctionsUC::validateFilepath($filepath, $validateName);
			
			return($filepath);
		}
		
		
		/**
		 * require some template from "templates" folder
		 */
		public static function getPathTemplate($templateName, $path = null){
		
			return self::getPathFile($templateName,$path,GlobalsUC::$pathTemplates,"Template");
		}
		
		/**
		 * require some template from "templates" folder
		 */
		public static function getPathView($viewName, $path = null){
		
			return self::getPathFile($viewName,$path,GlobalsUC::$pathViews,"View");
		}
		
		/**
		 * require some template from "templates" folder
		 */
		public static function getPathViewObject($viewObjectName, $path = null){
			
			return self::getPathFile($viewObjectName,$path,GlobalsUC::$pathViewsObjects,"View Object");
		}
		
		
		/**
		 * get settings path
		 */
		public static function getPathSettings($settingsName, $path=null){
			
			return self::getPathFile($settingsName,$path,GlobalsUC::$pathSettings,"Settings","xml");
		}
		
		/**
		 * get path provider template
		 */
		public static function getPathTemplateProvider($templateName){
			
			return self::getPathFile($templateName,GlobalsUC::$pathProviderTemplates,"","Provider Template");			
		}
		
		
		/**
		 * get path provider view
		 */
		public static function getPathViewProvider($viewName){

			return self::getPathFile($viewName, GlobalsUC::$pathProviderViews,"","Provider View");
						
		}
		
		
		public static function a________________SCRIPTS______________(){}
		
		
		/**
		 * add animations scripts and styles
		 */
		public static function putAnimationIncludes($animateOnly = false){

			if(self::$isPutAnimations == true)
				return(false);
			
			$urlAnimateCss = GlobalsUC::$url_assets_libraries."animate/animate.css";
			self::addStyleAbsoluteUrl($urlAnimateCss, "animate");
			
			if($animateOnly == true)
				return(false);
			
			UniteProviderFunctionsUC::addAdminJQueryInclude();
			
			$urlWowJs = GlobalsUC::$url_assets_libraries."animate/wow.min.js";
			self::addScriptAbsoluteUrl($urlWowJs, "wowjs");
			
			$script = "jQuery(document).ready(function(){new WOW().init()});";
			UniteProviderFunctionsUC::printCustomScript($script);
			
			
			self::$isPutAnimations = true;
		}
		
		
		/**
		 *
		 * register script helper function
		 * @param $scriptFilename
		 */
		public static function addScript($scriptName, $handle=null, $folder="js", $inFooter = false){
			if($handle == null)
				$handle = GlobalsUC::PLUGIN_NAME."-".$scriptName;
			
			UniteProviderFunctionsUC::addScript($handle, GlobalsUC::$urlPlugin .$folder."/".$scriptName.".js", $inFooter);
		}
		
		

		/**
		 *
		 * register script helper function
		 * @param $scriptFilename
		 */
		public static function addScriptAbsoluteUrl($urlScript, $handle, $inFooter=false){
		
			UniteProviderFunctionsUC::addScript($handle, $urlScript,$inFooter);
			
		}
		
		
		/**
		 *
		 * register style helper function
		 * @param $styleFilename
		 */
		public static function addStyle($styleName,$handle=null,$folder="css"){
			if($handle == null)
				$handle = GlobalsUC::PLUGIN_NAME."-".$styleName;
			
			UniteProviderFunctionsUC::addStyle($handle, GlobalsUC::$urlPlugin .$folder."/".$styleName.".css");
			
		}
		
		/**
		 * put inline style
		 */
		public static function putInlineStyle($css){
			
			//prevent duplicates
			if(empty($css))
				return(false);
			
			
			//allow print style only once
			$hash = md5($css);
			if(isset(self::$arrHashCache[$hash]))
				return(false);
			self::$arrHashCache[$hash] = true;
			
			
			UniteProviderFunctionsUC::printCustomStyle($css);
			
		}
		
		
		/**
		 *
		 * register style absolute url helper function
		 */
		public static function addStyleAbsoluteUrl($styleUrl, $handle){
			
			UniteProviderFunctionsUC::addStyle($handle, $styleUrl);
			
		}
		
		
		/**
		 * output system message
		 */
		public static function outputNote($message){
			$message = "system note: <b>&nbsp;&nbsp;&nbsp;&nbsp;".$message."</b>";
			
			$html = "<div style='background-color:#FCE8DE;border:1px solid #DD480D; padding:20px;margin:20px;'>{$message}</div>";
			echo $html;			
		}
		
		
		
		/**
		 * output addon from storred data
		 */
		public static function outputAddonFromData($data){
			
			$addons = new UniteCreatorAddons();
			$objAddon = $addons->initAddonByData($data);
			
			$objOutput = new UniteCreatorOutput();
			$objOutput->initByAddon($objAddon);
			$html = $objOutput->getHtmlBody();
			$objOutput->processIncludes();
			
			echo $html;
		}
		
		
		
		/**
		 * get error message html
		 */
		public static function getHtmlErrorMessage($message, $trace="", $prefix=null){
			
			if(empty($prefix))
				$prefix = HelperUC::getText("addon_library")." Error: ";
			
			$message = $prefix.$message;
			
			$html = self::$operations->getErrorMessageHtml($message, $trace);
			return($html);
		}
		
		
		
		public static function a________________ASSETS_PATH______________(){}
		
		
		/**
		 * validate that path located under assets folder
		 */
		public static function validatePathUnderAssets($path){
			
			$isUnderAssets = self::isPathUnderAssetsPath($path);
			if(!$isUnderAssets)
				UniteFunctionsUC::throwError("The path should be under assets folder");
			
		}
		
		
		/**
		 * return true if some path under base path
		 */
		public static function isPathUnderAssetsPath($path){
			
			$path = self::pathToAbsolute($path);
			
			$assetsPath = GlobalsUC::$pathAssets;
			$assetsPath = self::pathToAbsolute($assetsPath);
						
			$isUnderAssets = UniteFunctionsUC::isPathUnderBase($path, $assetsPath);
			
			return($isUnderAssets);
		}
		
		
		/**
		 * check if some path is assets path
		 */
		public static function isPathAssets($path){

			$assetsPath = GlobalsUC::$pathAssets;
			$assetsPath = self::pathToAbsolute($assetsPath);
			
			$path = self::pathToAbsolute($path);
			
			if(!empty($path) && $path === $assetsPath)
				return(true);
			
			return(false);
		}
		
		
		/**
		 * convert path to assets relative path
		 */
		public static function pathToAssetsRelative($path){
			
			$assetsPath = GlobalsUC::$pathAssets;
			$assetsPath = self::pathToAbsolute($assetsPath);
			
			$path = self::pathToAbsolute($path);
			
			$relativePath = UniteFunctionsUC::pathToRelative($path, $assetsPath);
			
			return($relativePath);
		}
		
		
		/**
		 * path to assets absolute
		 * @param $path
		 */
		public static function pathToAssetsAbsolute($path){
			
			if(self::isPathUnderAssetsPath($path) == true)
				return($path);
			
			$assetsPath = GlobalsUC::$pathAssets;
			$path = UniteFunctionsUC::joinPaths($assetsPath, $path);
			
			return($path);
		}

		
		public static function a________________OUTPUT_LAYOUT______________(){}
		
		
		/**
		 * output layout
		 */
		public static function outputLayout($layoutID, $getHtml = false, $outputFullPage = false){
			
			try{
				
				$layoutID = UniteProviderFunctionsUC::sanitizeVar($layoutID, UniteFunctionsUC::SANITIZE_ID);
				
				$layout = new UniteCreatorLayout();
				$layout->initByID($layoutID);
				
				$outputLayout = new UniteCreatorLayoutOutput();
				$outputLayout->initByLayout($layout);
				
				if($getHtml == true){
					
					if($outputFullPage == false)
						$html = $outputLayout->getHtml();
					else
						$html = $outputLayout->getFullPreviewHtml();
						
					return($html);
				}
				
				if($outputFullPage == false)
					$outputLayout->putHtml();
				else
					$outputLayout->putFullPreviewHtml();
				
					
			}catch(Exception $e){
				if($getHtml == true){
					throw $e;
				}
				else
					HelperHtmlUC::outputExceptionBox($e, HelperUC::getText("addon_library"). " Error");
			}
			
		}
		
	}
	
	//init the operations
	HelperUC::$operations = new UCOperations();
	
	
?>
