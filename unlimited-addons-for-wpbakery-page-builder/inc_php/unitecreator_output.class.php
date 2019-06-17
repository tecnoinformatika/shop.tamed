<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorOutputWork extends HtmlOutputBaseUC{
	
	private static $serial = 0;
	
	const TEMPLATE_HTML = "html";
	const TEMPLATE_CSS = "css";
	const TEMPLATE_CSS_ITEM = "css_item";
	const TEMPLATE_JS = "js";
	const TEMPLATE_HTML_ITEM = "item";
	const TEMPLATE_HTML_ITEM2 = "item2";
	
	private $addon;
	private $isInited = false;
	private $objTemplate;
	private $isItemsExists = false;
	private $itemsType = null;
	private $paramsCache = null;
	private $cacheConstants = null;
	private $processType = null;
	private $generatedID = null;
	
	private static $arrUrlCacheCss = array();
	private static $arrHandleCacheCss = array();
	
	private static $arrUrlCacheJs = array();
	private static $arrHandleCacheJs = array();
	
	/**
	 * construct
	 */
	public function __construct(){
		$this->addon = new UniteCreatorAddon();
		$this->objTemplate = new UniteCreatorTemplateEngine();
		$this->processType = UniteCreatorParamsProcessor::PROCESS_TYPE_OUTPUT;
		
	}
	
	
	/**
	* set output type
	 */
	public function setProcessType($type){
		
		UniteCreatorParamsProcessor::validateProcessType($type);
		
		$this->processType = $type;
		
	}
	
	/**
	 * validate inited
	 */
	private function validateInited(){
		if($this->isInited == false)
			UniteFunctionsUC::throwError("Output error: addon not inited");
		
	}
	
	private function a___________INCLUDES_________(){}
	
	
	/**
	 * cache include
	 */
	private function cacheInclude($url, $handle, $type){
		
		if($type == "css"){	  //cache css
			
			self::$arrUrlCacheCss[$url] = true;
			self::$arrHandleCacheCss[$handle] = true;
			
		}else{
				//cache js
				
			self::$arrUrlCacheJs[$url] = true;
			self::$arrHandleCacheJs[$handle] = true;
		
		}
		
	}
	
	/**
	 * check that the include located in cache
	 */
	private function isIncludeInCache($url, $handle, $type){
		
		if(empty($url) || empty($handle))
			return(false);
		
		if($type == "css"){
			
			if(isset(self::$arrUrlCacheCss[$url]))
				return(true);
			
			if(isset(self::$arrHandleCacheCss[$handle]))
				return(true);
			
		}else{	//js
			
			if(isset(self::$arrUrlCacheJs[$url]))
				return(true);
			
			if(isset(self::$arrHandleCacheJs[$handle]))
				return(true);
			
		}
		
		return(false);
	}
	
	
	
	/**
	 * check include condition
	 * return true  to include and false to not include
	 */
	private function checkIncludeCondition($condition){
		if(empty($condition))
			return(true);
		
		if(!is_array($condition))
			return(true);
		
		$name = UniteFunctionsUC::getVal($condition, "name");
		$value = UniteFunctionsUC::getVal($condition, "value");

		if(empty($name))
			return(true);

		if($name == "never_include")
			return(false);
		
		$params = $this->getAddonParams();
		
		if(array_key_exists($name, $params) == false)
			return(true);
		
		$paramValue = $params[$name];
			
		if($paramValue === $value)
			return(true);
		else
			return(false);
	}
	
	
	
	
	/**
	 * process includes list, get array("url", type)
	 */
	private function processIncludesList($arrIncludes, $type){
		
		$arrIncludesProcessed = array();
		
		foreach($arrIncludes as $handle => $include){
			$urlInclude = $include;
			
			
			if(is_array($include)){
				$urlInclude = UniteFunctionsUC::getVal($include, "url");
				$condition = UniteFunctionsUC::getVal($include, "condition");
				$isIncludeByCondition = $this->checkIncludeCondition($condition);
				
				if($isIncludeByCondition == false)
					continue;
			}
		
			if(is_numeric($handle) || empty($handle)){
				$addonName = $this->addon->getName();
				$handle = HelperUC::getUrlHandle($urlInclude, $addonName);
			}
			
			$urlInclude = HelperUC::urlToSSLCheck($urlInclude);
			
			$arrIncludeNew = array();
			$arrIncludeNew["url"] = $urlInclude;
			$arrIncludeNew["type"] = $type;
			
			if(!empty($handle))
				$arrIncludeNew["handle"] = $handle;
			
			$arrIncludesProcessed[] = $arrIncludeNew;
			
		}
		
		return($arrIncludesProcessed);
	}
	
	
	/**
	 * get processed includes list
	 * includes type = js / css / all
	 */
	public function getProcessedIncludes($includeLibraries = false, $processProviderLibrary = false, $includesType = "all"){
		
		$this->validateInited();
		
		//get list of js and css
		$arrLibJs = array();
		$arrLibCss = array();
		
		if($includeLibraries == true){
			
			//get all libraries without provider process
			$arrLibraries = $this->addon->getArrLibraryIncludesUrls($processProviderLibrary);
		}
		
		
		$arrIncludesJS = array();
		$arrIncludesCss = array();
		
		//get js
		if($includesType != "css"){
			
			if($includeLibraries)
				$arrLibJs = $arrLibraries["js"];
			
			$arrIncludesJS = $this->addon->getJSIncludes();
			$arrIncludesJS = array_merge($arrLibJs, $arrIncludesJS);
			$arrIncludesJS = $this->processIncludesList($arrIncludesJS, "js");
			
		}
		
		
		//get css
		if($includesType != "js"){
			if($includeLibraries)
				$arrLibCss = $arrLibraries["css"];
			
			$arrIncludesCss = $this->addon->getCSSIncludes();
			$arrIncludesCss = array_merge($arrLibCss, $arrIncludesCss);
			$arrIncludesCss = $this->processIncludesList($arrIncludesCss, "css");
		}
		
		$arrProcessedIncludes = array_merge($arrIncludesJS, $arrIncludesCss);
		
		return($arrProcessedIncludes);
	}
	
	
	/**
	 * get includes html
	 */
	private function getHtmlIncludes($arrIncludes = null, $filterType = null){
		
		$this->validateInited();
		
		if(empty($arrIncludes))
			return("");
		
		$addonName = $this->addon->getName();
		
		$html = "";
		
		foreach($arrIncludes as $include){
			
			$type = $include["type"];
			
			//filter
			if($filterType == "js" && $type != "js")
				continue;

			if($filterType == "css" && $type != "css")
				continue;
			
			$url = $include["url"];
			$handle = UniteFunctionsUC::getVal($include, "handle");
			
			if(empty($handle))
				$handle = HelperUC::getUrlHandle($url, $addonName);
			
			$isInCache = $this->isIncludeInCache($url, $handle, $type);
			if($isInCache == true)
				continue;
			
			$this->cacheInclude($url, $handle, $type);
			
			switch($type){
				case "js":
					$html .= self::TAB2."<script type='text/javascript' src='{$url}'></script>".self::BR;
					break;
				case "css":
					$cssID = "{$handle}-css";
					$html .= self::TAB2."<link id='{$cssID}' href='{$url}' type='text/css' rel='stylesheet' >".self::BR;
					break;
				default:
					UniteFunctionsUC::throwError("Wrong include type: {$type} ");
				break;
			}
			
		}
		
		return($html);
	}
	
	
	/**
	 * process includes
	 * includes type = "all,js,css"
	 */
	public function processIncludes($includesType = "all"){
		
		$arrIncludes = $this->getProcessedIncludes(true, true, $includesType);
		
		$addonName = $this->addon->getName();
		
		foreach($arrIncludes as $include){
							
			$type = $include["type"];
			$url = $include["url"];
			$handle = UniteFunctionsUC::getVal($include, "handle");
			
			if(empty($handle))
				$handle = HelperUC::getUrlHandle($url, $addonName);
			
			$isInCache = $this->isIncludeInCache($url, $handle, $type);
			if($isInCache == true){
				continue;
			}
			$this->cacheInclude($url, $handle, $type);
			
			switch($type){
				case "js":
					UniteProviderFunctionsUC::addScript($handle, $url);
				break;
				case "css":
						UniteProviderFunctionsUC::addStyle($handle, $url);
				break;
				default:
					UniteFunctionsUC::throwError("Wrong include type: {$type} ");
				break;
			}
		
		}
		
	}
	
	private function a___________PREVIEW_HTML_________(){}
	
	/**
	 * put header additions in header html, functiob for override
	 */
	protected function putPreviewHtml_headerAdd(){
	}
	
	/**
	 * put footer additions in body html, functiob for override
	 */
	protected function putPreviewHtml_footerAdd(){
	}
	
	/**
	 * get addon preview html
	 */
	public function getPreviewHtml(){
		
		$this->validateInited();
		
		$outputs = "";
		
		$title = $this->addon->getTitle();
		$title .= " ". __("Preview",ADDONLIBRARY_TEXTDOMAIN);
		$title = htmlspecialchars($title);

		//get libraries, but not process provider
		$htmlBody = $this->getHtmlBody(false);
		
		$arrIncludes = $this->getProcessedIncludes(true, false);
		
		$htmlInlcudesCss = $this->getHtmlIncludes($arrIncludes,"css");
		$htmlInlcudesJS = $this->getHtmlIncludes($arrIncludes,"js");
		
		$arrCssCustomStyles = UniteProviderFunctionsUC::getCustomStyles();
		$htmlCustomCssStyles = HelperHtmlUC::getHtmlCustomStyles($arrCssCustomStyles);
		
		$arrJsCustomScripts = UniteProviderFunctionsUC::getCustomScripts();
		$htmlJSScripts = HelperHtmlUC::getHtmlCustomScripts($arrJsCustomScripts);
		
		//set options
		
		$options = $this->addon->getOptions();
				
		$bgCol = $this->addon->getOption("preview_bgcol");
		$previewSize = $this->addon->getOption("preview_size");
		
		$previewWidth = "100%";
		
		switch($previewSize){
			case "column":
				$previewWidth = "300px";
			break;
			case "custom":
				$previewWidth = $this->addon->getOption("preview_custom_width");
				if(!empty($previewWidth)){
					$previewWidth = (int)$previewWidth;
					$previewWidth .= "px";
				}
			break;
		}
		
		
		$style = "";
		$style .= "max-width:{$previewWidth};";
		$style .= "background-color:{$bgCol};";
		
		$urlPreviewCss = GlobalsUC::$urlPlugin."css/unitecreator_preview.css";
		
		$html = "";
		$htmlHead = "";
				
		$htmlHead = "<!DOCTYPE html>".self::BR;
		$htmlHead .= "<html>".self::BR;
		
		//output head
		$htmlHead .= self::TAB."<head>".self::BR;
		$html .= $htmlHead;
		
		//get head html
		$htmlHead .= self::TAB2."<title>{$title}</title>".self::BR;
		$htmlHead .= self::TAB2."<link rel='stylesheet' href='{$urlPreviewCss}' type='text/css'>".self::BR;
		$htmlHead .= $htmlInlcudesCss;
		
		if(!empty($htmlCustomCssStyles))
			$htmlHead .= self::BR.$htmlCustomCssStyles;
		
		$html .= $htmlHead;
		$output["head"] = $htmlHead;
		
		$htmlAfterHead = "";
		$htmlAfterHead .= self::TAB."</head>".self::BR;
		
		//output body
		$htmlAfterHead .= self::TAB."<body>".self::BR;
		$htmlAfterHead .= self::BR.self::TAB2."<div class='uc-preview-wrapper' style='{$style}'>";
		$htmlAfterHead .= self::BR.$htmlBody;
		$htmlAfterHead .= self::BR.self::TAB2."</div>";
		
		$html .= $htmlAfterHead;
		$output["after_head"] = $htmlAfterHead;
		
		$htmlEnd = "";
		$htmlEnd .= $htmlInlcudesJS.self::BR;
		$htmlEnd .= $htmlJSScripts.self::BR;
		
		$htmlEnd .= self::BR.self::TAB."</body>".self::BR;
		$htmlEnd .= "</html>";
		
		$html .= $htmlEnd;
		$output["end"] = $htmlEnd;
				
		$output["full_html"] = $html;
		
		return($output);
	}
	
	
	
	/**
	 * put html preview
	 */
	public function putPreviewHtml(){
		
		$output = $this->getPreviewHtml();
		
		echo $output["head"];
		
		//$this->putPreviewHtml_headerAdd();
		
		echo $output["after_head"];
		
		$this->putPreviewHtml_footerAdd();
		
		echo $output["end"];
	}
	
	
	private function a___________GENERAL_________(){}
	
	
	/**
	 * process html before output, function for override
	 */
	protected function processHtml($html){
		
		return($html);
	}
	
	
	/**
	 * place output by shortcode
	 */
	public function getHtmlBody($scriptHardCoded = true, $putCssIncludes = false, $putCssInline = true, $params = null){
		
		$this->validateInited();
	
		$title = $this->addon->getTitle(true);
		
		try{
			
			$html = $this->objTemplate->getRenderedHtml(self::TEMPLATE_HTML);
			$html = $this->processHtml($html);
			
			//make css
			$css = $this->objTemplate->getRenderedHtml(self::TEMPLATE_CSS);
			
			
			$js = $this->objTemplate->getRenderedHtml(self::TEMPLATE_JS);
			
			//get css includes if needed
			$arrCssIncludes = array();
			if($putCssIncludes == true)
				$arrCssIncludes = $this->getProcessedIncludes(true, true, "css");
				
				
			$output = "<!-- start {$title} -->";
			
			//add css includes if needed
			if(!empty($arrCssIncludes)){
				$htmlIncludes = $this->getHtmlIncludes($arrCssIncludes);
				$output .= "\n".$htmlIncludes;
			}
			
			//add css
			if(!empty($css)){
				$css = "/* addon: $title */".self::BR2.$css.self::BR2;
				if($putCssInline == true)
					$output .= "\n			<style type=\"text/css\">{$css}</style>";
				else
					HelperUC::putInlineStyle($css);
			}
			
			
			//add html
			
			$output .= "\n\n			".$html;
	
			//output js
			if(!empty($js)){
				
				$title = $this->addon->getTitle();
				
				if($scriptHardCoded == false)
					$js = "// $title scripts: \n".$js;
				
				if($scriptHardCoded == false)
					UniteProviderFunctionsUC::printCustomScript($js);
				else{
					$wrapInTimeout = UniteFunctionsUC::getVal($params, "wrap_js_timeout");
					$wrapInTimeout = UniteFunctionsUC::strToBool($wrapInTimeout);
					
					$wrapStart = UniteFunctionsUC::getVal($params, "wrap_js_start");
					$wrapEnd = UniteFunctionsUC::getVal($params, "wrap_js_end");
					
					
					$output .= "\n\n			<script type=\"text/javascript\">";
					
					if(!empty($wrapStart))
						$output .= "\n		".$wrapStart;
					
					if($wrapInTimeout == true)
						$output .= "\n			setTimeout(function(){";
					
					$output .= "\n			".$js;
					
					if($wrapInTimeout == true)
						$output .= "\n			},300);";
					
					if(!empty($wrapEnd))
						$output .= "\n		".$wrapEnd;
					
					$output .= "\n			</script>";
				}
				
			}
				
			$output .= "\n			<!-- end {$title} -->";
			
		}catch(Exception $e){
			
			$message = $e->getMessage();
	
			$message = "Error in addon $title, ".$message;
			UniteFunctionsUC::throwError($message);			
		}
		
		return($output);
	}
	
	
	
	/**
	 * get addon contstant data that will be used in the template
	 */
	public function getConstantData(){
		
		$this->validateInited();
		
		if(!empty($this->cacheConstants))
			return($this->cacheConstants);
		
		$data = array();
		
		$prefix = "ucid";
		if($this->isInited)
			$prefix = "uc_".$this->addon->getName();
					
		//add serial number:
		self::$serial++;
		
		$generatedSerial = self::$serial.UniteFunctionsUC::getRandomString(4, true);
		
		$this->generatedID = $prefix.$generatedSerial;
		
		$data["uc_serial"] = $generatedSerial;
		$data["uc_id"] = $this->generatedID;
		
		//add assets url
		$urlAssets = $this->addon->getUrlAssets();
		if(!empty($urlAssets))
			$data["uc_assets_url"] = $urlAssets;
		
		$data = UniteProviderFunctionsUC::addSystemConstantData($data);
		
		$data = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_ADD_ADDON_OUTPUT_CONSTANT_DATA, $data);
				
		$this->cacheConstants = $data;
		
		return($data);
	}
	
	
	/**
	 * get item extra variables
	 */
	public function getItemConstantDataKeys(){
		
		$arrKeys = array(
				"item_id",
				"item_index"
		);
		
		return($arrKeys);
	}
	
	
	
	/**
	 * get constant data keys
	 */
	public function getConstantDataKeys(){
		$constantData = $this->getConstantData();
		$arrKeys = array_keys($constantData);
		return($arrKeys);
	}
	
	
	/**
	 * get addon params
	 */
	private function getAddonParams(){
		if(!empty($this->paramsCache))
			return($this->paramsCache);
		
		$this->paramsCache = $this->addon->getProcessedMainParamsValues($this->processType);
		return($this->paramsCache);
	}
	
	
	/**
	 * modify items data, add "item" to array
	 */
	protected function normalizeItemsData($arrItems, $extraKey=null){
		
		if(empty($arrItems))
			return(array());
		
		foreach($arrItems as $key=>$item){
			if(!empty($extraKey))
				$arrItems[$key] = array("item"=>array($extraKey=>$item));
			else 			
				$arrItems[$key] = array("item"=>$item);
		}
		
		return($arrItems);
	}
	
	/**
	 * get special items - instagram
	 */
	private function getItemsSpecial_Instagram($arrData){
		
		$paramInstagram = $this->addon->getParamByType(UniteCreatorDialogParam::PARAM_INSTAGRAM);
		$instaName = UniteFunctionsUC::getVal($paramInstagram, "name");
		$dataInsta = $arrData[$instaName];
		
		$instaMain = UniteFunctionsUC::getVal($dataInsta, "main");
		$instaItems = UniteFunctionsUC::getVal($dataInsta, "items");
		
		if(empty($instaMain))
			$instaMain = array();
		
		$instaMain["hasitems"] = !empty($instaItems);
		
		$arrItemData = $this->normalizeItemsData($instaItems, $instaName);
		$arrData[$instaName] = $instaMain;
		
		$output = array();
		$output["main"] = $arrData;
		$output["items"] = $arrItemData;
		
		return($output);
	}
	
	
	/**
	 * init the template
	 */
	private function initTemplate(){
				
		$this->validateInited();
		
		//set params
		$arrData = $this->getConstantData();
		
		$arrParams = $this->getAddonParams();
		
		$arrData = array_merge($arrData, $arrParams);
				
		//set templates
		$html = $this->addon->getHtml();
		$css = $this->addon->getCss();
				
		//set item css call
		$cssItem = $this->addon->getCssItem();
		$cssItem = trim($cssItem);
		if(!empty($cssItem))
			$css .= "\n{{put_css_items()}}";
		
		$js = $this->addon->getJs();
		
		$this->objTemplate->setAddon($this->addon);
		
		$this->objTemplate->addTemplate(self::TEMPLATE_HTML, $html);
		$this->objTemplate->addTemplate(self::TEMPLATE_CSS, $css);
		$this->objTemplate->addTemplate(self::TEMPLATE_JS, $js);
		
		
		//set items template
		if($this->isItemsExists == false){
			
			$this->objTemplate->setParams($arrData);
		
		}
		else{
			
			if($this->processType == UniteCreatorParamsProcessor::PROCESS_TYPE_CONFIG)
				$arrItemData = array();
			else
			switch($this->itemsType){
				case "instagram":
					
					$response = $this->getItemsSpecial_Instagram($arrData);
					$arrData = $response["main"];
					$arrItemData = $response["items"];
					
				break;
				case "post":		//move posts data from main to items
					
					$paramPostsList = $this->addon->getParamByType(UniteCreatorDialogParam::PARAM_POSTS_LIST);
					
					if(empty($paramPostsList))
						UniteFunctionsUC::throwError("Some posts list param should be found");
					
					$postsListName = UniteFunctionsUC::getVal($paramPostsList, "name");
						
					$arrItemData = $this->normalizeItemsData($arrData[$postsListName], $postsListName);
					
					//set main param (true/false)
					$arrData[$postsListName] = !empty($arrItemData);
					
				break;
				default:
															
					$arrItemData = $this->addon->getProcessedItemsData($this->processType);
				break;
			}
			
			
			$itemIndex = 0;
			foreach($arrItemData as $key=>$item){
			    
			    $arrItem = $item["item"];
			    
			    $itemIndex++;
			    
			    $arrItem["item_index"] = $itemIndex;
			    $arrItem["item_id"] = $this->generatedID."_item".$itemIndex;
			    
			    $arrItemData[$key]["item"] = $arrItem;
			}
			
			$this->objTemplate->setParams($arrData);
			
			$this->objTemplate->setArrItems($arrItemData);
			
			$htmlItem = $this->addon->getHtmlItem();
			$this->objTemplate->addTemplate(self::TEMPLATE_HTML_ITEM, $htmlItem);
			
			$htmlItem2 = $this->addon->getHtmlItem2();
			$this->objTemplate->addTemplate(self::TEMPLATE_HTML_ITEM2, $htmlItem2);
			
			$this->objTemplate->addTemplate(self::TEMPLATE_CSS_ITEM, $cssItem);
			
		}
		
	}
	
	
	
	/**
	 * init by addon
	 */
	public function initByAddon(UniteCreatorAddon $addon){
				
		if(empty($addon))
			UniteFunctionsUC::throwError("Wrong addon given");
		
		$this->isInited = true;
			
		$this->addon = $addon;
		$this->isItemsExists = $this->addon->isHasItems();
		
		$this->itemsType = $this->addon->getItemsType();
		
		//modify by special type
		
		switch($this->itemsType){
			case "instagram":
			case "post":
				$this->isItemsExists = true;
			break;
		}
		
		
		$this->initTemplate();
				
	}
	
	
}

?>