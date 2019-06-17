<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorTemplateEngineWork{
	
	protected $twig;
	protected $arrTemplates = array();
	protected $arrParams = null;
	protected $arrItems = array();
	protected $addon = null;
	
	
	/**
	 * init twig
	 */
	public function __construct(){
	
	}
	
	
	public function a________CUSTOM_FUNCTIONS_____(){}
	
	
	/**
	 * output some item
	 */
	private function outputItem($index, $itemParams, $templateName, $sap, $newLine = true){
			
		$params = array_merge($this->arrParams, $itemParams);
		
		$htmlItem = $this->twig->render($templateName, $params);
		
		if(!empty($sap)){
			if($index != 0)
				echo $sap;
			echo $htmlItem;
		}else
			echo $htmlItem;
		
		if($newLine)
			echo "\n";
	}
	
	
	/**
	 * put items actually
	 */
	private function putItemsWork($templateName, $sap=null, $numItem=null){
		
		if(empty($this->arrItems))
		 	return(false);
		
		if($this->isTemplateExists($templateName) == false)
			return(false);
		
			
		if($numItem !== null){
			$itemParams = UniteFunctionsUC::getVal($this->arrItems, $numItem);
			if(empty($itemParams))
				return(false);
			
			$this->outputItem($numItem, $itemParams, $templateName, $sap, false);
			
			return(false);
		}

		//if sap, then no new line
		$newLine = empty($sap);
		
		foreach($this->arrItems as $index => $itemParams)
			$this->outputItem($index, $itemParams, $templateName, $sap, $newLine);
		
	}
	
	
	/**
	 * put items. input can be saporator or number of item, or null
	 */
	public function putItems($input = null, $templateName = "item"){
		
		$sap = null;
		$numItem = null;
		
		if($input !== null){
			if(is_numeric($input))
				$numItem = $input;
			else
				$sap = $input;
		}
		
		$this->putItemsWork($templateName, $sap, $numItem);
	}
	
	
	/**
	 * put items 2
	 */
	public function putItems2($input = null){
		$this->putItems($input, "item2");
	}
	
	/**
	 * put items 2
	 */
	public function putCssItems(){
		$this->putItems(null, "css_item");
	}
	
	
	/**
	 * put font override
	 */
	public function putFontOverride($name, $selector, $useID = false){
		
		$arrFonts = $this->addon->getArrFonts();
		
		if(empty($arrFonts))
			return(false);
		
		$cssSelector = "";
		if($useID == true)
			$cssSelector .= "#".$this->arrParams["uc_id"];
		
		if(!empty($cssSelector))
			$cssSelector .= " ".$selector;
		
		$fontKey = "uc_font_override_".$name;

		$arrFont = UniteFunctionsUC::getVal($arrFonts, $fontKey);
		
		if(empty($arrFont))
			return(false);
		
		$processor = new UniteCreatorParamsProcessor();
		$processor->init($this->addon);
		
		$css = $processor->processFont(null, $arrFont, true, $cssSelector);
		
		if(empty($css))
			return(false);
		
		echo $css;
	}
	
	/**
	 * put font override
	 */
	public function putPostTags($postID){
				
		echo "no tag list for this platform";
	}
	
	
	/**
	 * put font override
	 */
	public function putPostMeta($postID, $key){
		
		echo "no meta for this platform";
	}
	
	
	/**
	 * put post date
	 */
	public function putPostDate($postID, $dateFormat){
		
		echo "no custom date for this platform";
	}
	
	/**
	 * add extra functions to twig
	 */
	protected function initTwig_addExtraFunctions(){
		
		//add extra functions
		
		$putItemsFunction = new Twig_SimpleFunction('put_items', array($this,"putItems"));
		$putItemsFunction2 = new Twig_SimpleFunction('put_items2', array($this,"putItems2"));
		$putCssItemsFunction = new Twig_SimpleFunction('put_css_items', array($this,"putCssItems"));
		$putFontOverride = new Twig_SimpleFunction('put_font_override', array($this,"putFontOverride"));
		$putPostTagsFunction = new Twig_SimpleFunction('putPostTags', array($this,"putPostTags"));
		$putPostMetaFunction = new Twig_SimpleFunction('putPostMeta', array($this,"putPostMeta"));
		$putPostDateFunction = new Twig_SimpleFunction('putPostDate', array($this,"putPostDate"));
		
		
		//add extra functions
		$this->twig->addFunction($putItemsFunction);
		$this->twig->addFunction($putItemsFunction2);
		$this->twig->addFunction($putCssItemsFunction);
		$this->twig->addFunction($putFontOverride);
		$this->twig->addFunction($putPostTagsFunction);
		$this->twig->addFunction($putPostMetaFunction);
		$this->twig->addFunction($putPostDateFunction);
		
	}
	
	
	public function a________OTHER_FUNCTIONS_____(){}
	
	
	
	
	/**
	 * init twig
	 */
	private function initTwig(){
		
		if(empty($this->arrTemplates))
			UniteFunctionsUC::throwError("No templates found");
		
		$loader = new Twig_Loader_Array($this->arrTemplates);
		
		$arrOptions = array();
		$arrOptions["debug"] = true;
		
		$this->twig = new Twig_Environment($loader, $arrOptions);
		$this->twig->addExtension(new Twig_Extension_Debug());
		
		$this->initTwig_addExtraFunctions();
		
	}
	
	
	/**
	 * validate that not inited
	 */
	private function validateNotInited(){
		if(!empty($this->twig))
			UniteFunctionsUC::throwError("Can't add template or params when after rendered");
	}

	
	/**
	 * validate that all is inited
	 */
	private function validateInited(){
				
		if($this->arrParams === null){
			UniteFunctionsUC::throwError("Please set the params");
		}		
		
	}
	
	
	/**
	 * return if some template exists
	 * @param $name
	 */
	private function isTemplateExists($name){
		
		$isExists = array_key_exists($name, $this->arrTemplates);
		
		return($isExists);
	}
	
	
	/**
	 * add template
	 */
	public function addTemplate($name, $html){
		$this->validateNotInited();
		if(isset($this->arrTemplates[$name]))
			UniteFunctionsUC::throwError("template with name: $name already exists");
		
		$this->arrTemplates[$name] = $html;
	}
	
	
	/**
	 * add params
	 */
	public function setParams($params){
		
		$this->arrParams = $params;
	}
	
	
	/**
	 * set items
	 * @param $arrItems
	 */
	public function setArrItems($arrItems){
		
		$this->arrItems = $arrItems;
	}
	
	
	/**
	 * set fonts array
	 */
	public function setArrFonts($arrFonts){
		$this->arrFonts = $arrFonts;
	}
	
	
	/**
	 * get rendered html
	 * @param $name
	 */
	public function getRenderedHtml($name){
		
		UniteFunctionsUC::validateNotEmpty($name);
		$this->validateInited();
		if(array_key_exists($name, $this->arrTemplates) == false)
			UniteFunctionsUC::throwError("Template with name: $name not exists");
		
		if(empty($this->twig))
			$this->initTwig();
		
		$output = $this->twig->render($name, $this->arrParams);
		
		return($output);
	}
	
	
	/**
	 * set addon
	 */
	public function setAddon($addon){
		
		$this->addon = $addon;
	}
	
}