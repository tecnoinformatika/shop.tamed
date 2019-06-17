<?php

/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorWebAPI{
	
	private static $urlAPI;
	private static $arrCatalogData;
	
	const CATALOG_CHECK_PERIOD = 7200;	 		//2 hours
	const CATALOG_CHECK_PERIOD_NOTEXIST = 600;	//10 min
	
	const OPTION_ACTIVATION = "addon_library_activation";
	const OPTION_CATALOG = "addon_library_catalog";
	const OPTION_TIMEOUT_TRANSIENT = "addon_library_catalog_timeout";
	
	
	/**
	 * construct
	 */
	public function __construct(){
		
		if(empty(self::$urlAPI))
			self::$urlAPI = GlobalsUC::URL_API;
	}
	
	private function a_________GETTERS___________(){}
		
	/**
	 * get activated product data
	 */
	private function getActivatedData(){
	
		$arrActivation = UniteProviderFunctionsUC::getOption(self::OPTION_ACTIVATION);
		if(empty($arrActivation))
			return(null);
	
		return($arrActivation);
	}
	
	
	/**
	 * get activation code
	 */
	private function getActivationCode(){
	
		$arrActivation = UniteProviderFunctionsUC::getOption(self::OPTION_ACTIVATION);
		if(empty($arrActivation))
			return("");
	
		$code = UniteFunctionsUC::getVal($arrActivation, "code");
	
		return($code);
	}
	
	
	/**
	 * get addon names array
	 */
	private function getArrAddonNames($arrCatalogAddons){
				
		if(empty($arrCatalogAddons))
			return(array());
		
		$arrNames = array();
		foreach($arrCatalogAddons as $arrCat){
			
			foreach($arrCat as $addon){
				$name = UniteFunctionsUC::getVal($addon, "name");
				$arrNames[$name] = true;
			}
		}
		
		return($arrNames);
	}
	
	
	/**
	 * modify data before save
	 */
	private function modifyArrData($arrData){
		
			
		//add addon names 
		if(!isset($arrData["catalog_addon_names"])){
			$arrCatalog = UniteFunctionsUC::getVal($arrData, "catalog");
			$arrAddons = UniteFunctionsUC::getVal($arrCatalog, "addons");
			
			$arrData["catalog_addon_names"] = $this->getArrAddonNames($arrAddons);
		}
		
		return($arrData);
	}
	
	
	/**
	 * get catalog data
	 */
	private function getCatalogData(){
	
		if(!empty(self::$arrCatalogData))
			return(self::$arrCatalogData);
		
		$arrData = UniteProviderFunctionsUC::getOption(self::OPTION_CATALOG);
		
		if(is_array($arrData) == false)
			return(null);
			
		$arrData = $this->modifyArrData($arrData);
		
		self::$arrCatalogData = $arrData;
	
		return($arrData);
	}
	
	/**
	 * get full catalog array
	 * Enter description here ...
	 */
	private function getCatalogArrayFromData(){
		
		$arrData = $this->getCatalogData();
		if(empty($arrData))
			return(array());
		
		$arrCatalog = UniteFunctionsUC::getVal($arrData, "catalog");
		
		//return from old way
		if(!isset($arrCatalog["addons"])){
			$arrCatalogOutput = array();
			$arrCatalogOutput["addons"] = $arrCatalog;
			$arrCatalogOutput["pages"] = array();
			
			return($arrCatalogOutput);
		}
		
		
		return($arrCatalog);
	}
	
	
	/**
	 * get catalog array
	 */
	private function getCatalogArray_addons(){
		
		$arrCatalog = $this->getCatalogArrayFromData();
		
		$arrCatalogAddons = $arrCatalog["addons"];
		
		return($arrCatalogAddons);		
	}

	
	/**
	 * get catalog array
	 */
	public function getCatalogArray_pages(){
		
		$arrCatalog = $this->getCatalogArrayFromData();
		
		$arrCatalogAddons = $arrCatalog["pages"];
		
		return($arrCatalogAddons);		
	}
	
	
	
	
	/**
	 * print catalog
	 */
	public function printCatalog(){
		
		$arrCatalog = $this->getCatalogArrayFromData();
		
		dmp($arrCatalog);
		exit();
	}
	
	
	/**
	 * get catalog addon names
	 */
	private function getArrCatalogAddonNames(){
		
		$arrData = $this->getCatalogData();
		
		if(empty($arrData))
			return(array());
					
		$arrNames = UniteFunctionsUC::getVal($arrData, "catalog_addon_names");
				
		return($arrNames);
	}
	
	
	/**
	 * check if product active or not
	 */
	public function isProductActive(){
	
		$data = $this->getActivatedData();
		
		if(empty($data))
			return(false);
	
		$stampExpire = UniteFunctionsUC::getVal($data, "expire");
	
		if(empty($stampExpire))
			return(false);
	
		$stampExpire = (int)$stampExpire;
		$stampNow = time();
	
		if($stampExpire < $stampNow)
			return(false);
	
		return(true);
	}
	
	/**
	 * check if time to check catalog
	 */
	public function isTimeToCheckCatalog(){
	
		$timeout = UniteProviderFunctionsUC::getTransient(self::OPTION_TIMEOUT_TRANSIENT);
	
		if(empty($timeout))
			return(true);
		else
			return(false);
	}
	
	
		
	
	/**
	 * get catalog version
	 */
	public function getCurrentCatalogStamp(){
	
		$arrData = $this->getCatalogData();
		if(empty($arrData))
			return(null);
	
		$stamp = UniteFunctionsUC::getVal($arrData, "stamp");
	
		return($stamp);
	}
	
	
	/**
	 * get current catalog date
	 */
	public function getCurrentCatalogDate(){
	
		$isExists = $this->isCatalogExists();
		if($isExists == false)
			return("");
	
		$stamp = $this->getCurrentCatalogStamp();
	
		if(empty($stamp))
			return("");
	
		$date = UniteFunctionsUC::timestamp2Date($stamp);
	
		return($date);
	}
	
	/**
	 * check if the saved catalog exists
	 */
	public function isCatalogExists(){
		
		$arrData = $this->getCatalogData();
		
		if(empty($arrData))
			return(false);
	
		return(true);
	}
	
	/**
	 * is pages catalog exists
	 */
	public function isPagesCatalogExists(){
		
		if($this->isCatalogExists() == false)
			return(false);
		
		$arrPages = $this->getCatalogArray_pages();
		if(empty($arrPages))
			return(false);
		
		return(true);
	}
	
	/**
	 * check if addon exists in catalog
	 * if empty catalog return false
	 */
	public function isAddonExistsInCatalog($addonName){
								
		$arrNames = $this->getArrCatalogAddonNames();
		
		if(isset($arrNames[$addonName]))
			return(true);
		
		return(false);
	}
	
	
	private function a_________SETTERS___________(){}
	
	
	/**
	 * call API with some action and data
	 */
	private function callAPI($action, $data = array(), $isRawResponse = false){
		
		$data["action"] = $action;
		$data["domain"] = GlobalsUC::$current_host;
				
		if(!isset($data["code"]))
			$data["code"] = $this->getActivationCode();
		
		if(!isset($data["catalog_date"]))
			$data["catalog_date"] = $this->getCurrentCatalogStamp();
		
		$data["blox_version"] = ADDON_LIBRARY_VERSION;
		
		$response = UniteFunctionsUC::getUrlContents(self::$urlAPI, $data);
		
		if($isRawResponse == true){
			$len = strlen($response);
			if($len < 200){
				$objResponse = @json_decode($response);
				if(empty($objResponse))
					return($objResponse);
			}else
				return($response);
		}
		
		if(empty($response))
			UniteFunctionsUC::throwError("Wrong API Response");
		
		$arrResponse = UniteFunctionsUC::jsonDecode($response);
		
		if(empty($arrResponse))
			UniteFunctionsUC::throwError("wrong API response: ".$response);
		
		$success = UniteFunctionsUC::getVal($arrResponse, "success");
		$success = UniteFunctionsUC::strToBool($success);
		if($success == false){
			$message = UniteFunctionsUC::getVal($arrResponse,"message");
			if(empty($message))
				$message = "There was some error";
			
			$message = "server error: ".$message;
			
			UniteFunctionsUC::throwError($message);
		}
		
		return($arrResponse);
	}
	
	
	/**
	 * save activated product
	 * save purchase code and expire days
	 */
	private function saveActivatedProduct($code, $expireStamp){
		
		$arrActivation = array();
		$arrActivation["code"] = $code;
		$arrActivation["expire"] = $expireStamp;
		
		UniteProviderFunctionsUC::updateOption(self::OPTION_ACTIVATION, $arrActivation);
		
	}
	
	/**
	 * delete saved catalog
	 */
	public function deleteCatalog(){
	
		UniteProviderFunctionsUC::deleteOption(self::OPTION_CATALOG);
	
	}
	
	
	/**
	 * deactivate product
	 */
	public function deactivateProduct(){
	
		UniteProviderFunctionsUC::deleteOption(self::OPTION_ACTIVATION);
	
	}
	
	
	/**
	 * activate product from data
	 */
	public function activateProductFromData($data){
		
		$code = UniteFunctionsUC::getVal($data, "code");
		$type = UniteFunctionsUC::getVal($data, "type");
		UniteFunctionsUC::validateNotEmpty($code, "Activation Code");
		
		$reqData = array();
		$reqData["code"] = $code;
		$reqData["type"] = $type;
		
		$responseAPI = $this->callAPI("activate", $reqData);
		
		
		$expireStamp = UniteFunctionsUC::getVal($responseAPI, "expire_stamp");
		$expireDays = UniteFunctionsUC::getVal($responseAPI, "expire_days");
		
		//save activation
		$this->saveActivatedProduct($code, $expireStamp);
		
		UniteFunctionsUC::validateNotEmpty($expireDays, "Expire Days");
		
		return($expireDays);
	}
	
		
	
	/**
	 * save catalog data
	 */
	private function saveCatalogData($stamp, $arrCatalog){
		
		$arrData = array();
		$arrData["stamp"] = $stamp;
		$arrData["catalog"] = $arrCatalog;
		$arrData["catalog_addon_names"] = $this->getArrAddonNames($arrCatalog);
		
		
		UniteProviderFunctionsUC::updateOption(self::OPTION_CATALOG, $arrData);
		
	}
	
	
	/**
	 * check or update catalog in web
	 */
	public function checkUpdateCatalog(){
		
		try{
			
			$isCatalogExists = $this->isCatalogExists();
			
			if($isCatalogExists == false){
				$checkPerioud = self::CATALOG_CHECK_PERIOD_NOTEXIST;
				$catalogStamp = null;
			}else{
				
				//update transient, for wait perioud
				$checkPerioud = self::CATALOG_CHECK_PERIOD;				
				
				$catalogStamp = $this->getCurrentCatalogStamp();
				
				if(empty($catalogStamp))
					$checkPerioud = self::CATALOG_CHECK_PERIOD_NOTEXIST;
				
			}
			
			UniteProviderFunctionsUC::setTransient(self::OPTION_TIMEOUT_TRANSIENT, true, $checkPerioud);
			
			
			$data = array();
			$data["catalog_date"] = $catalogStamp;
			$data["include_pages"] = true;
			
			$response = $this->callAPI("check_catalog", $data);
			
			
			$updateFound = UniteFunctionsUC::getVal($response, "update_found");
			$updateFound = UniteFunctionsUC::strToBool($updateFound);
			
			$clientResponse = array();
			
			//response up to date
			if($updateFound == false){
				$clientResponse["update_found"] = false;
				$catalogDate = UniteFunctionsUC::timestamp2DateTime($catalogStamp);
				$clientResponse["message"] = "The catalog is up to date: ".$catalogDate;
				
				return($clientResponse);
			}
			
			$stamp = UniteFunctionsUC::getVal($response, "stamp");
			$arrCatalog = UniteFunctionsUC::getVal($response, "catalog");
			
			$this->saveCatalogData($stamp, $arrCatalog);
			
			//response catalog date
			$date = UniteFunctionsUC::timestamp2DateTime($stamp);
			$clientResponse["update_found"] = true;
			$clientResponse["catalog_date"] = $date;
			$clientResponse["message"] = "The catalog updated. Catalog Date: $date. <br><br> Please refresh the browser to see the changes";
			
			return($clientResponse);
			
		}catch(Exception $e){
			
			$message = $e->getMessage();
			
			$clientResponse = array();
			$clientResponse["update_found"] = false;
			$clientResponse["error_message"] = $message;
			
			return($clientResponse);
			
			//remove me
			//HelperHtmlUC::outputException($e);
		}
		
	}
	
	
	/**
	 * merge cats with catalog cats
	 */
	public function mergeCatsAndAddonsWithCatalog($arrCats, $numAddonsOnly = false){
		
		if($this->isCatalogExists() == false)
			$this->checkUpdateCatalog();
		
		$arrWebCatalog = $this->getCatalogArray_addons();
		
		if(empty($arrWebCatalog))
			return($arrCats);
			
		foreach($arrWebCatalog as $dir=>$addons){
						
			//add directory
			if(isset($arrCats[$dir]) == false){
								
				$catHandle = HelperUC::convertTitleToHandle($dir);
				$catID = "ucweb_".$catHandle;
				
				$arrCats[$dir] = array(
					"id"=>$catID,
					"isweb"=>true,
					"title"=>$dir,
					"addons"=>array()
				);
			}
			
			$numWebAddons = 0;
			
			
			//add addons from web to existing folder
			foreach($addons as $arrAddon){
				$name = $arrAddon["name"];
				
				if(isset($arrCats[$dir]["addons"][$name]) == false){
					$arrAddon["isweb"] = true;
					$arrCats[$dir]["addons"][$name] = $arrAddon;
					$numWebAddons++;
				}
			}
			
			
			$arrCats[$dir]["num_web_addons"] = $numWebAddons;
		}
		
		
		if($numAddonsOnly == false)
			return($arrCats);
		
		//replace the addons bu num addons
		foreach($arrCats as $dir=>$cat){
			
			$arrAddons = UniteFunctionsUC::getVal($cat, "addons");
			$numAddons = 0;
			if(!empty($arrAddons))
				$numAddons = count($arrAddons);
			
			$arrCats[$dir]["num_addons"] = $numAddons;
			unset($arrCats[$dir]["addons"]);
			
			//delete uncategorized if empty
			$catID = UniteFunctionsUC::getVal($cat, "id");
			if($catID == 0 && $numAddons == 0)
				unset($arrCats[$dir]);
			
		}
		
		
		return($arrCats);
	}
	
	
	/**
	 * merge categories list with catalog
	 * for manager
	 */
	public function mergeCatsWithCatalog($arrCats){
		
		if($this->isCatalogExists() == false)
			$this->checkUpdateCatalog();
		
		$arrWebCatalog = $this->getCatalogArray_addons();
		
		if(empty($arrWebCatalog))
			return($arrCats);

		$arrCats = UniteFunctionsUC::arrayToAssoc($arrCats,"title");
		
		foreach($arrWebCatalog as $dir=>$addons){
			$arrDir = array();
			
			if(empty($addons))
				$addons = array();
			
			if(isset($arrCats[$dir]) == false)
				$arrCats[$dir] = array(
					"isweb"=>true,
					"title"=>$dir,
					"num_addons"=>count($addons)
				);			
			
			//add number of web addons
		}
		
		
		return($arrCats);
	}
	
	
	/**
	 * get category addons array from catalog
	 */
	public function getArrCatAddons($title){
				
		$arrWebCatalog = $this->getCatalogArray_addons();
		
		if(empty($arrWebCatalog))
			return(array());
		
		$arrCatAddons = UniteFunctionsUC::getVal($arrWebCatalog, $title);
		
		return($arrCatAddons);
	}
	
	
	/**
	 * filter web addons with installed addons
	 */
	private function filterWebAddonsByInstalled($arrWebAddons, $arrWebNames){
		
		if(empty($arrWebAddons))
			return($arrWebAddons);
		
		$objAddons = new UniteCreatorAddons();
		
		$params = array();
		$params["filter_names"] = $arrWebNames;
		$arrInstalledAddons = $objAddons->getArrAddonsShort("", $params);
		
		if(empty($arrInstalledAddons))
			return($arrWebAddons);
		
		foreach($arrInstalledAddons as $addon){
			$name = UniteFunctionsUC::getVal($addon, "name");
			unset($arrWebAddons[$name]);
		}
		
		return($arrWebAddons);
	}
	
	
	/**
	 * merge addons objects with the addons from catalog
	 */
	public function mergeCatAddonsWithCatalog($title, $arrAddons){
				
		$arrCatalogAddons = $this->getArrCatAddons($title);
		if(empty($arrCatalogAddons))
			return($arrAddons);
		
		$arrNames = array();
		foreach($arrAddons as $addon){
			$name = $addon->getName();
			$arrNames[$name] = true;
		}
		
		$arrWebAddons = array();
		
		$arrWebNames = array();
		foreach($arrCatalogAddons as $addon){
			
			$name = UniteFunctionsUC::getVal($addon, "name");
			if(isset($arrNames[$name]))
				continue;
			
			if(empty($name))
				continue;
			
			$addon["isweb"] = true;
			
			$arrWebNames[] = $name;
			$arrWebAddons[$name] = $addon;
		}
		
		//exclude web addons existing in another folders
		$arrWebAddons = $this->filterWebAddonsByInstalled($arrWebAddons, $arrWebNames);
		
		foreach($arrWebAddons as $addon)
			$arrAddons[] = $addon;
		
				
		return($arrAddons);
	}
	
	
	
	/**
	 * install catalog addon
	 */
	public function installCatalogAddonFromData($data){
		
		$name = UniteFunctionsUC::getVal($data, "name");
		$cat = UniteFunctionsUC::getVal($data, "cat");
		
		$apiData = array();
		$apiData["name"] = $name;
		$apiData["cat"] = $cat;
		
		$zipContent = $this->callAPI("get_addon_zip", $data, true);
				
		//save to folder
		$filename = $name.".zip";
		$filepath = GlobalsUC::$path_cache.$filename;
		UniteFunctionsUC::writeFile($zipContent, $filepath);
		
		$exporter = new UniteCreatorExporter();
		$exporter->import(null, $filepath);
		
		$importedAddonID = $exporter->getImportedAddonID();
		
		return($importedAddonID);
	}

	
	/**
	 * install catalog addon
	 */
	public function installCatalogPageFromData($data){
		
		$name = UniteFunctionsUC::getVal($data, "name");
		
		$apiData = array();
		$apiData["name"] = $name;
				
		$zipContent = $this->callAPI("get_page_zip", $data, true);
				
		//save to folder
		$filename = $name.".zip";
		$filepath = GlobalsUC::$path_cache.$filename;
		UniteFunctionsUC::writeFile($zipContent, $filepath);
				
		$exporter = new UniteCreatorLayoutsExporter();
		$importedLayoutID = $exporter->import($filepath, null, true);
		
		if(file_exists($filepath))
			@unlink($filepath);
		
		return($importedLayoutID);
	}
	
	
}