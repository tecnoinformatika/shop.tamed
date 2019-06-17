<?php

/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayoutWork extends UniteElementsBaseUC{
	
	protected $id;
	protected $title, $data, $gridData, $ordering, $gridDataFull, $record, $params;
	protected static $arrCacheAddons = array();
	protected $addonType = null, $arrAllOptionsCache = null;
	
	
	const FIELDS_LAYOUTS = "title,ordering,layout_data";
	const LAYOUTS_GLOBAL_SETTINGS_OPTION = "unitecreator_layouts_general_settings";
	
	
	/**
	 * construct the layout
	 */
	public function __construct(){
		parent::__construct();
		
	}
	
	
	/**
	 * validate that the layout is inited
	 */
	public function validateInited(){
		
		if(empty($this->id))
			UniteFunctionsUC::throwError("The layout is not inited");
	}
	
	
	/**
	 * check if the layout inited
	 */
	public function isInited(){
		
		if(!empty($this->id))
			return(true);
		
		return(false);
	}
	
	/**
	 * validate title
	 */
	private function validateTitle($title){
		
		UniteFunctionsUC::validateNotEmpty($title, "Title");
		
	}
	
	private function a__________STATIC_FUNCTIONS___________(){}
	
	/**
	 * get stored values of grid options
	 */
	public static function getGridGlobalStoredValues(){
		$arrValues = UniteProviderFunctionsUC::getOption(self::LAYOUTS_GLOBAL_SETTINGS_OPTION, array());
		
		return($arrValues);
	}
	
	
	/**
	 * get settings object
	 */
	public static function getGlobalSettingsObject($includeGlobalOptions = true){
		
		$filepathSettings = GlobalsUC::$pathSettings."layouts_global_settings.xml";
		
		$objSettings = new UniteCreatorSettings();
		$objSettings->loadXMLFile($filepathSettings);
		
		if($includeGlobalOptions == true){
			$arrValues = self::getGridGlobalStoredValues();
			
			if(!empty($arrValues))
				$objSettings->setStoredValues($arrValues);
		}
		
		return($objSettings);
	}
	
	
	/**
	 * get grid settings object
	 */
	public static function getGridSettingsObject(){
	
		$filepathSettings = GlobalsUC::$pathSettings."layouts_grid_settings.xml";
		
		$objSettings = new UniteCreatorSettings();
		$objSettings->loadXMLFile($filepathSettings);
	
		return($objSettings);
	}
	
	
	
	/**
	 * update general settings
	 */
	public static function updateLayoutGlobalSettingsFromData($data){
	
		$arrValues = UniteFunctionsUC::getVal($data, "settings_values");
		
		UniteProviderFunctionsUC::updateOption(self::LAYOUTS_GLOBAL_SETTINGS_OPTION, $arrValues);
	}
	
	
	
	/**
	 * init layout by id
	 */
	public function initByID($id){
		
		$id = (int)$id;
		if(empty($id))
			UniteFunctionsUC::throwError("Empty layout ID");
		
		$options = array();
		$options["tableName"] = GlobalsUC::$table_layouts;
		$options["where"] = "id=".$id;
		$options["errorEmpty"] = "layout with id: {$id} not found";
				
		$record = $this->db->fetchSingle($options);
		
		$this->id = $id;
		
		$this->initByRecord($record);
	}
	
	private function a__________GET_OPTIONS___________(){}
		
	
	/**
	 * get global values - together with default
	 */
	public static function getGridGlobalOptions(){
		
		$settings = self::getGlobalSettingsObject();
		
		$arrValues = $settings->getArrValues();
		
		return($arrValues);
	}
	
	
	/**
	 * get grid settings options
	 */
	public static function getGridSettingsOptions($arrInitValues = array()){
		
		$objSettings = self::getGridSettingsObject();
		
		if(!empty($arrInitValues))
			$objSettings->setStoredValues($arrInitValues);
		
		$arrValues = $objSettings->getArrValues();
		
		return($arrValues);
	}
	
	/**
	 * get grid default options - without loading values
	 */
	public static function getGridDefaultOptions(){
		
		$objGlobalSettings = self::getGlobalSettingsObject(false);
						
		$arrValuesGrid = self::getGridSettingsOptions();
				
		$arrValuesGlobal = $objGlobalSettings->getArrValues();
				
		$arrMerged = array_merge($arrValuesGlobal, $arrValuesGrid);
				
		return($arrMerged);
	}
	
	
	/**
	 * get all grid options
	 */
	public function getAllGridOptions(){
		
		$this->validateInited();
		
		if(!empty($this->arrAllOptionsCache))
			return($this->arrAllOptionsCache);
		
		$globalOptions = self::getGridGlobalOptions();
		
		$layoutOptions = UniteFunctionsUC::getVal($this->gridData, "options", array());
		
		if(empty($layoutOptions) || is_array($layoutOptions) == false)
			$layoutOptions = array();
		
		$allOptions = array_merge($globalOptions, $layoutOptions);
		
		$this->arrAllOptionsCache = $allOptions;
		
		return($allOptions);
	}
	
	
	private function a__________DATA_RELATED___________(){}
	
	
	/**
	 * init layout by record
	 */
	protected function initByRecord($record){
		
		$this->title = UniteFunctionsUC::getVal($record, "title");
		$this->ordering = UniteFunctionsUC::getVal($record, "ordering");
		
		$data = UniteFunctionsUC::getVal($record, "layout_data");
		$data = UniteFunctionsUC::maybeUnserialize($data);
		
		$this->record = $record;
		$this->data = $data;
		
		$params = UniteFunctionsUC::getVal($record, "params");
		if(!empty($params))
			$this->params = UniteFunctionsUC::jsonDecode($params);
		
		if(empty($this->params))
			$this->params = array();
			
		$gridData = UniteFunctionsUC::getVal($data, "grid_data");
		
		$this->gridData = $gridData;
		
	}
	
	
	/**
	 * get arr addon
	 */
	private function getAddonObject($name){
		
		//take from cache
		if(isset(self::$arrCacheAddons[$name]))
			return(self::$arrCacheAddons[$name]);
	
		//init the obj - take from db
		try{
	
			$addon = new UniteCreatorAddon();
			
			if(empty($this->addonType))
				$addon->initByName($name);
			else 
				$addon->initByAlias($name, $this->addonType);
			
			self::$arrCacheAddons[$name] = $addon;
	
			return($addon);
	
		}catch(Exception $e){
			return(null);
		}
	
	}
	
	
	/**
	 * get extra data from addon like title
	 */
	private function getAddonExtraData($name, $addon = null){
	
		if(empty($addon))
			$addon = $this->getAddonObject($name);
	
		if(empty($addon))
			return(null);
	
		$arrExtraData = array();
		$arrExtraData["id"] = $addon->getID();
		$arrExtraData["title"] = $addon->getTitle();
		$arrExtraData["url_icon"] = $addon->getUrlIcon();
		$arrExtraData["admin_labels"] = $addon->getAdminLabels();		
		
		return($arrExtraData);
	}
	
	
	/**
	 * modify addon data for editor
	 */
	private function modifyAddonDataForEditor($addonData){
		
		try{
			
			//$objAddon = new UniteCreatorAddon();
			//$objAddon->isHasItems()
			
			$objAddons = new UniteCreatorAddons();
			$objAddon = $objAddons->prepareAddonByData($addonData);
			
			//merge config
			$arrConfig = $objAddon->getProcessedMainParamsValues(UniteCreatorParamsProcessor::PROCESS_TYPE_CONFIG);
			
			$origConfig = UniteFunctionsUC::getVal($addonData, "config");
			if(empty($origConfig))
				$origConfig = array();
			
			$addonData["config"] = array_merge($origConfig, $arrConfig);
			
			if($objAddon->isHasItems()){
				$arrItems = $objAddon->getProcessedItemsData(UniteCreatorParamsProcessor::PROCESS_TYPE_CONFIG, false);
				$addonData["items"] = $arrItems;
			}
			
			return($addonData);
			
		}catch(Exception $e){
			
			return($addonData);
		}
		
	}
	
	/**
	 * modify addon data for editor get
	 */
	private function createGridDataFull_modifyAddonData($addonData, $addParams = null){
		
		if(empty($addonData))
			return($addonData);
			
		$addonData = $this->modifyAddonDataForEditor($addonData);
		
		$addonName = UniteFunctionsUC::getVal($addonData, "name");
				
		if(empty($addonName))
			return($addonData);
		
		$addonExtraData = $this->getAddonExtraData($addonName);
		if(empty($addonExtraData)){
			$addonExtraData = array();
			$addonExtraData["missing"] = true;
		}
		
		$addonData["extra"] = $addonExtraData;
		
		
		//add addon content if needed
		$isAddContent = UniteFunctionsUC::getVal($addParams, "add_addon_content");
		$isAddContent = UniteFunctionsUC::strToBool($isAddContent);
		
		if($isAddContent == true){
			
			$addons = new UniteCreatorAddons();
			$addonData["output"] = $addons->getLayoutAddonOutputData($addonData);
		}
		
		return($addonData);
	}
	
	
	/**
	 * create grid data full
	 * complete the addon with it's data
	 */
	private function createGridDataFull($addAddonContent = false){
		
		if(!empty($this->gridDataFull))
			return($this->gridDataFull);
		
		$addParams = array();
		$addParams["add_addon_content"] = $addAddonContent;
		
		$this->gridDataFull = $this->mapModifyLayoutDataAddons($this->gridData, "createGridDataFull_modifyAddonData",  $addParams);
		
		return($this->gridDataFull);
	}
	
	
	/**
	 * modify layout data settings objects
	 */
	public function mapModifyLayoutDataSettings($arrData, $modifyFunc, $addParams = null){
		
		//modify grid options
		$gridSettings = UniteFunctionsUC::getVal($arrData, "options");
		if(!empty($gridSettings))
			$arrData["options"] = call_user_func($modifyFunc, $gridSettings, "grid");
		
		//modify rows options
		
		$rows = UniteFunctionsUC::getVal($arrData, "rows");
			
		if(empty($rows))
			return(false);
			
		foreach($rows as $rowIndex => $row){
			
			$rowSettings = UniteFunctionsUC::getVal($row, "settings");
			if(!empty($rowSettings))
				$arrData["rows"][$rowIndex]["settings"] = call_user_func($modifyFunc, $rowSettings, "row");
			
			//modify columns settings
			$cols = UniteFunctionsUC::getVal($row, "cols");
			if(empty($cols))
				continue;
			
			foreach($cols as $colIndex=>$col){
				
				$colSettings = UniteFunctionsUC::getVal($col, "settings");
				
				if(!empty($colSettings))
					$arrData["rows"][$rowIndex]["cols"][$colIndex]["settings"] = call_user_func($modifyFunc, $colSettings, "col");

				//modify addons settings
				
				$addonData = UniteFunctionsUC::getVal($col, "addon_data");
				if(empty($addonData))
					continue;
				
				foreach($addonData as $addonIndex=>$addon){
					
					$addonOptions = UniteFunctionsUC::getVal($addon, "options");
					if(empty($addonOptions))
						continue;
					
					$arrData["rows"][$rowIndex]["cols"][$colIndex]["addon_data"][$addonIndex]["options"] = call_user_func($modifyFunc, $addonOptions, "addon");
				}
				
			}
			
		}
		
				
		return($arrData);
	}
	
	
	/**
	 * map layout data, call modify function to modify each addon
	 */
	public function mapModifyLayoutDataAddons($arrData, $modifyFunc, $addParams = null){
	
		$rows = UniteFunctionsUC::getVal($arrData, "rows");
		if(empty($rows))
			return($arrData);
				
		foreach($rows as $keyRow=>$row){
			$cols = UniteFunctionsUC::getVal($row, "cols");
	
			foreach($cols as $keyCol=>$col){
				$addonData = UniteFunctionsUC::getVal($col, "addon_data");
								
				if(isset($addonData["config"]))
					$addonData = array($addonData);
	
				if(empty($addonData))
					$addonData = array();
								
				foreach($addonData as $keyAddon=>$addon){
								
					//modify addon
					if(is_array($modifyFunc))
						$addon = call_user_func($modifyFunc,$addon);
					else
						$addon = call_user_func(array($this, $modifyFunc),$addon, $addParams);
					
					if(!empty($addon))
						$addonData[$keyAddon] = $addon;
					else
						unset($addonData[$keyAddon]);
					
				}
	
				$col["addon_data"] = $addonData;
	
				$cols[$keyCol] = $col;
			}
	
			$row["cols"] = $cols;
	
			$rows[$keyRow] = $row;
		}
	
		$arrData["rows"] = $rows;
	
		return($arrData);
	}
	
	/**
	 * map layout data walk through all the layout array using recursion
	 */
	public function mapModifyLayoutDataAll($arrData, $modifyFunc){
		
		if(is_array($arrData)){
			
			$arrData = call_user_func($modifyFunc, $arrData);
			
			foreach($arrData as $index=>$item){
				if(is_array($item)){
					$item = call_user_func($modifyFunc, $item);
					$arrData[$index] = $this->mapModifyLayoutDataAll($item, $modifyFunc);
				}
					
			}
			
		}
		
		return($arrData);
	}
	
	
	/**
	 * modify grid data for save
	 */
	public function modifyAddonDataForSave($data){
		
		$name = UniteFunctionsUC::getVal($data, "name");
		
		try{
			
			unset($data["url_icon"]);
			unset($data["title"]);
						
			$config = UniteFunctionsUC::getVal($data, "config");
			$items = UniteFunctionsUC::getVal($data, "items");
			
			//init addon
			$addon = new UniteCreatorAddon();
			$addon->initByName($name);
			
			$addon->setParamsValues($config);
			
			if(!empty($items))
				$addon->setArrItems($items);
			
			
			if(!empty($config)){
				
				$arrImages = $addon->getProcessedMainParamsImages();
				if(!empty($arrImages)){
					$arrImages = $addon->modifyDataConvertToUrlAssets($arrImages);
					$data["config"] = array_merge($config, $arrImages);
				}
			}
			
			if(!empty($items) && is_array($items)){
				
				$arrItemsImages = $addon->getProcessedItemsData(UniteCreatorParamsProcessor::PROCESS_TYPE_SAVE, false, "uc_image");
				
				foreach($arrItemsImages as $key=>$itemImage){
					$itemImage = $addon->modifyDataConvertToUrlAssets($itemImage);
					
					if(!empty($itemImage))
						$items[$key] = array_merge($items[$key],$itemImage);
				}
			
				$data["items"] =  $items;
			}
	
		}catch(Exception $e){
			
			return($data);
		}
		
		
		return($data);
	}
	
	
	/**
	 * sanitize layout data for save
	 */
	private function prepareDataForSave($data){
	
		unset($data["title"]);
		unset($data["layoutid"]);
	
		if(isset($data["id"]))
			unset($data["id"]);
	
		$gridData = UniteFunctionsUC::getVal($data, "grid_data");
	
		//decode the data:
		if(is_string($gridData)){
			$gridData = UniteFunctionsUC::decodeContent($gridData);
			
			$gridData = $this->modifyGridDataForSave($gridData);
						
			$data["grid_data"] = $gridData;
		}
		
		$data = serialize($data);
	
		return($data);
	}
	
	
	
	private function a__________EXTERNAL_GETTERS___________(){}
	
	
	/**
	 * get grid data
	 */
	public function getGridDataForEditor($addAddonContent = false){
		
		$this->validateInited();
		
		try{
			
			if(empty($this->gridDataFull))
				$this->createGridDataFull($addAddonContent);
			
		}catch(Exception $e){
			
			HelperHtmlUC::outputException($e);
			
			return $this->gridData;
		}
		
				
		return($this->gridDataFull);
	}
	
	
	/**
	 * get grid data for front
	 */
	public function getRowsFront(){
		
		$this->validateInited();
		
		$rows = UniteFunctionsUC::getVal($this->gridData, "rows");
		
		if(empty($rows))
		    $rows = array();
		
		//UniteFunctionsUC::validateNotEmpty($rows, "Layout Rows");		
		//dmp($this->gridData); exit();
		
		return($rows);
	}
	
		
	
	/**
	 * get grid options - only those that different from default
	 */
	public function getGridOptionsDiff(){
		
		$defaultOptions = self::getGridDefaultOptions();
		
		$allOptions = $this->getAllGridOptions();
		
		$arrDiffOptions = UniteFunctionsUC::getDiffArrItems($allOptions, $defaultOptions);
		
		return($arrDiffOptions);
	}
	
	
	/**
	 * get title
	 */
	public function getTitle($specialChars = false){
		
		$this->validateInited();
		
		return($this->title);
	}
	
	/**
	 * get layout ID
	 */
	public function getID(){
		
		$this->validateInited();
		
		return($this->id);
	}
        
     /**
	 * get layout category info
	 */
    public function getCategory(){
		
		$this->validateInited();
        $category_info = array("layoutid" => $this->getID());
        $objCats = new UniteCreatorCategories();
            
       	 $cat_id = UniteFunctionsUC::getVal($this->record, "catid");
            		  
          if(empty($cat_id))
		  {
		    $category_info["name"] = "Uncategorized";
		    $category_info["id"] = 0;
		    
		  } else {
                $cat = $objCats->getCat($cat_id);
                $category_info["name"] = $cat["title"];
			 $category_info["id"] = $cat["id"];
            }
		return $category_info;
	}
	
	/**
	 * get category name
	 */
	public function getCategoryName(){
		
		$objCat = $this->getCategory();
		$catName = $objCat["name"];
		
		return($catName);
	}
	
	
	/**
	 * get record
	 */
	public function getRecord(){
		
		return($this->record);
	}
	
	
	/**
	 * get layout record for export
	 */
	public function getRecordForExport(){
		$this->validateInited();
		
		$record = array();
		$record["title"] = $this->title;
		
		$gridData = $this->modifyGridDataForSave($this->gridData);
		
		$this->data["grid_data"] = $gridData;
		$layoutData = serialize($this->data);
		$record["layout_data"] = $layoutData;
		
		return($record);
	}
	
	
	/**
	 * get shortcode
	 */
	public function getShortcode(){
		
		$title = $this->getTitle(true);
		$id = $this->id;
		
		$shortcode = GlobalsUC::$layoutShortcodeName." id={$id} title=\"{$title}\"";
		
		$shortcode = UniteProviderFunctionsUC::wrapShortcode($shortcode);
		
		return($shortcode);
	}
	
	
	/**
	 * get layout addon type
	 */
	public function getAddonType(){
		
		return($this->addonType);
	}
	
	
	/**
	 * get raw layout data
	 */
	public function getRawLayoutData(){
		$this->validateInited();
		
		$strLayoutData = $this->record["layout_data"];
		
		return($strLayoutData);
	}
	
	
	/**
	 * check if layout exists by title
	 */
	protected function isLayoutExistsByTitle($title){
		
		$title = $this->db->escape($title);
		
		$response = $this->db->fetch(GlobalsUC::$table_layouts, "title='{$title}'");
		
		$isExists = !empty($response);
		
		return($isExists);
	}
	
	
	/**
	 * get new layout title
	 */
	public function getNewLayoutTitle(){
		
		$titleBase = __("Page", ADDONLIBRARY_TEXTDOMAIN);
		$counter = 1;
				
		do{
			$title = $titleBase.$counter;			
			$found = $this->isLayoutExistsByTitle($title);
			$counter++;
		}while($found == true);
		
		return($title);
	}
	
	
	/**
	 * collect addon names
	 */
	private function modifyAddons_collectAddonNames($addonData){
	
		$name = UniteFunctionsUC::getVal($addonData, "name");
	
		$this->arrAddonNames[$name] = true;
		
		return(null);
	}
	
	
	/**
	 * get row settigns combined with grid options
	 */
	public function getRowSettingsCombined($row){
		
		$settings = UniteFunctionsUC::getVal($row, "settings");

		$allOptions = $this->getAllGridOptions();
		
		
		//add option values
		if(empty($allOptions))
			return($settings);
		
		if(empty($settings))
			return($allOptions);
		
		//combine both
					
		foreach($settings as $key=>$value){
			
			if($value !== "")
				continue;
			
			$globalValue = UniteFunctionsUC::getVal($allOptions, $key);
			if($globalValue !== "")
				$settings[$key] = $globalValue;
		}
		
		//get the rest options
		foreach($allOptions as $key=>$value){
			
			$localValue = UniteFunctionsUC::getVal($settings, $key);
			if($localValue !== "")
				$value = $localValue;
			
			$settings[$key] = $value;
		}
		
		
		return($settings);
	}
	
	
	private function a__________EXPORT_RELATED___________(){}
	
	
	/**
	 * map through all layout settings and modify if needed
	 */
	public function modifyLayoutElementsSettings($modifyFunc){
		$this->validateInited();
		if(is_array($modifyFunc) == false)
			UniteFunctionsUC::throwError("Wrong modify func");
		
		$this->gridData = $this->mapModifyLayoutDataSettings($this->gridData, $modifyFunc);
	}
		
	
	
	/**
	 * modify grid data by some function
	 */
	public function modifyGridDataAddons($modifyFunc){
		
		$this->validateInited();
		if(is_array($modifyFunc) == false)
			UniteFunctionsUC::throwError("Wrong modify func");
		
		$this->gridData = $this->mapModifyLayoutDataAddons($this->gridData, $modifyFunc);
		
	}
	
	
	/**
	 * get all layout addons without content
	 */
	public function getArrAddons(){
		
		$this->validateInited();
	
		$this->arrAddonNames = array();
		$this->mapModifyLayoutDataAddons($this->gridData, "modifyAddons_collectAddonNames");
		
		$arrAddons = array();
		
		foreach($this->arrAddonNames as $name=>$var){
			
			try{
								
				$objAddon = new UniteCreatorAddon();
				$objAddon->initByMixed($name, $this->addonType);
				$arrAddons[] = $objAddon;
				
			}catch(Exception $e){
				
			}
		}
		
		return($arrAddons);
	}
	
	/**
	 * clean layout data before save
	 * get array of data each time
	 */
	public function cleanLayoutDataBeforeExport($arrData){
		
		if(is_array($arrData) == false)
			return($arrData);
		
		$arrDataNew = array();
		foreach($arrData as $key=>$item){
			
			if(strpos($key, "_unite_selected_text") !== false)
				continue;
						
			$arrDataNew[$key] = $item;
		}
		
		
		return($arrDataNew);
	}
	
	/**
	 * clean layout settings before save or export from extra data
	 */
	public function cleanLayoutSettingsBeforeExport(){
		
		$cleanFunc = array($this, "cleanLayoutDataBeforeExport");
		
		$this->gridData = $this->mapModifyLayoutDataAll($this->gridData, $cleanFunc);
	}
	
	
	private function a__________EXTERNAL_SETTERS___________(){}
	
	
	/**
	 * modify grid data for save
	 */
	public function modifyGridDataForSave($gridData){
		
		$gridData = $this->mapModifyLayoutDataAddons($gridData, "modifyAddonDataForSave");
		
		return($gridData);
	}
	
	
	/**
	 * update layout in db
	 */
	public function createLayoutInDB($arrInsert){
		
		$objLayouts = new UniteCreatorLayouts();
		
		$maxOrder = $objLayouts->getMaxOrder();
		$arrInsert["ordering"] = $maxOrder+1;
		
		$id = $this->db->insert(GlobalsUC::$table_layouts, $arrInsert);
		
		return($id);
	}
	
	
	/**
	 * create layout from data
	 */
	public function create($data){
		
		$title = UniteFunctionsUC::getVal($data, "title");
		
		unset($data["title"]);
		unset($data["layoutid"]);
		UniteFunctionsUC::validateNotEmpty($title, HelperUC::getText("layout_title"));
		
		$arrInsert = array();
		$arrInsert["title"] = $title;
		$arrInsert["layout_data"] = $this->prepareDataForSave($data);
		
		$id = $this->createLayoutInDB($arrInsert);
		
		return($id);
	
	}
	
	
	/**
	 * update layout in db
	 */
	public function updateLayoutInDB($arrUpdate){
		$this->validateInited();
		
		$where = "id={$this->id}";
		
		$this->db->update(GlobalsUC::$table_layouts, $arrUpdate, $where);
	}
	
	/**
	 * update layout category
	 */
	public function updateCategory($catID){
		
		$this->validateInited();
		$catID = (int)$catID;
		
		$arrUpdate = array();
		$arrUpdate['catid'] = $catID;
		
		$this->updateLayoutInDB($arrUpdate);
	}
	
	
	/**
	 * update title
	 */
	public function updateTitle($title){
		
		$this->validateInited();
		
		$this->validateTitle($title);
		
		$arrUpdate = array();
		$arrUpdate["title"] = $title;
		
		$this->updateLayoutInDB($arrUpdate);
	}
	
	/**
	 * 
	 * update saved internal params in db
	 */
	private function updateInternalParamsInDB(){
		
		$jsonParams = json_encode($this->params);
		
		$arrUpdate = array();
		$arrUpdate["params"] = $jsonParams;
		
		$this->updateLayoutInDB($arrUpdate);
		
	}
	
	
	/**
	 * update param
	 */
	public function updateParam($paramName, $paramValue){
		$this->validateInited();
		
		$this->params[$paramName] = $paramValue;
		
		$this->updateInternalParamsInDB();
	}
	
	
	/**
	 * update layout params
	 */
	public function updateParams($arrParams, $isRewrite = false){
		
		$this->validateInited();
		if(empty($arrParams))
			return(false);
		
		if(is_array($arrParams) == false)
			return(false);
		
		if($isRewrite == true)
			$this->params = $arrParams;
		else
			$this->params = array_merge($this->params, $arrParams);
		
		$this->updateInternalParamsInDB();
	}
	
	
	/**
	 * get params
	 */
	public function getParams(){
		
		return($this->params);
	}
	
	
	/**
	 * get param
	 */
	public function getParam($paramName){
		
		$value = UniteFunctionsUC::getVal($this->params, $paramName);
		
		return($value);
	}
	
	
	/**
	 * update layout
	 */
	public function update($data){
	
		$this->validateInited();
	
		$title = UniteFunctionsUC::getVal($data, "title");
		$this->validateTitle($title);
		
		$arrUpdate = array();
		$arrUpdate["title"] = $title;
		$arrUpdate["layout_data"] = $this->prepareDataForSave($data);
				
		
		$this->updateLayoutInDB($arrUpdate);
		
	}
	
	/**
	 * update grid data in db
	 */
	public function updateGridData($gridData = null){
		
		if($gridData !== null)
			$this->gridData = $gridData;
		
		$this->data["grid_data"] = $this->gridData;
		
		$arrUpdate = array();
		$arrUpdate["layout_data"] = $this->prepareDataForSave($this->data);
		
		$this->updateLayoutInDB($arrUpdate);
	}
	
	
	/**
	 * delete layout
	 */
	public function delete(){
		$this->validateInited();
		
		$this->db->delete(GlobalsUC::$table_layouts, "id=".$this->id);
		
	}
	
	
	/**
	 * get new name
	 */
	private function getDuplicateTitle(){
		
		$objLayouts = new UniteCreatorLayouts();
		
		$suffixTitle = " - copy";
	
		$title = $this->getTitle();
				
		$newTitle = $title.$suffixTitle;
		$isExists = $objLayouts->isLayoutExistsByTitle($newTitle);
		
		$num = 1;
		$limit = 1;
		while($isExists == true && $limit < 10){
			$limit++;
			$num++;
			$suffixTitle = " - copy".$num;
			$newTitle = $title.$suffixTitle;
			$isExists = $objLayouts->isLayoutExistsByTitle($newTitle);
		}
		
		
		return($newTitle);
	}
	
	
	/**
	 * duplicate layout
	 */
	public function duplicate(){
		
		$this->validateInited();
		
		$layouts = new UniteCreatorLayouts();
				
		$newTitle = $this->getDuplicateTitle();
		
		$layouts->shiftOrder($this->ordering);
		
		$newOrder = $this->ordering+1;
		
		//insert a new gallery
		$sqlSelect = "select ".self::FIELDS_LAYOUTS." from ".GlobalsUC::$table_layouts." where id={$this->id}";
		$sqlInsert = "insert into ".GlobalsUC::$table_layouts." (".self::FIELDS_LAYOUTS.") ($sqlSelect)";
		
		$this->db->runSql($sqlInsert);
		$lastID = $this->db->getLastInsertID();
		UniteFunctionsUC::validateNotEmpty($lastID);
		
		//update the new layout with the title and the name values
		$arrUpdate = array();
		$arrUpdate["title"] = $newTitle;
		$arrUpdate["ordering"] = $newOrder;
		
		$this->db->update(GlobalsUC::$table_layouts, $arrUpdate, array("id"=>$lastID));
		
		return($lastID);
		
	}
	
	
}