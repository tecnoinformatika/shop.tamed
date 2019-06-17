<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorCategories extends UniteElementsBaseUC{
	
	private $titleBase = "New Category";
	private static $serial = 0;
	
	const TYPE_LAYOUT = "layout";
	
	
	public function __construct(){
		parent::__construct();
		
	}
	
	/**
	 * 
	 * validate that category exists
	 */
	public function validateCatExist($catID){
		$this->getCat($catID);
	}
	
	
	/**
	 * validate if category not exists
	 */
	private function validateCatTitleNotExists($title, $type, $catID=null){
		
		$isExists = $this->isCatExistsByTitle($title, $type, $catID);
		
		if($isExists == true)
			UniteFunctionsUC::throwError("Category with title: $title already exists");
		
	}
	
	/**
	 * validate category title
	 */
	private function validateTitle($title){
		
		UniteFunctionsUC::validateNotEmpty($title, "Category Title");
		
		UniteFunctionsUC::validateNoTags($title, "Category Title");
		
	}
	
	/**
	 * validate new title before add or update by type
	 */
	private function validateTitleByType($title, $type, $catID = null){
		
		$this->validateTitle($title);
		
		//validate that not exists for all the types
		$this->validateCatTitleNotExists($title, $type, $catID);
		
	}
	
	
	private function a______________GETTERS____________(){}
	
	
	/**
	 * get uncategorised category
	 */
	private function getFirstCats($type, $showAll){
	
		$objAddons = new UniteCreatorAddons();
		
		$filterActive = UniteCreatorManagerAddons::getStateFilterActive();
		
		$numAddonsZero = $objAddons->getNumAddons(0, $filterActive, $type);
		
		//all
		
		$arrCatAll = array();
		$arrCatAll["id"] = "all";
		$arrCatAll["title"] = HelperUC::getText("all_addons");
		$arrCatAll["alias"] = "";
		$arrCatAll["ordering"] = 0;
		$arrCatAll["parent_id"] = "";
		$arrCatAll["params"] = "";
		$arrCatAll["type"] = "";
		$arrCatAll["num_addons"] = "set";
		
		
		//uncategorized
		$arrCatZero = array();
		$arrCatZero["id"] = 0;
		$arrCatZero["title"] = HelperUC::getText("uncategorized");
		$arrCatZero["alias"] = "";
		$arrCatZero["ordering"] = 0;
		$arrCatZero["parent_id"] = "";
		$arrCatZero["params"] = "";
		$arrCatZero["type"] = "";
		$arrCatZero["num_addons"] = $numAddonsZero;
		
		$arrCats = array();
		
		if($showAll == true)
			$arrCats[] = $arrCatAll;
		
		if($numAddonsZero > 0)
			$arrCats[] = $arrCatZero;
		
		return($arrCats);
	}
	
	
	/**
	 * get category list simple
	 */
	private function getListSimple($type=""){
		
		if(empty($type))
			$type = UniteCreatorDB::ISNULL;
		
		$where = array();
		$where["type"] = $type;
		
		$response = $this->db->fetch(GlobalsUC::$table_categories, $where);
		
		return($response);
	}

	
	/**
	 * get list extra where ending
	 */
	private function getListExtra_WhereEnding($type, $filterTitle="", $ordering="", $params = array()){
		
		$whereFilterActive = UniteFunctionsUC::getVal($params, "where_filter_active");
				
		$type = $this->db->escape($type);
	
		if(empty($type))
			$where = "where cats.type is null or cats.type=''";
		else
			$where = "where cats.type='$type'";
	
		//add filter by title
		if(!empty($filterTitle)){
			$filterTitle = $this->db->escape($filterTitle);
			$where .= " and title like %$filterTitle%";
		}
	
		//add ordering
		$ordering = strtolower($ordering);
		switch($ordering){
			case "asc":
				$ordering = "title asc";
				break;
			case "desc":
				$ordering = "title desc";
				break;
			default:
				$ordering = "ordering";
			break;
		}
	
		$whereEnding = "$where $whereFilterActive GROUP BY cats.id order by $ordering";
	
		return($whereEnding);
	}
	
	
	/**
	 * get layouts list extra
	 */
	private function getListExtraLayouts($filterTitle="", $ordering=""){
		
		$type = self::TYPE_LAYOUT;
		
		$whereEnding = $this->getListExtra_WhereEnding($type, $filterTitle, $ordering);
		
		$tableCats = GlobalsUC::$table_categories;
		$tableLayouts = GlobalsUC::$table_layouts;
		
		$query = "select cats.*, count(layouts.id) as num_layouts from {$tableCats} as cats";
		$query .= " left join $tableLayouts as layouts on layouts.catid=cats.id $whereEnding";
		
		$arrCats = $this->db->fetchSql($query);
		
		//make short output
		$arrCatsNew = array();
		
		//add uncategorised
		$arrCatsNew[] = array(
				"id" => 0,
				"title" => __("Uncategorized", ADDONLIBRARY_TEXTDOMAIN),
				"num_layouts" => 0
		);
		
		foreach($arrCats as $key=>$cat){
			
			$arr = array();
			$arr["id"] = $cat["id"];
			$arr["title"] = $cat["title"];
			$arr["num_layouts"] = $cat["num_layouts"];
			
			$arrCatsNew[] = $arr;
		}
		
		return($arrCatsNew);
	}
	
	
	/**
	 * get categories list with "all" and "uncategorised" and num addons
	 * ordering = asc / desc / empty
	 */
	public function getListExtra($type, $filterTitle="", $ordering="", $showAll = true){
		
		$whereFilterActive = "";
		if($type != self::TYPE_LAYOUT)
			$whereFilterActive = UniteCreatorAddons::getFilterActiveWhere(null, " and addons");
		
		$params = array();
		$params["where_filter_active"] = $whereFilterActive;
		
		$whereEnding = $this->getListExtra_WhereEnding($type, $filterTitle, $ordering, $params);
		
		$tableCats = GlobalsUC::$table_categories;
		$tableAddons = GlobalsUC::$table_addons;
		
		$query = "select cats.*, count(addons.id) as num_addons from {$tableCats} as cats";
		$query .= " left join $tableAddons as addons on addons.catid=cats.id $whereEnding";
		
		$arrCats = $this->db->fetchSql($query);
		
		if(empty($arrCats))
			$arrCats = array();
				
		$arrFirstCats = $this->getFirstCats($type, $showAll);
		
		$arrCats = array_merge($arrFirstCats, $arrCats);
		
		if($showAll == false)
			return($arrCats);
		
		//set number of all addons
		
		$numAddons = 0;
		foreach($arrCats as $cat){
			$numCatAddons = $cat["num_addons"];
			if(!is_numeric($numCatAddons))
				continue;
			
			$numAddons += $numCatAddons;
		}
		
		$arrCats[0]["num_addons"] = $numAddons;
		
		return($arrCats);
	}
	
	
	/**
	 * get category records simple without num items
	 */
	public function getCatRecords($type){
		
		if(empty($type))
			$type = UniteCreatorDB::ISNULL;
		
		$where = array();
		if($type != "all"){
			$where["type"] = $type;
		}
		
		$arrCats = $this->db->fetch(GlobalsUC::$table_categories, $where, "ordering");
		
		return($arrCats);
	}
	
	
	/**
	 * get category records extra with num items
	 */
	public function getCatRecordsExtra($type){
		
		$filterTitle="";
		$ordering = "";
		
		$whereEnding = $this->getListExtra_WhereEnding($type, $filterTitle, $ordering);
		
		if(empty($type))
			$type = UniteCreatorDB::ISNULL;
		
		$where = array();
		if($type != "all"){
			$where["type"] = $type;
		}
		
		$tableCats = GlobalsUC::$table_categories;
		$tableAddons = GlobalsUC::$table_addons;
		
		$query = "select cats.*, count(addons.id) as num_addons from {$tableCats} as cats";
		$query .= " left join $tableAddons as addons on addons.catid=cats.id $whereEnding";
		
		$arrCats = $this->db->fetchSql($query);
		
		return($arrCats);
	}
	
	
	/**
	 * get first category array by cat type
	 */
	private function getFirstCatByAddType($addType){
		
		$arrCatsOutput = array();
		
		switch($addType){
			case "empty":
				$arrCatsOutput[""] = __("[Not Selected]", ADDONLIBRARY_TEXTDOMAIN);
			break;
			case "new":
				$arrCatsOutput["new"] = __("[Add New Category]", ADDONLIBRARY_TEXTDOMAIN);
			break;
			case "component":
				$arrCatsOutput[""] = __("[From Gallery Settings]", ADDONLIBRARY_TEXTDOMAIN);
			break;
			case "all_uncat":
				$arrCatsOutput["all"] = HelperUC::getText("all_addons");
				$arrCatsOutput[0] = HelperUC::getText("uncategorized");
			break;
			case "uncategorized":
				$arrCatsOutput[0] = HelperUC::getText("uncategorized");
			break;
			case "all_uncat_layouts":
				$arrCatsOutput["all"] = HelperUC::getText("all_layouts");
				$arrCatsOutput[0] = HelperUC::getText("uncategorized");
			break;
			
		}
		
		return($arrCatsOutput);
	}
	
	
	/**
	 * get category records with add type
	 */
	public function getCatRecordsWithAddType($addType, $type){
		
		$arrCats = $this->getCatRecords($type);
		
		$arrCatsOutput = $this->getFirstCatByAddType($addType);
		
		foreach($arrCats as $cat){
			$catID = UniteFunctionsUC::getVal($cat, "id");
			$arrCatsOutput[$catID] = $cat;
		}
		
		return($arrCatsOutput);
	}
	
	
	/**
	 * 
	 * get categories list short
	 * addtype: empty (empty category), new (craete new category)
	 */
	public function getCatsShort($addType = "", $type){
		
		$arrCats = $this->getCatRecords($type);
		
		$arrCatsOutput = $this->getFirstCatByAddType($addType);
		
		foreach($arrCats as $cat){
			$catID = UniteFunctionsUC::getVal($cat, "id");
			$title = UniteFunctionsUC::getVal($cat, "title");
			$arrCatsOutput[$catID] = $title;
		}
		
		return($arrCatsOutput);
	}
	
	
	
	/**
	 * get categories array
	 */
	public function getArrCats($type){
		
		$records = $this->getCatRecordsExtra($type);
		
		$arrCats = array();
		foreach($records as $record){
			$cat = $record;
			
			$params = UniteFunctionsUC::getVal($record, "params");
			$cat["params"] = UniteFunctionsUC::jsonDecode($params, true);
			$arrCats[] = $cat;
		}
		
		return($arrCats);
	}
	
	
	/**
	 * 
	 * get assoc value of category name
	 */
	private function getArrCatTitlesAssoc($type=""){
		
		$arrCats = $this->getListSimple($type);
		$arrAssoc = array();
		foreach($arrCats as $cat){
			$arrAssoc[$cat["title"]] = true;
		}
		return($arrAssoc);
	}
	
	
	/**
	 * 
	 * get max order from categories list
	 */
	private function getMaxOrder($type=""){
		
		$type = $this->db->escape($type);
		
		if(empty($type))
			$where = "where type='' or type is null";
		else			
			$where = "where type='$type'";
		
		$query = "select MAX(ordering) as maxorder from ".GlobalsUC::$table_categories." $where";
		
		///$query = "select * from ".self::TABLE_CATEGORIES;
		$rows = $this->db->fetchSql($query);
		
		$maxOrder = 0;
		if(count($rows)>0) 
			$maxOrder = $rows[0]["maxorder"];
		
		if(!is_numeric($maxOrder))
			$maxOrder = 0;
		
		return($maxOrder);
	}
	
	
	/**
	 * get true/false if some category exists
	 */
	public function isCatExists($catID){
		
		$arrCat = null;
		
		try{
		
			$arrCat = $this->db->fetchSingle(GlobalsUC::$table_categories,"id=$catID");
			
		}catch(Exception $e){
					
		}
		
		return !empty($arrCat);		
	}
	
	
	/**
	 * check if category exists by title
	 * check in all cats except the current category id
	 */
	private function isCatExistsByTitle($title, $type = "", $catID = null){
		
		if(empty($type))
			$type = UniteCreatorDB::ISNULL;
		
		$arrWhere = array();
		$arrWhere["title"] = $title;
		$arrWhere["type"] = $type;
		
		$response = $this->db->fetch(GlobalsUC::$table_categories, $arrWhere);
		
		if(empty($response))
			return(false);
		
		//check by catID
		if(empty($catID))
			return(true);
		
		$cat = $response[0];
		if($cat["id"] == $catID)
			return(false);
		else
			return(true);		
	}
	
	
	/**
	 * 
	 * get category
	 */
	public function getCat($catID){
		$catID = (int)$catID;
		
		$arrCat = $this->db->fetchSingle(GlobalsUC::$table_categories,"id=$catID");
		if(empty($arrCat))
			UniteFunctionsUC::throwError("Category with id: $catID not found");
			
		return($arrCat);
	}
	
	
	/**
	 * get category type by id
	 */
	public function getCatType($catID){
		
		$arrCat = $this->getCat($catID);
		$type = UniteFunctionsUC::getVal($arrCat, "type");
		
		return($type);
	}
	
	
	/**
	 * get category by title
	 * if not found - return null
	 */
	public function getCatByTitle($title, $type=""){
		
		if(empty($type))
			$type = UniteCreatorDB::ISNULL;
		
		$arrWhere = array();
		$arrWhere["title"] = $title;
		$arrWhere["type"] = $type;
		
		try{
			$arrCat = $this->db->fetchSingle(GlobalsUC::$table_categories, $arrWhere);
		
		if(empty($arrCat))
			return(null);
		
		return($arrCat);
		
		}catch(Exception $e){
			return(null);
		}
	}
	
	
	/**
	 *
	 * get items for select categories
	 */
	public function getHtmlSelectCats($type){
		
		$arrCats = $this->getListSimple($type);
		
		$html = "";
		foreach($arrCats as $cat):
			$catID = $cat["id"];
			$title = $cat["title"];
			$html .= "<option value=\"{$catID}\">{$title}</option>";
		endforeach;
	
		return($html);
	}
	
	
	private function a______________CATLIST____________(){}
	
	
	/**
	 * get categories list from data
	 */
	public function getCatListFromData($data){
		
		$selectedCat = UniteFunctionsUC::getVal($data, "selected_catid");
		$filterActive = UniteFunctionsUC::getVal($data, "filter_active");
		$type = UniteFunctionsUC::getVal($data, "addontype");
		
		UniteCreatorAddons::setStateFilterActive($filterActive);
		
		$htmlCats = $this->getHtmlCatList($selectedCat, $type);
		
		$response = array();
		$response["htmlCats"] = $htmlCats;
		
		return($response);
	}
	
	
	/**
	 * get list of categories
	 */
	public function getHtmlCatList($selectedCatID = null, $type, $arrCats = null){
		
		if($arrCats == null)
			$arrCats = $this->getListExtra($type);
				
		$html = "";
		
		foreach($arrCats as $index => $cat):
			
			$id = UniteFunctionsUC::getVal($cat, "id");
			
			$class = "";
			if($index == 0)
				$class = "first-item";
			
			if(is_numeric($selectedCatID))
				$selectedCatID = (int)$selectedCatID;
			
			if(is_numeric($id))
				$id = (int)$id;
			
			//select item
			if($selectedCatID !== null && $id === $selectedCatID){
				if(!empty($class))
					$class .= " ";
				$class .= "selected-item";			
			}
	
		
			$html .= $this->getCatHTML($cat, $class);
			
		endforeach;
	
		return($html);
	}
	
	
	/**
	 *
	 * get html of category
	 */
	private function getCatHTML($cat, $class = ""){
		
		$id = UniteFunctionsUC::getVal($cat, "id");
				
		$isweb = UniteFunctionsUC::getVal($cat, "isweb");
		
		$title = $cat["title"];
		$numAddons = $cat["num_addons"];
		
		$title = $cat["title"];
		$numAddons = $cat["num_addons"];
		
		$showTitle = $title;
	
		if(!empty($numAddons))
			$showTitle .= " ($numAddons)";
		
		$dataTitle = htmlspecialchars($title);
		
		$classIcon = "";
		
		$addHtml = "";
		if($isweb == true){
			$addHtml .= " data-isweb='true'";
			$class .= " uc-isweb";
		}else{
			
			//get cat params			
			$objCat = new UniteCreatorCategory();
			$objCat->initByRecord($cat);
			$classIcon = $objCat->getParam("icon");
		}
		
		if(!empty($class))
			$class = "class=\"{$class}\"";
					
		
		$html = "";
		$html .= "<li id=\"category_{$id}\" {$class} data-id=\"{$id}\" data-numaddons=\"{$numAddons}\" data-title=\"{$dataTitle}\" {$addHtml}>\n";
		
		//add icon
		if(!empty($classIcon))
			$showTitle = "<i class='uc-cat-icon $classIcon'></i>".$showTitle;
		
		$html .= "	<span class=\"cat_title\">{$showTitle}</span>\n";
		if($isweb == true){
			$webText = __("Web",ADDONLIBRARY_TEXTDOMAIN);
			$html .= "<div class=\"uc-state-label uc-state-free\">\n";
			$html .= "<div class=\"uc-state-label-text\">$webText</div>\n";
			$html .= "</div>\n";
		}
		
		$html .= "</li>\n";
	
		return($html);
	}
	
	
	private function a______________GET_SORTED_CATLIST____________(){}
	
	
	/**
	 * get sorted category list
	 * clean this function
	 */
	public function getLayoutsCatsListFromData($data){
		
		$addType = "all_uncat";
		$type = UniteFunctionsUC::getVal($data, "type");
		$orderParam = UniteFunctionsUC::getVal($data, "sort");
		$filterWord = UniteFunctionsUC::getVal($data, "filter_word");
		
		if($orderParam == 'z-a') 
			$orderParam = "DESC";
		else 
			$orderParam = "ASC";
		 
		$arrCats = $this->getListExtraLayouts($filterWord, $orderParam);
		
		$response = array();
		$response["cats_list"] = $arrCats;
		
		return($response);
	}
	
	
	
	private function a______________SETTERS____________(){}
	
	
	/**
	 * get category id by title. If the title not exists - create it 
	 */
	public function getCreateCatByTitle($title, $type="", $catData = null){
		
		//if found, return id
		$arrCat = $this->getCatByTitle($title, $type);
		if(!empty($arrCat)){
			$catID = $arrCat["id"];
			return($catID);
		}
		
		try{
			
			$objCategory = new UniteCreatorCategory();			
			$createData = $objCategory->add($title, $type, $catData);
			$catID = $createData["id"];
			
			return($catID);
			
		}catch(Exception $e){
			
			return(0);
		}
		
	}
	
	
	/**
	 * 
	 * remove the category.
	 */
	private function remove($catID){
		$catID = (int)$catID;
		
		$arrCat = $this->getCat($catID);
		$type = UniteFunctionsUC::getVal($arrCat, "type");
		
		//remove category
		$this->db->delete(GlobalsUC::$table_categories,"id=".$catID);
				
		//do action by type after remove
		switch($type){
			case self::TYPE_LAYOUT:	
				$this->db->runSql("UPDATE ".GlobalsUC::$table_layouts." SET catid='0' WHERE catid='".$catID."'");
			break;
			default:	//remove addons
				$this->db->delete(GlobalsUC::$table_addons,"catid=".$catID);
			break;
		}
		
	}
	
	
	
	
	/**
	 * update category type
	 */
	public function updateType($catID, $type){
		$catID = (int)$catID;
		
		$this->validateCatExist($catID);
		
		$arrUpdate = array();
		$arrUpdate["type"] = $type;
		$this->db->update(GlobalsUC::$table_categories,$arrUpdate,array("id"=>$catID));
	}
	
	
	/**
	 * 
	 * update categories order
	 */
	private function updateOrder($arrCatIDs){
		
		foreach($arrCatIDs as $index=>$catID){
			$order = $index+1;
			$arrUpdate = array("ordering"=>$order);
			$where = array("id"=>$catID);
			$this->db->update(GlobalsUC::$table_categories,$arrUpdate,$where);
		}
	}
	
	
	/**
	 * 
	 * remove category from data
	 */
	public function removeFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "catID");
		$type = UniteFunctionsUC::getVal($data, "type");
		
		$this->remove($catID, $type);
				
		$response = array();
		$response["htmlSelectCats"] = $this->getHtmlSelectCats($type);
		
		return($response);
	}
	
	/**
	* get catagory object from data
	*/
	private function getObjCatFromData($data){
		
		$catID = UniteFunctionsUC::getVal($data, "cat_id");
		if(empty($catID))
			$catID = UniteFunctionsUC::getVal($data, "id");
					
		$objCat = new UniteCreatorCategory();
		$objCat->initByID($catID);
		
		return($objCat);
	}
	
	
	/**
	 * 
	 * update category from data
	 */
	public function updateFromData($data){
				
		$objCat = $this->getObjCatFromData($data);
				
		$objCat->updateFromData($data);
				
	}
	
	
	/**
	 * 
	 * update order from data
	 */
	public function updateOrderFromData($data){
		$arrCatIDs = UniteFunctionsUC::getVal($data, "cat_order");
		if(is_array($arrCatIDs) == false)
			UniteFunctionsUC::throwError("Wrong categories array");
			
		$this->updateOrder($arrCatIDs);
	}
	
	
	/**
	 * 
	 * add catgory from data, return cat select html list
	 */
	public function addFromData($data){
				
		$title = UniteFunctionsUC::getVal($data, "catname");
		$type = UniteFunctionsUC::getVal($data, "type");
				
		$objCategory = new UniteCreatorCategory();
		$response = $objCategory->add($title, $type);
		
		
		$arrCat = array("id"=>$response["id"],"title"=>$response["title"],"num_addons"=>0);
		$html = $this->getCatHTML($arrCat);
		
		$response["message"] = __("Category Added", ADDONLIBRARY_TEXTDOMAIN);
		$response["htmlSelectCats"] = $this->getHtmlSelectCats($type);
		$response["htmlCat"] = $html;
		
		return($response);
		
	}
	
	
}

?>