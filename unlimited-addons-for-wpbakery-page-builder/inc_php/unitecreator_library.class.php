<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLibraryWork{
	
	private static $arrLibrary;
		
	
	/**
	 * construct the library, load library array
	 */
	public function __construct(){
		if(empty(self::$arrLibrary))
			$this->loadLibrary();
	}
	
	
	/**
	 * get includes (js or css) from object
	 */
	private function loadLibrary_getIncludes($xmlIncludes){
				
		$objIncludes = $xmlIncludes->include;
		if(!@count($objIncludes))
			$objIncludes = array($objIncludes);
		
		$arrIncludes = array();
		
		foreach($objIncludes as $objInclude){
			$attribs = $objInclude->attributes();
		
			$handle = (string)UniteFunctionsUC::getVal($attribs, "handle");
			$local = (string)UniteFunctionsUC::getVal($attribs, "local");
			$remote = (string)UniteFunctionsUC::getVal($attribs, "remote");
		
			if(empty($local) && empty($remote))
				UniteFunctionsUC::throwError("Include: $handle must have some url");
		
			if(!empty($local)){
				$local = trim($local);
				$urlInclude = GlobalsUC::$url_assets_libraries.$local;
			}else if(!empty($remote)){
				$urlInclude = $remote;
			}
		
			$arrIncludes[$handle] = $urlInclude;
		
		}
		
		return($arrIncludes);
	}
	
	
	/**
	 * load library from xml file
	 */
	private function loadLibrary(){
		$filepathLibrary = GlobalsUC::$pathSettings."library.xml";
		UniteFunctionsUC::validateFilepath($filepathLibrary);
		
		$arrLibrary = array();
		
		$obj = simplexml_load_file($filepathLibrary);
		
		if(empty($obj))
			UniteFunctionsUC::throwError("Wrong xml file format: $filepathLibrary");
		
		$items = $obj->item;
		if(!@count($obj->item)){
			$items = array($items);
		}
		
		foreach($items as $objItem){
			$attribs = $objItem->attributes();

			$name = (string)UniteFunctionsUC::getVal($attribs, "name");
			
			$arrItem = array();
			$arrItem["name"] = $name;
			$arrItem["title"] = (string)UniteFunctionsUC::getVal($attribs, "title");
			
			UniteFunctionsUC::validateNotEmpty($name, "item name");
			UniteFunctionsUC::validateNotEmpty($arrItem["title"], "item title");
			
			//get js includes
			$arrJsIncludes = array();
			
			if(isset($objItem->js))
				$arrJsIncludes = $this->loadLibrary_getIncludes($objItem->js);
			
			$arrCssIncludes = array();
			if(isset($objItem->css))
				$arrCssIncludes = $this->loadLibrary_getIncludes($objItem->css);
			
			$arrItem["includes_js"] = $arrJsIncludes;
			$arrItem["includes_css"] = $arrCssIncludes;
			
			$arrLibrary[$name] = $arrItem;
		}
		
		self::$arrLibrary = $arrLibrary;
	}
	
	/**
	 * function for override, process provide library
	 * return true if library found and processed, and false if not
	 */
	public function processProviderLibrary($name){
		return(false);
	}
	
	
	/**
	 * output urls in array of js and css saparately
	 */
	public function getLibraryIncludes($name){
	
		$urlBase = GlobalsUC::$url_assets_libraries;
		
		$arrJs = array();
		$arrCss = array();
		
		$output = array("js"=>array(),"css"=>array());
		
		if(!isset(self::$arrLibrary[$name]))
			return($output);
		
		$arrItem = self::$arrLibrary[$name];
		
		$output["js"] = $arrItem["includes_js"];
		$output["css"] = $arrItem["includes_css"];
		
		
		return($output);
	}
	
	
	/**
	 * get library array
	 */
	public function getArrLibrary(){
		
		return(self::$arrLibrary);
	}
	
	
}