<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorParamsProcessorWork{
	
	private $addon;
	private $processType;
	
	const PROCESS_TYPE_CONFIG = "config";	//process for output the config
	const PROCESS_TYPE_OUTPUT = "output";	//process for output
	const PROCESS_TYPE_OUTPUT_BACK = "output_back";	//process for backend live output
	const PROCESS_TYPE_SAVE = "save";		//process for save
	
	
	/**
	 * validate that the processor inited
	 */
	private function validateInited(){
		
		if(empty($this->addon))
			UniteFunctionsUC::throwError("The params processor is not inited");
		
	}
	
	/**
	 * validate that process type exists
	 */
	private function validateProcessTypeInited(){
		
		if(empty($this->processType))
			UniteFunctionsUC::throwError("The process type is not inited");
		
		self::validateProcessType($this->processType);
	}
	
	
	private function _____________GENERAL_____________(){}
	
	/**
	 * validate process type
	 */
	public static function validateProcessType($type){
		UniteFunctionsUC::validateValueInArray($type, "process type",array(
				self::PROCESS_TYPE_CONFIG,
				self::PROCESS_TYPE_SAVE,
				self::PROCESS_TYPE_OUTPUT,
				self::PROCESS_TYPE_OUTPUT_BACK,
		));
	}
	
	
	/**
	 * convert from url assets
	 */
	private function convertFromUrlAssets($value){
		
		$urlAssets = $this->addon->getUrlAssets();
	
		if(!empty($urlAssets))
			$value = HelperUC::convertFromUrlAssets($value, $urlAssets);
	
		return($value);
	}
	
	/**
	 * process param value, by param type
	 * if it's url, convert to full
	 */
	private function convertValueByType($value, $type){
		
		if(empty($value))
			return($value);
		
		$value = $this->convertFromUrlAssets($value);
		
		switch($type){
			case "uc_image":
			case "uc_mp3":
				$value = HelperUC::URLtoFull($value);
				break;
		}
	
		return($value);
	}
	
	
	/**
	 * make sure the value is always taken from the options
	 */
	private function convertValueFromOptions($value, $options, $defaultValue){
	
		if(is_array($options) == false)
			return($value);
	
		if(empty($options))
			return($value);
	
		$key = array_search($value, $options, true);
		if($key !== false)
			return($value);
	
		//------- not found
		//in case of false / nothing
		if(empty($value)){
			$key = array_search("false", $options, true);
			if($key !== false)
				return("false");
		}
	
		//if still not found, return default value
		return($defaultValue);
	}
	
	
	/**
	 * construct the object
	 */
	public function init($addon){
	
		//for auto complete
		//$this->addon = new UniteCreatorAddon();
	
		$this->addon = $addon;
	}
	
	
	/**
	 * set process type
	 */
	public function setProcessType($type){
		
		self::validateProcessType($type);
		$this->processType = $type;
	}
	
	
	/**
	* return if it's output process type
	 */
	public function isOutputProcessType($processType){
		
		if($processType == self::PROCESS_TYPE_OUTPUT || $processType == self::PROCESS_TYPE_OUTPUT_BACK)
			return(true);
			
		return(false);
	}
	
		
	private function a_____________FONTS______________(){}
	
	
	/**
	 * process the font
	 */
	public function processFont($value, $arrFont, $isReturnCss = false, $cssSelector = null, $fontsKey){
		
		//$this->validateInited();
		
		$arrStyle = array();
		$spanClass = "";
		$counter = 0;
		$addStyles = "";
		$arrGoogleFonts = null;
		$cssMobileSize = "";
		$fontTemplate = null;
		
		if(empty($arrFont))
			$arrFont = array();
		
		//on production don't return empty span
		if($this->processType == self::PROCESS_TYPE_OUTPUT && empty($arrFont) && $isReturnCss == false)
			return($value);
		
		foreach($arrFont as $styleName => $styleValue){
			
			$styleValue = trim($styleValue);
	
			if(empty($styleValue))
				continue;
			
			if($styleValue == "not_chosen")
				continue;
			
			switch($styleName){
				case "font-family":
					
					if(strpos($styleValue, " ") !== false && strpos($styleValue, ",") === false)
						$arrStyle[$styleName] = "'$styleValue'";
					else
						$arrStyle[$styleName] = "$styleValue";
					
					//check google fonts
					if(empty($arrGoogleFonts)){
						$arrFontsPanelData = HelperUC::getFontPanelData();
						$arrGoogleFonts = $arrFontsPanelData["arrGoogleFonts"];
					}
					
					if(isset($arrGoogleFonts[$styleValue])){
						
						$urlGoogleFont = "https://fonts.googleapis.com/css?family=".$styleValue;
						
						if(!empty($this->addon))
							$this->addon->addCssInclude($urlGoogleFont);
						else{
							$handle = HelperUC::getUrlHandle($urlGoogleFont);
							HelperUC::addStyleAbsoluteUrl($urlGoogleFont, $handle);
						}
						
					}
				break;
				case "font-weight":
				case "font-size":
				case "line-height":
				case "text-decoration":
				case "color":
				case "font-style":
					$arrStyle[$styleName] = $styleValue;
				break;
				case "mobile-size":
					
					//generate id
					if($isReturnCss == true){
						$spanClass = $cssSelector;
						$mobileSizeClass = $cssSelector;
					}
					else{	
						$counter++;
						$spanClass = "uc-style-".$counter.UniteFunctionsUC::getRandomString(10, true);
						$mobileSizeClass = ".".$spanClass;
					}
					$cssMobileSize = "@media (max-width:480px){{$mobileSizeClass}{font-size:{$styleValue} !important;}}";
					
					if($isReturnCss == false)
						$this->addon->addToCSS($cssMobileSize);
						
				break;
				case "custom":
					$addStyles = $styleValue;
				break;
				case "template":
					$fontTemplate = $styleValue;
				break;
				default:
					UniteFunctionsUC::throwError("Wrong font style: $styleName");
				break;
			}
		}
	
		
		if($isReturnCss == true){
			$css = UniteFunctionsUC::arrStyleToStrInlineCss($arrStyle, $addStyles, false);
			
			if(!empty($css))
				$css = $cssSelector."{".$css."}";
			
			if(!empty($cssMobileSize))
				$css .= "\n".$cssMobileSize;
			
			return($css);
		}
		
		
		$style = "";
		if(!empty($arrStyle) || !empty($addStyles))
			$style = UniteFunctionsUC::arrStyleToStrInlineCss($arrStyle, $addStyles);
		
		$htmlAdd = "";
		$arrClasses = array();
		if(!empty($spanClass))
			$arrClasses[] = $spanClass;
		
		//if linked to font template, eliminate the style, and add template class		
		if(!empty($fontTemplate))
			$arrClasses[] = 'uc-page-font-'.$fontTemplate;
							
		if($this->processType == self::PROCESS_TYPE_OUTPUT_BACK){
			$arrClasses[] = "uc-font-editable-field";
			$htmlAdd .= " data-uc_font_field=\"{$fontsKey}\" ";
		}
		
		if(!empty($arrClasses)){
			$strClasses = implode(" ", $arrClasses);
			$htmlAdd .= " class=\"{$strClasses}\"";
		}
		
		$value = "<span {$htmlAdd} {$style}>$value</span>";
		return($value);
	}
	
	
	/**
	 * process fonts, type can be main or items
	 */
	private function processFonts($arrValues, $type){
		
		$this->validateProcessTypeInited();
		
		$arrFonts = $this->addon->getArrFonts();
		
		$arrFontEnabledKeys = $this->getAllParamsNamesForFonts();
		
		
		if(empty($arrValues))
			return($arrValues);
		
		switch($type){
			case "main":
				$prefix = "";
				break;
			case "items":
				$prefix = "uc_items_attribute_";
				break;
			default:
				UniteFunctionsUC::throwError("Wrong fonts type: $type");
			break;
		}
		
		
		foreach($arrValues as $key=>$value){
			
			if(empty($value))
				continue;
				
				
			//for items like posts
			if(is_array($value)){
								
				foreach($value as $itemIndex => $item){
					
					if(!is_array($item))
						continue;
					
					foreach($item as $itemKey => $itemValue){
						$fontsKey = $prefix.$key.".$itemKey";
						$arrFont = UniteFunctionsUC::getVal($arrFonts, $fontsKey);
						$isFontEnabled = isset($arrFontEnabledKeys[$fontsKey]);
						
						if(!empty($arrFont) || $isFontEnabled)
							$arrValues[$key][$itemIndex][$itemKey] = $this->processFont($itemValue, $arrFont, false, null, $fontsKey);
					}
				}
				
				continue;
			}
				
			$fontsKey = $prefix.$key;
			$arrFont = UniteFunctionsUC::getVal($arrFonts, $fontsKey);
			
			$isFontEnabled = isset($arrFontEnabledKeys[$fontsKey]);
			if(!empty($arrFont) || $isFontEnabled)
				$arrValues[$key] = $this->processFont($value, $arrFont, false, null, $fontsKey);
			
		}
		
		
		return($arrValues);
	}
	
	
	/**
	 * return if fonts panel enabled for this addon
	 */
	public function isFontsPanelEnabled(){
		
		$this->validateInited();
		
		$arrParams = $this->addon->getParams();
		
		$hasItems = $this->addon->isHasItems();
				
		if($hasItems == true){
			$arrParamsItems = $this->addon->getParamsItems();
			$arrParams = array_merge($arrParams, $arrParamsItems);			
		}
		
		$numValidParams = 0;
		foreach($arrParams as $param){
			$type = UniteFunctionsUC::getVal($param, "type");
			
			switch($type){
				case UniteCreatorDialogParam::PARAM_EDITOR:
				case UniteCreatorDialogParam::PARAM_TEXTAREA:
				case UniteCreatorDialogParam::PARAM_TEXTFIELD:
				case UniteCreatorDialogParam::PARAM_DROPDOWN:
				case UniteCreatorDialogParam::PARAM_FONT_OVERRIDE:
				case UniteCreatorDialogParam::PARAM_POSTS_LIST:
				case UniteCreatorDialogParam::PARAM_INSTAGRAM:
					$numValidParams++;
				break;
			}
			
		}
		
		
		if($numValidParams == 0)
			return(false);
		else
			return(true);
	}
	
	
	/**
	 * get main params names
	 */
	private function getParamsNamesForFonts($paramsType){
		
		switch($paramsType){
			case "main":
				$arrParams = $this->addon->getParams();
			break;
			case "items":
				if($this->addon->isHasItems() == false)
					return(array());
				
				$arrParams = $this->addon->getParamsItems();
			break;
			default:
				UniteFunctionsUC::throwError("Wrong params type: $paramsType");
			break;
		}
		
		
		$arrNames = array();
		foreach($arrParams as $param){
			
			$type = UniteFunctionsUC::getVal($param, "type");
						
			$name = UniteFunctionsUC::getVal($param, "name");
			$title = UniteFunctionsUC::getVal($param, "title");
			
			if($paramsType == "items"){
				$name = "uc_items_attribute_".$name;
				$title = __("Items", ADDONLIBRARY_TEXTDOMAIN)." => ".$title;
			}
			
			$fontEditable = UniteFunctionsUC::getVal($param, "font_editable");
			$fontEditable = UniteFunctionsUC::strToBool($fontEditable);
			
			switch($type){
				case UniteCreatorDialogParam::PARAM_POSTS_LIST:
					if($fontEditable == true){
						$name = "{$name}.title";
						$arrNames[$name] = $title." => Title";
					}
				break;
				case UniteCreatorDialogParam::PARAM_INSTAGRAM:
					
					if($fontEditable == true){
						$arrNames["{$name}.name"] = $title." => Name";
						$arrNames["{$name}.biography"] = $title." => Biography";
						$arrNames["{$name}.item.caption"] = $title." => Item => Caption";
					}
					
				break;
				case UniteCreatorDialogParam::PARAM_FONT_OVERRIDE:
					if($paramsType == "items")
						return(false);
					
					$arrNames["uc_font_override_".$name] = $title;
				break;
				default:
					
					if($fontEditable == true)
						$arrNames[$name] = $title;
				break;
			}
			
		}
		
		return($arrNames);
	}
	
	
	
	/**
	 * get all params names for font panel
	 */
	public function getAllParamsNamesForFonts(){
		
		$arrParamsNamesMain = $this->getParamsNamesForFonts("main");
		
		$arrParamsNamesItems = $this->getParamsNamesForFonts("items");
		$arrParamsNames = array_merge($arrParamsNamesMain, $arrParamsNamesItems);
		
		return($arrParamsNames);
	}
	
	
	
	
	
	private function _____________POST______________(){}
	
	
	/**
	 * get post data
	 */
	protected function getPostData($postID){
		dmp("getPostData: function for override");exit();
	}
	
	
	/**
	 * process image param value, add to data
	 */
	private function getProcessedParamsValue_post($data, $value, $param, $processType){
		
		self::validateProcessType($processType);
		
		$postID = $value;
		if(empty($postID))
			return($data);
		
		$name = UniteFunctionsUC::getVal($param, "name");
		
		switch($processType){
			case self::PROCESS_TYPE_CONFIG:		//get additional post title
				
				$postTitle = UniteProviderFunctionsUC::getPostTitleByID($postID);
				$data[$name] = $postID;
				
				if(!empty($postTitle))
					$data[$name."_post_title"] = $postTitle;
				
			break;
			case self::PROCESS_TYPE_SAVE:
				$data[$name] = $postID;
				unset($data[$name."_post_title"]);
			break;
			case self::PROCESS_TYPE_OUTPUT:
			case self::PROCESS_TYPE_OUTPUT_BACK:
				$data[$name] = $this->getPostData($postID);
			break;
		}
				
		return($data);
	}
	
	
	/**
	 * process image param value, add to data
	 */
	private function getProcessedParamsValue_content($data, $value, $param, $processType){
		
		self::validateProcessType($processType);
		
		
		return($data);
	}
	
	private function _____________FORM______________(){}
	
	
	/**
	 * process image param value, add to data
	 */
	private function getProcessedParamsValue_form($data, $value, $param, $processType){
		
		self::validateProcessType($processType);
		
		$objForm = new UniteCreatorForm();
		$objForm->setAddon($this->addon);
		
		switch($processType){
			case self::PROCESS_TYPE_OUTPUT:
			case self::PROCESS_TYPE_OUTPUT_BACK:
				
				$paramName = UniteFunctionsUC::getVal($param, "name");
				$data = $objForm->getFormOutputData($data, $paramName, $value);
				
				//$data[$name] = $this->getPostData($postID);
			break;
		}
				
		return($data);
	}
		
	
	
	
	private function _____________IMAGE______________(){}
	
	
	/**
	 * add other image thumbs based of the platform
	 */
	protected function addOtherImageThumbs($data, $name, $value){
	
		return($data);
	}
	
	/**
	 * get all image related fields to data, but value
	 * create param with full fields
	 */
	protected function getImageFields($data, $name, $value){
		
		if(empty($data))
			$data = array();
		
		//get by param
		$param = array();
		$param["name"] = $name;
		$param["value"] = $value;
		$param["add_thumb"] = true;
		$param["add_thumb_large"] = true;
		
		$data[$name] = $value;
		$data = $this->getProcessedParamsValue_image($data, $value, $param);
		
		return($data);
	}
	
	/**
	 * process image param value, add to data
	 * @param unknown_type $param
	 */
	protected function getProcessedParamsValue_image($data, $value, $param){
		
		$name = $param["name"];
		
		$urlImage = $value;		//in case that the value is image id
		if(is_numeric($value)){
			$urlImage = UniteProviderFunctionsUC::getImageUrlFromImageID($value);
			$data[$name] = $urlImage;
		}else{
			
			$value = HelperUC::URLtoFull($value);
			$data[$name] = $value;
		}
		$addThumb = UniteFunctionsUC::getVal($param, "add_thumb");
		$addThumb = UniteFunctionsUC::strToBool($addThumb);
	
		$addThumbLarge = UniteFunctionsUC::getVal($param, "add_thumb_large");
		$addThumbLarge = UniteFunctionsUC::strToBool($addThumbLarge);
	
		if($addThumb == true){
	
			$urlThumb = HelperUC::$operations->getThumbURLFromImageUrl($value, null, GlobalsUC::THUMB_SIZE_NORMAL);
			$urlThumb = HelperUC::URLtoFull($urlThumb);
			
			$data[$name."_thumb"] = $urlThumb;
		}
	
		if($addThumbLarge == true){
	
			$urlThumb = HelperUC::$operations->getThumbURLFromImageUrl($value, null, GlobalsUC::THUMB_SIZE_LARGE);
			$urlThumb = HelperUC::URLtoFull($urlThumb);
			
			$data[$name."_thumb_large"] = $urlThumb;
		}
	
		$data = $this->addOtherImageThumbs($data, $name, $value);
	
		return($data);
	}
	
	
	private function ____________INSTAGRAM______________(){}
	
	
	/**
	 * get instagram data
	 */
	private function getInstagramData($value, $name, $param){
		
		try{
			if(empty($value))
				$value = UniteCreatorSettingsWork::INSTAGRAM_DEFAULT_VALUE;
			
			$maxItems = UniteFunctionsUC::getVal($param, "max_items");
			$services = new UniteServicesUC();
			$data = $services->getInstagramData($value, $maxItems);
			
			return($data);
			
		}catch(Exception $e){
			
			return(null);
		}
		
	}

	/**
	 * get google map output
	 */
	private function getGoogleMapOutput($value, $name, $param){
		
		$filepathPickerObject = GlobalsUC::$pathViewsObjects."mappicker_view.class.php";
		require_once $filepathPickerObject;
		$objView = new UniteCreatorMappickerView();
		
		if(!empty($value))
			$objView->setData($value);
		
		$html = $objView->getHtmlClientSide($value);
		
		return($html);
	}
	
	private function ____________VARIABLES______________(){}
	
	
	/**
	 * process items variables, based on variable type and item content
	 */
	private function getItemsVariablesProcessed($arrItem, $index, $numItems){
	
		$arrVars = $this->addon->getVariablesItem();
		$arrVarData = array();
	
		//get variables output object
		$arrParams = $this->getProcessedMainParamsValues(self::PROCESS_TYPE_SAVE);
		
		$objVarOutput = new UniteCreatorVariablesOutput();
		$objVarOutput->init($arrParams);
	
	
		foreach($arrVars as $var){
			$name = UniteFunctionsUC::getVal($var, "name");
			UniteFunctionsUC::validateNotEmpty($name, "variable name");
	
			$content = $objVarOutput->getItemVarContent($var, $arrItem, $index, $numItems);
	
			$arrVarData[$name] = $content;
		}
	
		return($arrVarData);
	}
	
	
	/**
	 * get main processed variables
	 */
	private function getMainVariablesProcessed($arrParams){
	
		//get variables
		$objVariablesOutput = new UniteCreatorVariablesOutput();
		$objVariablesOutput->init($arrParams);
	
		$arrVars = $this->addon->getVariablesMain();
	
		$arrOutput = array();
	
		foreach($arrVars as $var){
	
			$name = UniteFunctionsUC::getVal($var, "name");
			$content = $objVariablesOutput->getMainVarContent($var);
			$arrOutput[$name] = $content;
		}
	
		return($arrOutput);
	}
	
	private function ________PARAMS_OUTPUT__________(){}
	
	/**
	 * process params - add params by type (like image base)
	 */
	public function initProcessParams($arrParams){
	
		$this->validateInited();
	
		if(empty($arrParams))
			return(array());
	
		$arrParamsNew = array();
		foreach($arrParams as $param){
	
			$type = UniteFunctionsUC::getVal($param, "type");
			switch($type){
				case "uc_imagebase":
					$settings = new UniteCreatorSettings();
					$settings->addImageBaseSettings();
					$arrParamsAdd = $settings->getSettingsCreatorFormat();
					foreach($arrParamsAdd as $addParam)
						$arrParamsNew[] = $addParam;
					break;
				default:
					$arrParamsNew[] = $param;
				break;
			}
	
		}
	
		return($arrParamsNew);
	}
	
	
	/**
	 * process params for output it to settings html
	 * update params items for output
	 */
	public function processParamsForOutput($arrParams){
		
		$this->validateInited();
	
		if(is_array($arrParams) == false)
			UniteFunctionsUC::throwError("objParams should be array");
	
		foreach($arrParams as $key=>$param){
	
			$type = UniteFunctionsUC::getVal($param, "type");
	
			if(isset($param["value"]))
				$param["value"] = $this->convertValueByType($param["value"], $type);
	
			if(isset($param["default_value"]))
				$param["default_value"] = $this->convertValueByType($param["default_value"], $type);
	
			//make sure that the value is part of the options
			if(isset($param["value"]) && isset($param["default_value"]) && isset($param["options"]) && !empty($param["options"]) )
				$param["value"] = $this->convertValueFromOptions($param["value"], $param["options"], $param["default_value"]);
	
			$arrParams[$key] = $param;
		}
		
		
		return($arrParams);
	}
	
		
	
	private function _____________VALUES_OUTPUT______________(){}
	
	
	/**
	 * get processe param data, function with override
	 */
	protected function getProcessedParamData($data, $value, $param, $processType){
						
		$type = UniteFunctionsUC::getVal($param, "type");
		$name = UniteFunctionsUC::getVal($param, "name");
		
		$isOutputProcessType = $this->isOutputProcessType($processType);
		
		//special params - all types
		switch($type){
			case UniteCreatorDialogParam::PARAM_IMAGE:
				$data = $this->getProcessedParamsValue_image($data, $value, $param);
			break;
			case UniteCreatorDialogParam::PARAM_POST:
				$data = $this->getProcessedParamsValue_post($data, $value, $param, $processType);
			break;
			case UniteCreatorDialogParam::PARAM_CONTENT:
				$data = $this->getProcessedParamsValue_content($data, $value, $param, $processType);
			break;
			case UniteCreatorDialogParam::PARAM_FORM:
				$data = $this->getProcessedParamsValue_form($data, $value, $param, $processType);
			break;
		}
		
		//process output type only
		
		if($isOutputProcessType == false)
			return($data);
				
		switch($type){
			case UniteCreatorDialogParam::PARAM_INSTAGRAM:
				
				$data[$name] = $this->getInstagramData($value, $name, $param);
				
			break;
			case UniteCreatorDialogParam::PARAM_MAP:
				$data[$name] = $this->getGoogleMapOutput($value, $name, $param);
			break;
		}
		
		return($data);
	}
	
	
	
	/**
	 * get processed params
	 * @param $objParams
	 */
	public function getProcessedParamsValues($arrParams, $processType, $filterType = null){
	    
		self::validateProcessType($processType);
		
		$arrParams = $this->processParamsForOutput($arrParams);
		
		$data = array();
	       
		foreach($arrParams as $param){
	
			$type = UniteFunctionsUC::getVal($param, "type");
	
			if(!empty($filterType)){
				if($type != $filterType)
					continue;
			}
			
			$name = UniteFunctionsUC::getVal($param, "name");
	
			$defaultValue = UniteFunctionsUC::getVal($param, "default_value");
			$value = $defaultValue;
			if(array_key_exists("value", $param))
				$value = UniteFunctionsUC::getVal($param, "value");
	
			$value = $this->convertValueByType($value, $type);
	
			if(empty($name))
				continue;
	
			if(isset($data[$name]))
				continue;
	
			if($type != "imagebase_fields")
				$data[$name] = $value;
			
			$data = $this->getProcessedParamData($data, $value, $param, $processType);
			
		}
		
		return($data);
	}
	
	
	/**
	 * get main params processed, for output
	 */
	public function getProcessedMainParamsValues($processType){
		
		$this->validateInited();
		
		self::validateProcessType($processType);
		
		$this->setProcessType($processType);	//save it for fonts
		
		$objParams = $this->addon->getParams();
		
		$arrParams = $this->getProcessedParamsValues($objParams, $processType);
		
		$arrVars = $this->getMainVariablesProcessed($arrParams);
		
		if($this->isOutputProcessType($processType) == true)
			$arrParams = $this->processFonts($arrParams, "main");
		
		$arrParams = array_merge($arrParams, $arrVars);
		
		
		return($arrParams);
	}
	
	/**
	 * modify items data, add "item" to array
	 */
	protected function normalizeItemsData($arrItems){
		
		if(empty($arrItems))
			return(array());
		
		foreach($arrItems as $key=>$item){
				$arrItems[$key] = array("item"=>$item);
		}
		
		return($arrItems);
	}
	
	
	/**
	 * get item data
	 */
	public function getProcessedItemsData($arrItems, $processType, $forTemplate = true, $filterType = null){
	   	
		$this->validateInited();
		self::validateProcessType($processType);
		
		$this->setProcessType($processType);
		
		if(empty($arrItems))
			return(array());
		
				
		//process form items
		$itemsType = $this->addon->getItemsType();
		if($itemsType == UniteCreatorAddon::ITEMS_TYPE_FORM){
			
			$objForm = new UniteCreatorForm();
			$objForm->setAddon($this->addon);
			
			if($this->isOutputProcessType($processType)){
				
				$arrItems = $objForm->processFormItemsForOutput($arrItems);
				return($arrItems);
			}else{
				
				//don't process for config
				//$arrItems = $this->normalizeItemsData($arrItems);
				//dmp($arrItems);
				
				return($arrItems);
			}
		}
		
		
		//regular process
		$operations = new UCOperations();
		
		$arrItemsNew = array();
		$arrItemParams = $this->addon->getParamsItems();
		$arrItemParams = $this->initProcessParams($arrItemParams);
		
		$numItems = count($arrItems);
	
		foreach($arrItems as $index => $arrItemValues){
	
			$arrParamsNew = $this->addon->setParamsValuesItems($arrItemValues, $arrItemParams);
	
			$item = $this->getProcessedParamsValues($arrParamsNew, $processType, $filterType);
			
			if($this->isOutputProcessType($processType) == true)
				$item = $this->processFonts($item, "items");
			
			//in case of filter it's enought
			if(!empty($filterType)){
	
				$arrItemsNew[] = $item;
				continue;
			}

			//add values by items type
			$itemsType = $this->addon->getItemsType();
	       
			switch($itemsType){
				case UniteCreatorAddon::ITEMS_TYPE_IMAGE:
					//add thumb
					$urlImage = UniteFunctionsUC::getVal($item, "image");
					
					try{
						$urlThumb = $operations->createThumbs($urlImage);
						$urlThumb = HelperUC::URLtoFull($urlThumb);
					}catch(Exception $e){
						$urlThumb = "";
					}
					
					$item["thumb"] = $urlThumb;
				break;
			}
	
			//add item variables
			$arrVarsData = $this->getItemsVariablesProcessed($item, $index, $numItems);
			$item = array_merge($item, $arrVarsData);
			
			if($forTemplate == true)
				$arrItemsNew[] = array("item"=>$item);
			else
				$arrItemsNew[] = $item;
		}
	
		
		return($arrItemsNew);
	}
	
	
	/**
	 * get array param values, for special params
	 */
	private function getArrayParamValue($arrValues, $paramName, $value){
		
            $paramArrValues = array();
            $paramArrValues[$paramName] = $value;
            
            if(empty($arrValues))
            	$arrValues = array();
            		            
            foreach($arrValues as $key=>$value){
                if(strpos($key, $paramName."_") === 0)
                    $paramArrValues[$key] = $value;
            }
            
            $value = $paramArrValues;
		
           return($value);
	}
	
	
	/**
	 * return if param value is array
	 */
	protected function isParamValueIsArray($paramType){
		
		switch($paramType){
			case UniteCreatorDialogParam::PARAM_FORM:
				
				return(true);
			break;
		}
		
		return(false);
	}
	
	
	/**
	 * get param value, function for override, by type
	 */
	public function getSpecialParamValue($paramType, $paramName, $value, $arrValues){
	    
		$isArray = $this->isParamValueIsArray($paramType);
	    
		if($isArray == true)
			$value = $this->getArrayParamValue($arrValues, $paramName, $value);
		
	    return($value);
	}
	
	
	
	
	
}