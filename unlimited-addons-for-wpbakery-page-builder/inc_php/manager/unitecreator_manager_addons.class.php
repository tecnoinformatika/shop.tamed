<?php

/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorManagerAddons extends UniteCreatorManager{
	
	const STATE_FILTER_CATALOG = "manager_filter_catalog";
	const STATE_FILTER_ACTIVE = "fitler_active_addons";
	const STATE_LAST_ADDONS_CATEGORY = "last_addons_cat";
	
	const FILTER_CATALOG_MIXED = "mixed";
	const FILTER_CATALOG_INSTALLED = "installed";
	const FILTER_CATALOG_WEB = "web";
	
	protected $showAllCategory = false;
	private $filterAddonType = null;
	private $addonTypeTitle = "";
	private $filterActive = "";
	private $showAddonTooltip = false;
	protected $filterCatalogState;
	protected $defaultFilterCatalog;
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		$this->type = self::TYPE_ADDONS;
		$this->viewType = self::VIEW_TYPE_THUMB;		
		$this->defaultFilterCatalog = self::FILTER_CATALOG_INSTALLED;
		$this->filterAddonType = GlobalsUC::$defaultAddonType;
		
		$this->init();
		
		UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MODIFY_ADDONS_MANAGER, $this);
				
	}
	
	
	/**
	 * set filter active state
	 */
	public static function setStateFilterCatalog($filterCatalog){
		
		if(!empty($filterCatalog))
			HelperUC::setState(self::STATE_FILTER_CATALOG, $filterCatalog);
	}
	
	/**
	 * get filter active statge
	 */
	protected function getStateFilterCatalog(){
		
		if(GlobalsUC::$enableWebCatalog == false)
			return(self::FILTER_CATALOG_INSTALLED);
		
		$filterCatalog = HelperUC::getState(self::STATE_FILTER_CATALOG);
		if(empty($filterCatalog))
			$filterCatalog = $this->defaultFilterCatalog;
				
		return($filterCatalog);
	}
	
	
	/**
	 * set filter active state
	 */
	public static function setStateFilterActive($filterActive){
		
		if(!empty($filterActive))
			HelperUC::setState(UniteCreatorManagerAddons::STATE_FILTER_ACTIVE, $filterActive);
		
	}
	
	/**
	 * get filter active statge
	 */
	public static function getStateFilterActive(){
		$filterActive = HelperUC::getState(UniteCreatorManagerAddons::STATE_FILTER_ACTIVE);
		
		return($filterActive);
	}
	
	
	private function a________ADDON_HTML______(){}
	
		
	/**
	 * get addon admin html add
	 */
	protected function getAddonAdminAddHtml(UniteCreatorAddon $objAddon){
		
		$addHtml = "";
		
		$addHtml = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDON_ADDHTML, $addHtml, $objAddon);
		
		return($addHtml);
	}
	
	
	/**
	 * get data of the admin html from addon
	 */
	private function getAddonAdminHtml_getDataFromAddon(UniteCreatorAddon $objAddon){
		
		$data = array();
		
		$objAddon->validateInited();
		
		$title = $objAddon->getTitle();
		
		$name = $objAddon->getNameByType();
		
		$description = $objAddon->getDescription();
		
		//set html icon
		$urlIcon = $objAddon->getUrlIcon();
		
		//get preview html
		$urlPreview = $objAddon->getUrlPreview();
		
		$itemID = $objAddon->getID();
		
		$isActive = $objAddon->getIsActive();
		
		$addHtml = $this->getAddonAdminAddHtml($objAddon);
		
		$data["title"] = $title;
		$data["name"] = $name;
		$data["description"] = $description;
		$data["url_icon"] = $urlIcon;
		$data["url_preview"] = $urlPreview;
		$data["id"] = $itemID;
		$data["is_active"] = $isActive;
		$data["add_html"] = $addHtml;
		
		return($data);
	}
	
		
	
	/**
	 * get add html of web addon
	 */
	private function getWebAddonData($addon){
		
		$isFree = UniteCreatorBrowser::isWebAddonFree($addon); 
		
		$state = UniteCreatorBrowser::STATE_PRO;
		if($isFree == true)
			$state = UniteCreatorBrowser::STATE_FREE;
		
		$data = UniteCreatorBrowser::getCatalogAddonStateData($state);
		
		return($data);
	}
	
	/**
	 * get category addons, objects or array from catalog
	 */
	private function getCatAddons($catID, $type=null, $title = "", $isweb = false){
		
		$filterActive = self::getStateFilterActive();
				
		$objAddons = new UniteCreatorAddons();
		
		$filterCatalog = $this->getStateFilterCatalog();
		
		$addons = array();
		
		switch($filterCatalog){
			case self::FILTER_CATALOG_WEB:
			break;
			case self::FILTER_CATALOG_INSTALLED:
				if($isweb == false)
					$addons = $objAddons->getCatAddons($catID, false, $filterActive, $type);
				
				return($addons);
			break;
			case self::FILTER_CATALOG_MIXED:
				if($isweb == false)
					$addons = $objAddons->getCatAddons($catID, false, $filterActive, $type);
			break;
		}
		
		
		//mix with the catalog
				
		//get category title
		if(!empty($catID) && empty($title)){
			$objCategories = new UniteCreatorCategories();
			$arrCat = $objCategories->getCat($catID);
			$title = UniteFunctionsUC::getVal($arrCat, "title");
		}
		
		if(empty($title))
			return($addons);
		
		$webAPI = new UniteCreatorWebAPI();
		$addons = $webAPI->mergeCatAddonsWithCatalog($title, $addons);
		
		
		return($addons);
	}
	
	
	/**
	 * get html addon
	 */
	public function getAddonAdminHtml($objAddon){
		
		if(is_array($objAddon))
			$data = $objAddon;
		else
			$data = $this->getAddonAdminHtml_getDataFromAddon($objAddon);
		
		//--- prepare data
		
		$title = UniteFunctionsUC::getVal($data, "title");
		$name = UniteFunctionsUC::getVal($data, "name");
		$description = UniteFunctionsUC::getVal($data, "description");
		$urlIcon = UniteFunctionsUC::getVal($data, "url_icon");
		$urlPreview = UniteFunctionsUC::getVal($data, "url_preview");
		$itemID = UniteFunctionsUC::getVal($data, "id");
		$isActive = UniteFunctionsUC::getVal($data, "is_active");
		$addHtml = UniteFunctionsUC::getVal($data, "add_html");
		$isweb = UniteFunctionsUC::getVal($data, "isweb");
		$liAddHTML = "";
		
		$state = null;
		
		if($isweb == true){
						
			$urlPreview = UniteFunctionsUC::getVal($data, "image");
			$isActive = true;
			$webData = $this->getWebAddonData($data);
			
			$addHtml = $webData["html_state"];
			$addHtml .= $webData["html_additions"];
			$state = $webData["state"];
			
			$itemID = UniteFunctionsUC::getSerialID("webaddon");
			$liAddHTML = " data-itemtype='web' data-state='{$state}'";
		}
		
		
		//--- prepare output
				
		$title = htmlspecialchars($title);
		$name = htmlspecialchars($name);
		$description = htmlspecialchars($description);
		
		$descOutput = $description;
		
		$htmlPreview = "";
		
		if($this->showAddonTooltip === true && !empty($urlPreview)){
			$urlPreviewHtml = htmlspecialchars($urlPreview);
			$htmlPreview = "data-preview='$urlPreviewHtml'";
		}
		
		$class = "uc-addon-thumbnail";
		if($isActive == false)
			$class .= " uc-item-notactive";
		
		if($isweb == true)
			$class .= " uc-item-web";
			
		$class = "class=\"{$class}\"";
		
		//set html output
		$htmlItem  = "<li id=\"uc_item_{$itemID}\" data-id=\"{$itemID}\" data-title=\"{$title}\" data-name=\"{$name}\" data-description=\"{$description}\" {$liAddHTML} {$htmlPreview} {$class} >";
		
		if($state == UniteCreatorBrowser::STATE_PRO){
			$urlBuy = GlobalsUC::URL_BUY;
			$htmlItem .= "<a href='$urlBuy' target='_blank'>";
		}
		
		if($this->viewType == self::VIEW_TYPE_INFO){
			
			$htmlItem .= "	<div class=\"uc-item-title unselectable\" unselectable=\"on\">{$title}</div>";
			$htmlItem .= "	<div class=\"uc-item-description unselectable\" unselectable=\"on\">{$descOutput}</div>";
			$htmlItem .= "	<div class=\"uc-item-icon unselectable\" unselectable=\"on\"></div>";
			
			//add icon
			$htmlIcon = "";
			if(!empty($urlIcon))
				$htmlIcon = "<div class='uc-item-icon' style=\"background-image:url('{$urlIcon}')\"></div>";
			
			$htmlItem .= $htmlIcon;
			
		}elseif($this->viewType == self::VIEW_TYPE_THUMB){
						
			$classThumb = "";
			$style = "";
			if(empty($urlPreview))
				$classThumb = " uc-no-thumb";
			else{
				$style = "style=\"background-image:url('{$urlPreview}')\"";
			}
			
			$htmlItem .= "	<div class=\"uc-item-thumb{$classThumb} unselectable\" unselectable=\"on\" {$style}></div>";
			
			$htmlItem .= "	<div class=\"uc-item-title unselectable\" unselectable=\"on\">{$title}</div>";
			
			if($addHtml)
				$htmlItem .= $addHtml;
			
		}else{
			UniteFunctionsUC::throwError("Wrong addons view type");
		}
		
		if($state == UniteCreatorBrowser::STATE_PRO){
			$htmlItem .= "</a>";
		}
		
		$htmlItem .= "</li>";
		
		
		return($htmlItem);
	}
	
	
	/**
	 * get html of cate items
	 */
	public function getCatAddonsHtml($catID, $type=null, $title = "", $isweb = false){
		
		$addons = $this->getCatAddons($catID, $type, $title, $isweb);
		
		$htmlAddons = "";
		
		foreach($addons as $addon){
			
			$html = $this->getAddonAdminHtml($addon);
			$htmlAddons .= $html;
		}
		
		return($htmlAddons);
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
	
	/**
	 * set last selected category state
	 */
	private function setStateLastSelectedCat($catID){
		HelperUC::setState(self::STATE_LAST_ADDONS_CATEGORY, $catID);
	}
	
	/**
	 * get category items html
	 */
	public function getCatAddonsHtmlFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catID");
		$catTitle = UniteFunctionsUC::getVal($data, "title");
				
		$objAddons = new UniteCreatorAddons();
		
		$type = $objAddons->getAddonTypeFromData($data);
		
		$resonseCombo = UniteFunctionsUC::getVal($data, "response_combo");
		$resonseCombo = UniteFunctionsUC::strToBool($resonseCombo);
				
		$filterActive = UniteFunctionsUC::getVal($data, "filter_active");
		
		$isweb = UniteFunctionsUC::getVal($data, "isweb");
		$isweb = UniteFunctionsUC::strToBool($isweb);
		
		if($isweb == false && $catID != "all")
			UniteFunctionsUC::validateNumeric($catID,"category id");
		
		if(GlobalsUC::$enableWebCatalog == true){
			
			$filterCatalog = UniteFunctionsUC::getVal($data, "filter_catalog");
			self::setStateFilterCatalog($filterCatalog);
		}
		
		self::setStateFilterActive($filterActive);
		$this->setStateLastSelectedCat($catID);
		
		if($resonseCombo == true){
			$response = $this->getCatsAndAddonsHtml($catID, $type, $catTitle, $isweb);
			
		}else{
			$itemsHtml = $this->getCatAddonsHtml($catID, $type, $catTitle, $isweb);
			$response = array("itemsHtml"=>$itemsHtml);
		}
		
		
		return($response);
	}
	
	
	
	private function a________OTHERS______(){}
	
	
	
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){
		
		$arrMenuItem = array();
		$arrMenuItem["edit_addon"] = __("Edit Addon",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["edit_addon_blank"] = __("Edit In New Tab",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["quick_edit"] = __("Quick Edit",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["remove_item"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["test_addon"] = __("Test Addon",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["test_addon_blank"] = __("Test In New Tab",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuItem["export_addon"] = __("Export Addon",ADDONLIBRARY_TEXTDOMAIN);
		
		$arrMenuItem = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_SINGLE, $arrMenuItem);
		
		return($arrMenuItem);
	}

	
	/**
	 * get item field menu
	 */
	protected function getMenuField(){
		$arrMenuField = array();
				
		$arrMenuField["select_all"] = __("Select All",ADDONLIBRARY_TEXTDOMAIN);
		
		$arrMenuField = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_FIELD, $arrMenuField);
		
		return($arrMenuField);
	}

	/**
	 * set filter addon type to use only it
	 */
	public function setAddonType($addonType, $typeTitle){
		
		$this->filterAddonType = $addonType;
		$this->addonTypeTitle = $typeTitle;
		
	}
	
	
	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		$arrMenuItemMultiple["remove_item"] = __("Delete",ADDONLIBRARY_TEXTDOMAIN);
				
		$arrMenuItemMultiple = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_MULTIPLE, $arrMenuItemMultiple);
		
		return($arrMenuItemMultiple);
	}
	
	
	/**
	 * get category menu
	 */
	protected function getMenuCategory(){
	
		$arrMenuCat = array();
		$arrMenuCat["edit_category"] = __("Edit Category",ADDONLIBRARY_TEXTDOMAIN);
		$arrMenuCat["delete_category"] = __("Delete Category",ADDONLIBRARY_TEXTDOMAIN);
		
		
		$arrMenuCat = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_MENU_CATEGORY, $arrMenuCat);
		
		return($arrMenuCat);
	}
	
	
	/**
	 * filter categories without web addons
	 */
	private function filterCatsWithoutWeb($arrCats){
		
		foreach($arrCats as $key=>$cat){
			$isweb = UniteFunctionsUC::getVal($cat, "isweb");
			$isweb = UniteFunctionsUC::strToBool($isweb);
			if($isweb == true)
				continue;
			
			$numWebAddons = UniteFunctionsUC::getVal($cat, "num_web_addons");
			if($numWebAddons == 0)
				unset($arrCats[$key]);
		}
		
		return($arrCats);
	}
	
	
	/**
	 * get categories with catalog
	 */
	private function getCatsWithCatalog($filterCatalog){
		
		$objAddons = new UniteCreatorAddons();
		$webAPI = new UniteCreatorWebAPI();
		
		$arrCats = $objAddons->getAddonsWidthCategories(true, true, $this->filterAddonType);
		$arrCats = $webAPI->mergeCatsAndAddonsWithCatalog($arrCats, true);
		
		if($filterCatalog == self::FILTER_CATALOG_WEB)
			$arrCats = $this->filterCatsWithoutWeb($arrCats);
		
		return($arrCats);
	}
	
	
	/**
	 * get categories
	 */
	protected function getArrCats(){
		
		$filterCatalog = $this->getStateFilterCatalog();
		
		switch($filterCatalog){
			case self::FILTER_CATALOG_MIXED:
			case self::FILTER_CATALOG_WEB:
				$arrCats = $this->getCatsWithCatalog($filterCatalog);
			break;
			default:	//installed type
				$arrCats = $this->objCats->getListExtra($this->filterAddonType,"", "", $this->showAllCategory);
			break;
		}
		
		return($arrCats);
	}
	
	
	/**
	 * get category list
	 */
	protected function getCatList($selectCatID = null, $arrCats = null){
		
		if($arrCats === null)
			$arrCats = $this->getArrCats();
		
		//dmp("add web cats");
		//dmp($arrCats);
		//exit();
				
		$htmlCatList = $this->objCats->getHtmlCatList($selectCatID, $this->filterAddonType, $arrCats);
		
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
		
		$htmlSelectCats = $this->objCats->getHtmlSelectCats($this->filterAddonType);
		
		return($htmlSelectCats);
	}
	
	
	/**
	 * put content to items wrapper div
	 */
	protected function putListWrapperContent(){
		$addonType = $this->filterAddonType;
		if(empty($addonType))
			$addonType = "default";
		
		$filepathEmptyAddons = GlobalsUC::$pathProviderViews."empty_addons_text_{$addonType}.php";
		if(file_exists($filepathEmptyAddons) == false)
			return(false);
		
		?>
		<div id="uc_empty_addons_wrapper" class="uc-empty-addons-wrapper" style="display:none">
			
			<?php include $filepathEmptyAddons?>
			
		</div>
		<?php 
	}
	
	
	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){
		?>
			
			<?php 
			 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS1);
			?>
				 			
 			<a data-action="import_addon" type="button" class="unite-button-secondary unite-button-blue button-disabled uc-button-item uc-button-add"><?php _e("Import Addons",ADDONLIBRARY_TEXTDOMAIN)?></a>
 			<a data-action="select_all_items" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-select" data-textselect="<?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?>" data-textunselect="<?php _e("Unselect All",ADDONLIBRARY_TEXTDOMAIN)?>"><?php _e("Select All",ADDONLIBRARY_TEXTDOMAIN)?></a>

			<?php 
			 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS2);
			?>
 			
	 		<a data-action="remove_item" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php _e("Delete",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="edit_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-single-item"><?php _e("Edit Addon",ADDONLIBRARY_TEXTDOMAIN)?> </a>
	 		<a data-action="quick_edit" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php _e("Quick Edit",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="test_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php _e("Test Addon",ADDONLIBRARY_TEXTDOMAIN)?></a>

			<?php 
			 UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS3);
			?>
	 			 			
	 		<a data-action="activate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-notactive-item"><?php _e("Activate",ADDONLIBRARY_TEXTDOMAIN)?></a>
	 		<a data-action="deactivate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-active-item"><?php _e("Deactivate",ADDONLIBRARY_TEXTDOMAIN)?></a>
		<?php
	}
	
	/**
	 * put catalog filters
	 */
	protected function putFiltersCatalog(){
		
		if(GlobalsUC::$enableWebCatalog == false)
			return(false);
		
		$classActive = "class='uc-active'";
			
		$filterCatalog = $this->filterCatalogState;
		
		?>
			<div class="uc-filters-set-sap"></div>
			
			<div class="uc-filters-set-title"><?php _e("Filter Addons", ADDONLIBRARY_TEXTDOMAIN)?>:</div>
			
			<div id="uc_filters_catalog" class="uc-filters-set">
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="<?php echo self::FILTER_CATALOG_MIXED?>" <?php echo ($filterCatalog == self::FILTER_CATALOG_MIXED)?$classActive:""?> ><?php _e("Web and Installed", ADDONLIBRARY_TEXTDOMAIN)?></a>
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="<?php echo self::FILTER_CATALOG_INSTALLED?>" <?php echo ($filterCatalog == self::FILTER_CATALOG_INSTALLED)?$classActive:""?> ><?php _e("Installed", ADDONLIBRARY_TEXTDOMAIN)?></a>
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="<?php echo self::FILTER_CATALOG_WEB?>" <?php echo ($filterCatalog == self::FILTER_CATALOG_WEB)?$classActive:""?> ><?php _e("Web", ADDONLIBRARY_TEXTDOMAIN)?></a>
			</div>
		
		<?php 
	}
	
	/**
	 * put filters - function for override
	 */
	protected function putItemsFilters(){
		
		$classActive = "class='uc-active'";
		$filter = $this->filterActive;
		if(empty($filter))
			$filter = "all";
				
		?>
		
		<div class="uc-items-filters">
			
			<div class="uc-filters-set-title"><?php _e("Show Addons", ADDONLIBRARY_TEXTDOMAIN)?>:</div>
			
			<div id="uc_filters_active" class="uc-filters-set">
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="all" <?php echo ($filter == "all")?$classActive:""?> ><?php _e("All", ADDONLIBRARY_TEXTDOMAIN)?></a>
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="active" <?php echo ($filter == "active")?$classActive:""?> ><?php _e("Active", ADDONLIBRARY_TEXTDOMAIN)?></a>
				<a href="javascript:void(0)" onfocus="this.blur()" data-filter="not_active" <?php echo ($filter == "not_active")?$classActive:""?> ><?php _e("Not Active", ADDONLIBRARY_TEXTDOMAIN)?></a>
			</div>
			
			<?php $this->putFiltersCatalog()?>
			
			<div class="unite-clear"></div>
		</div>
		
		<?php 
	}
	
	
	/**
	 * put quick edit dialog
	 */
	private function putDialogQuickEdit(){
		?>
			<!-- dialog quick edit -->
		
			<div id="dialog_edit_item_title"  title="<?php _e("Quick Edit",ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;">
			
				<div class="dialog_edit_title_inner unite-inputs mtop_20 mbottom_20" >
			
					<div class="unite-inputs-label-inline">
						<?php _e("Title", ADDONLIBRARY_TEXTDOMAIN)?>:
					</div>
					<input type="text" id="dialog_quick_edit_title" class="unite-input-wide">
					
					<div class="unite-inputs-sap"></div>
							
					<div class="unite-inputs-label-inline">
						<?php _e("Name", ADDONLIBRARY_TEXTDOMAIN)?>:
					</div>
					<input type="text" id="dialog_quick_edit_name" class="unite-input-wide">
					
					<div class="unite-inputs-sap"></div>
					
					<div class="unite-inputs-label-inline">
						<?php _e("Description", ADDONLIBRARY_TEXTDOMAIN)?>:
					</div>
					
					<textarea class="unite-input-wide" id="dialog_quick_edit_description"></textarea>
					
				</div>
				
			</div>
		
		<?php 
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
	 * get category settings from cat ID
	 */
	protected function getCatagorySettings(UniteCreatorCategory $objCat){
		
		$title = $objCat->getTitle();
		$alias = $objCat->getAlias();
		$params = $objCat->getParams();
		$catID = $objCat->getID();
		
		$settings = new UniteCreatorSettings();
		
		$settings->addStaticText("Category ID: <b>$catID</b>","some_name");
		$settings->addTextBox("category_title", $title, __("Category Title",ADDONLIBRARY_TEXTDOMAIN));
		$settings->addTextBox("category_alias", $alias, __("Category Name",ADDONLIBRARY_TEXTDOMAIN));
		$settings->addIconPicker("icon","",__("Category Icon", ADDONLIBRARY_TEXTDOMAIN));
		
		$settings = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDONS_CATEGORY_SETTINGS, $settings, $objCat, $this->filterAddonType);
		
		$settings->setStoredValues($params);
		
		return($settings);
	}
	
	
	/**
	 * get category settings html
	 */
	public function getCatSettingsHtmlFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catid");
		UniteFunctionsUC::validateNotEmpty($catID, "category id");
		
		$objCat = new UniteCreatorCategory();
		$objCat->initByID($catID);
		
		$settings = $this->getCatagorySettings($objCat);
		
		
		$output = new UniteSettingsOutputWideUC();
		$output->init($settings);
		
		ob_start();
		$output->draw("uc_category_settings");
		
		$htmlSettings = ob_get_contents();
		
		ob_end_clean();
		
		$response = array();
		$response["html"] = $htmlSettings;
		
		return($response);
	}
	
	
	
	/**
	 * put import addons dialog
	 */
	private function putDialogImportAddons(){
		
		$dialogTitle = __("Import Addons",ADDONLIBRARY_TEXTDOMAIN);
		
		if(!empty($this->filterAddonType)){
			$dialogTitle .= __(" for ",ADDONLIBRARY_TEXTDOMAIN);
			$dialogTitle .= $this->addonTypeTitle;
		}
		
		$nonce = "";
		if(method_exists("UniteProviderFunctionsUC", "getNonce"))
			$nonce = UniteProviderFunctionsUC::getNonce();
		?>
		
			<div id="dialog_import_addons" class="unite-inputs" title="<?php echo $dialogTitle?>" style="display:none;">
				
				<div class="unite-dialog-top"></div>
				
				<div class='dialog-import-addons-left'>
				
					<div class="unite-inputs-label">
						<?php _e("Select addons export zip file (or files)", ADDONLIBRARY_TEXTDOMAIN)?>:
					</div>
					
					<div class="unite-inputs-sap-small"></div>
				
					<form id="dialog_import_addons_form" action="<?php echo GlobalsUC::$url_ajax?>" name="form_import_addon" class="dropzone uc-import-addons-dropzone">
						<input type="hidden" name="action" value="<?php echo GlobalsUC::PLUGIN_NAME?>_ajax_action">
						<input type="hidden" name="client_action" value="import_addons">
						
						<?php if(!empty($nonce)):?>
							<input type="hidden" name="nonce" value="<?php echo $nonce?>">
						<?php endif?>
						<script type="text/javascript">
							if(typeof Dropzone != "undefined")
								Dropzone.autoDiscover = false;
						</script>
					</form>	
						<div class="unite-inputs-sap-double"></div>
						
						<div class="unite-inputs-label">
							<?php _e("Import to Category", ADDONLIBRARY_TEXTDOMAIN)?>:
							
						<select id="dialog_import_catname">
							<option value="autodetect" ><?php _e("[Autodetect]", ADDONLIBRARY_TEXTDOMAIN)?></option>
							<option id="dialog_import_catname_specific" value="specific"><?php _e("Current Category", ADDONLIBRARY_TEXTDOMAIN)?></option>
						</select>
							
						</div>
						
						<div class="unite-inputs-sap-double"></div>
						
						<div class="unite-inputs-label">
							<label for="dialog_import_check_overwrite">							
								<?php _e("Overwrite Existing Addons", ADDONLIBRARY_TEXTDOMAIN)?>:
							</label>
							<input type="checkbox" checked="checked" id="dialog_import_check_overwrite"></input>
						</div>
						
				
				</div>
				
				<div id="dialog_import_addons_log" class='dialog-import-addons-right' style="display:none">
					
					<div class="unite-bold"> <?php _e("Import Addons Log",ADDONLIBRARY_TEXTDOMAIN)?> </div>
					
					<br>
					
					<div id="dialog_import_addons_log_text" class="dialog-import-addons-log"></div>
				</div>
				
				<div class="unite-clear"></div>
				
				<?php 
					$prefix = "dialog_import_addons";
					$buttonTitle = __("Import Addons", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Uploading addon file...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Addon Added Successfully", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
					
			</div>		
		<?php 
	}
	
	
	/**
	 * put add addon dialog
	 */
	private function putDialogAddAddon(){
		?>
			<!-- add addon dialog -->
			
			<div id="dialog_add_addon" class="unite-inputs" title="<?php _e("Add Addon",ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;">
			
				<div class="unite-dialog-top"></div>
			
				<div class="unite-inputs-label">
					<?php _e("Addon Title", ADDONLIBRARY_TEXTDOMAIN)?>:
				</div>
				
				<input type="text" id="dialog_add_addon_title" class="dialog_addon_input unite-input-regular" />
				
				<div class="unite-inputs-sap"></div>
				
				<div class="unite-inputs-label">
					<?php _e("Addon Name")?>:
				</div>
				
				<input type="text" id="dialog_add_addon_name" class="dialog_addon_input unite-input-alias" />
				
				<div class="unite-inputs-sap"></div>
				
				<div class="unite-inputs-label">
					<?php _e("Addon Description")?>:
				</div>
				
				<textarea id="dialog_add_addon_description" class="dialog_addon_input unite-input-regular"></textarea>
				
				<?php 
					$prefix = "dialog_add_addon";
					$buttonTitle = __("Add Addon", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Adding Addon...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Addon Added Successfully", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>			
				
			</div>
		
		<?php 
	}	
	

	/**
	 * put scripts
	 */
	private function putScripts(){
		
		$arrPlugins = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_MANAGER_ADDONS_PLUGINS, array());
		
		//$arrPlugins[] = "UCManagerMaster";
		
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
	 * put preview tooltips
	 */
	protected function putPreviewTooltips(){
		?>
		<div id="uc_manager_addon_preview" class="uc-addon-preview-wrapper" style="display:none"></div>
		<?php 
	}
	
	
	/**
	 * put additional html here
	 */
	protected function putAddHtml(){
		$this->putDialogQuickEdit();
		$this->putDialogAddAddon();
		$this->putDialogImportAddons();
		
		if($this->showAddonTooltip)
			$this->putPreviewTooltips();
		
		$this->putScripts();
	}
	
	
	/**
	 * put init items, will not run, because always there are cats
	 */
	protected function putInitItems(){
		
		if($this->hasCats == true)
			return(false);
		
		$objAddons = new UniteCreatorAddons();
		$htmlAddons = $objAddons->getCatAddonsHtml(null);
		
		echo $htmlAddons;
	}
	
	
	/**
	 * 
	 * set the custom data to manager wrapper div
	 */
	protected function onBeforePutHtml(){
		
		$addonsType = $this->filterAddonType;
		
		$addHTML = "data-addonstype=\"{$addonsType}\"";
		
		$this->setManagerAddHtml($addHTML); 
	}
	
	
	
	/**
	 * init the addons manager
	 */
	protected function init(){
		
		$this->hasCats = true;
		
		parent::init();
		
		$this->itemsLoaderText = __("Getting Addons",ADDONLIBRARY_TEXTDOMAIN);
		$this->textItemsSelected = __("addons selected",ADDONLIBRARY_TEXTDOMAIN);
		
		$this->filterActive = self::getStateFilterActive();
		$this->filterCatalogState = $this->getStateFilterCatalog();

		//set selected category
		$lastCatID = HelperUC::getState(self::STATE_LAST_ADDONS_CATEGORY);
		if(!empty($lastCatID))
			$this->selectedCategory = $lastCatID;
		
	}
	
	
}