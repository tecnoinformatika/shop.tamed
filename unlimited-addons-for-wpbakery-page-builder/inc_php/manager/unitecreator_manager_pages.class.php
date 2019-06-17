<?php

/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorManagerPages extends UniteCreatorManager{
	
	const STATE_LAST_PAGES_CATEGORY = "state_last_pages_category";
	const FILTER_CAT_TYPE = "layout";
	
	protected $showAllCategory = false;
	private $addonTypeTitle = "";
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		$this->type = self::TYPE_PAGES;
		
		$this->init();
	}
	
	
	
	/**
	 * set last selected category state
	 */
	private function setStateLastSelectedCat($catID){
		HelperUC::setState(self::STATE_LAST_PAGES_CATEGORY, $catID);
	}
	
	
	private function a________PAGES_ITEM_HTML______(){}
	
	
	/**
	 * get page item add html, function for override
	 */
	protected function getPageItemAddHtml($layout){
		//funciton for override
		
		return("");
	}
	
	
	/**
	 * get item html from layout
	 */
	private function getPageItemHtml(UniteCreatorLayout $layout){
		
		$layoutID = $layout->getID();
		$title = $layout->getTitle(true);
		
		$class = "uc-addon-thumbnail";
		$class = "class=\"{$class}\"";
		
		$addHTML = "";
		$addHTML = $this->getPageItemAddHtml($layout);
		$addHTML = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_PAGE_ADD_ITEM_HTML, $addHTML, $layout, $this);
		
		//set html output
		$htmlItem  = "<li id=\"uc_item_{$layoutID}\" data-id=\"{$layoutID}\" data-title=\"{$title}\" {$class} >";
		
		$htmlItem .= "	<div class=\"uc-item-title unselectable\" unselectable=\"on\">{$title}</div>";
		$htmlItem .= "	<div class=\"uc-item-icon unselectable\" unselectable=\"on\"></div>";
		
		if(!empty($addHTML))
			$htmlItem .= $addHTML;
		
		$htmlItem .= "</li>";
		
		return($htmlItem);
	}
	
	
	/**
	 * get cat pages html from data
	 */
	public function getCatPagesHtmlFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catID");
		$catID = (int)$catID;
		UniteFunctionsUC::validateNotEmpty($catID,"cat id");
		
		$objLayouts = new UniteCreatorLayouts();
		$arrLayouts = $objLayouts->getArrLayouts("ordering",array("catid"=>$catID));
		
		$htmlItems = "";
		
		foreach($arrLayouts as $layout){
			
			$htmlItem = $this->getPageItemHtml($layout);
			
			$htmlItems .= $htmlItem;
		}
		
		$output = array();
		$output["html_items"] = $htmlItems;
		
		return($output);
	}
	
	
	/**
	 * get html of categories and items.
	 */
	public function getCatsAndAddonsHtml($catID, $type, $catTitle = "", $isweb = false){
		
		$arrCats = $this->getArrCats();
		
		//change category if needed
		$arrCatsAssoc = UniteFunctionsUC::arrayToAssoc($arrCats, "id");
		
		if(isset($arrCatsAssoc[$catID]) == false){
			$firstCat = reset($arrCats);
			if(!empty($firstCat)){
				$catID = $firstCat["id"];
				$catTitle = $firstCat["title"];
				$isweb = UniteFunctionsUC::getVal($firstCat, "isweb");
				$isweb = UniteFunctionsUC::strToBool($isweb);
			}
		}
		
		$objCats = new UniteCreatorCategories();
		$htmlCatList = $this->getCatList($catID);
		
		$htmlAddons = $this->getCatAddonsHtml($catID, $type, $catTitle, $isweb);
		
		$response = array();
		$response["htmlItems"] = $htmlAddons;
		$response["htmlCats"] = $htmlCatList;
	
		return($response);
	}
		
	
	
		
	private function a________MENUS______(){}
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){
		
		$arrMenuItem = array();
		
		/*
		$arrMenuItem["edit_addon"] = __("Edit Addon",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["edit_addon_blank"] = __("Edit In New Tab",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["quick_edit"] = __("Quick Edit",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["remove_item"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["test_addon"] = __("Test Addon",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["test_addon_blank"] = __("Test In New Tab",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["export_addon"] = __("Export Addon",ADDONLIBRARY_TEXTDOMAIN);
		
		*/
		
		$arrMenuItem = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_PAGES_MENU_SINGLE, $arrMenuItem, $this->managerName);
		
		return($arrMenuItem);
	}

	
	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		//$arrMenuItemMultiple["remove_item"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
		
		//$arrMenuItemMultiple = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_MULTIPLE, $arrMenuItemMultiple);
		
		return($arrMenuItemMultiple);
	}
	
	
	/**
	 * get item field menu
	 */
	protected function getMenuField(){
		$arrMenuField = array();
				
		$arrMenuField["select_all"] = __("Select All",ADDONLIBRARY_TEXTDOMAIN);
		
		//$arrMenuField = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_FIELD, $arrMenuField);
		
		return($arrMenuField);
	}

	
	
	/**
	 * get category menu
	 */
	protected function getMenuCategory(){
	
		$arrMenuCat = array();
		
		$arrMenuCat["edit_category"] = __("No Page Category Actions Available",ADDONLIBRARY_TEXTDOMAIN);
		
		//$arrMenuCat = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_CATEGORY, $arrMenuCat);
		
		return($arrMenuCat);
	}
	
	
	private function a________OTHERS______(){}
	
	
	/**
	 * get categories
	 */
	protected function getArrCats(){
		
		$arrCats = $this->objCats->getListExtra(self::FILTER_CAT_TYPE,"", "",false);
		
		return($arrCats);
	}
	
	
	/**
	 * get category list
	 */
	protected function getCatList($selectCatID = null, $arrCats = null){
		
		if($arrCats === null)
			$arrCats = $this->getArrCats();
		
		$htmlCatList = $this->objCats->getHtmlCatList($selectCatID, null, $arrCats);
		
		
		return($htmlCatList);
	}
	
	
	/**
	 * get no items text
	 */
	protected function getNoItemsText(){
		
		$text = __("No Addons Found", ADDONLIBRARY_TEXTDOMAIN);
		
		return($text);
	}
	
	
	/**
	 * get html categories select
	 */
	protected function getHtmlSelectCats(){
		
		if($this->hasCats == false)
			UniteFunctionsUC::throwError("the function ");
		
		$htmlSelectCats = $this->objCats->getHtmlSelectCats(self::FILTER_CAT_TYPE);
		
		return($htmlSelectCats);
	}
	
	
	/**
	 * put content to items wrapper div
	 */
	protected function putListWrapperContent(){
		
		?>
		<div id="uc_empty_addons_wrapper" class="uc-empty-addons-wrapper" style="display:none">
			
			No Pages Found
			
		</div>
		<?php 
	}
	
	
	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){
		?>
							 			
 			<a data-action="select_all_items" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-select" data-textselect="<?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?>" data-textunselect="<?php _e("Unselect All",ADDONLIBRARY_TEXTDOMAIN)?>"><?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?></a>
 			
 			<!-- 
	 		<a data-action="edit_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-single-item"><?php _e("Edit Addon",ADDONLIBRARY_TEXTDOMAIN)?> </a>
			-->
		<?php
		
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_PAGES_ITEM_BUTTONS, $this->managerName);
		
	}
	
	
	/**
	 * put category edit dialog
	 */
	protected function putDialogEditCategory(){
		
		$prefix = "uc_dialog_edit_category";
		
		?>
			<div id="uc_dialog_edit_category" class="uc-dialog-edit-category" data-custom='yes' title="<?php _e("Edit Category",ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;" >
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-dialog-inner-constant">	
					<div id="<?php echo $prefix?>_settings_loader" class="loader_text"><?php _e("Loading Settings", ADDONLIBRARY_TEXTDOMAIN)?>...</div>
					
					<div id="<?php echo $prefix?>_settings_content"></div>
					
				</div>
				
				<?php 
					$buttonTitle = __("Update Category", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Updating Category...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Category Updated", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>			
				
			</div>
		
		<?php
	}
	
	
	/**
	 * get category settings html
	 */
	public function getCatSettingsHtmlFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catid");
		UniteFunctionsUC::validateNotEmpty($catID, "category id");

		$objCat = new UniteCreatorCategories();
		$arrCat = $objCat->getCat($catID);
		
		$title = UniteFunctionsUC::getVal($arrCat, "title");
				
		$settings = new UniteCreatorSettings();
		$settings->addStaticText("Category ID: <b>$catID</b>","some_name");
		$settings->addTextBox("category_title", $title, __("Category Title",ADDONLIBRARY_TEXTDOMAIN));
		
		$htmlSettings = HelperHtmlUC::drawSettingsGetHtml($settings, "uc_category_settings");
		
		$response = array();
		$response["html"] = $htmlSettings;
		
		return($response);
	}
		
	
	private function a________PAGE_PROPERTIES_DIALOG_____(){}
	
	
	/**
	 * get page properties settings object
	 */
	protected function getPagePropertiesSettings(){
		
		$objSettings = HelperUC::getSettingsObject("page_properties");
		
		return($objSettings);
	}
	
	
	/**
	 * get page props from data
	 */
	public function getPagePropsHtmlFromData($data){
		
		$pageID = UniteFunctionsUC::getVal($data, "pageid");
		UniteFunctionsUC::validateNotEmpty($pageID);
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($pageID);
		
		//set param values
		$params = $objLayout->getParams();
		$params["title"] = $objLayout->getTitle();
		
		$objSettings = $this->getPagePropertiesSettings();
		$objSettings->setStoredValues($params);
		
		
		$htmlSettings = HelperHtmlUC::drawSettingsGetHtml($objSettings, "uc_page_properties");
		
		$response = array();
		$response["html"] = $htmlSettings;
		
		return($response);
	}
	

	/**
	 * update page params from data, get new page HTML
	 */
	public function updatePageParamsFromData($data){
		
		$layoutID = UniteFunctionsUC::getVal($data, "layoutid");
		$params = UniteFunctionsUC::getVal($data, "params");
		
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		$objLayout->updateParams($params);
		
		
		$htmlItem = $this->getPageItemHtml($objLayout);
		
		$response = array();
		$response["html_item"] = $htmlItem;
		
		return($response);
	}
	
		
	
	/**
	 * put category edit dialog
	 */
	protected function putDialogPageProperties(){
		
		$prefix = "uc_dialog_page_properties";
		
		?>
			<div id="<?php echo $prefix?>" class="uc-dialog-edit-properties" title="<?php _e("Edit Properties",ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;" >
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-dialog-inner-constant">	
					<div class="unite-dialog-loader"><?php _e("Loading Page Properties", ADDONLIBRARY_TEXTDOMAIN)?>...</div>
					<div class="unite-dialog-content"></div>
				</div>
																
				<?php 
					$buttonTitle = __("Update Page", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Updating Page...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Page Updated", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>			
				
			</div>
		
		<?php
	}
	
	private function a________INIT______(){}
	
	
	/**
	 * put scripts
	 */
	private function putScripts(){
				
		$arrPlugins = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDONS_PLUGINS, array());
		
		
		$script = "
			jQuery(document).ready(function(){
				var selectedCatID = \"{$this->selectedCategory}\";
				var managerAdmin = new UCManagerAdmin();";
		
		if(!empty($arrPlugins)){
			foreach($arrPlugins as $plugin)
				$script .= "\n				managerAdmin.addPlugin('{$plugin}');";
		}
		
		$script .= "
				managerAdmin.initManager(selectedCatID);
			});
		";
	
		UniteProviderFunctionsUC::printCustomScript($script);
	}
	
	
	/**
	 * put additional html here
	 */
	protected function putAddHtml(){
		
		$this->putScripts();
		
		$this->putDialogPageProperties();
		
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_PAGES_ADD_HTML, $this->managerName);
	}
	
	
	
	/**
	 * put init items, will not run, because always there are cats
	 */
	protected function putInitItems(){
		
		if($this->hasCats == true)
			return(false);
		
		/*
		$objAddons = new UniteCreatorAddons();
		$htmlAddons = $objAddons->getCatAddonsHtml(null);
		
		echo $htmlAddons;
		*/
	}
	
	
	/**
	 * init the addons manager
	 */
	protected function init(){
		
		
		parent::init();
		
		$this->itemsLoaderText = __("Getting Addons",ADDONLIBRARY_TEXTDOMAIN);
		$this->textItemsSelected = __("addons selected",ADDONLIBRARY_TEXTDOMAIN);
		$this->enableCatsActions = false;
		$this->listClassType = "addons";
		$this->viewType = self::VIEW_TYPE_THUMB;
		$this->enableStatusLineOperations = false;
		
		//set selected category
		$lastCatID = HelperUC::getState(self::STATE_LAST_PAGES_CATEGORY);
		if(!empty($lastCatID))
			$this->selectedCategory = $lastCatID;
		
	}
	
	
}