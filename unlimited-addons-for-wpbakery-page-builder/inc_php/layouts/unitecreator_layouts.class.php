<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayoutsWork extends UniteElementsBaseUC{
	
	/**
	 * get layout where from params
	 */
	private function getWhereFromParams($params){
		
		$where = array();
		
		$catID = UniteFunctionsUC::getVal($params, "catid");
		if(!empty($catID))
			$where["catid"] = $catID;
		
		return($where);
	}
	
	
	/**
	 * get galleries array
	 */
	private function getRecords($order = "ordering", $params = array()){
		
		if(empty($order))
			$order = "ordering";
		
		$where = $this->getWhereFromParams($params);
		
		$response = $this->db->fetch(GlobalsUC::$table_layouts, $where, $order);
		
		return($response);
	}
	
	
	/**
	 * get records with paging
	 */
	private function getRecordsPaging($pagingOptions){
		
		$where = "";
				
		//search
		$search = UniteFunctionsUC::getVal($pagingOptions, "search");
				
		if(!empty($search))	{
			$search = $this->db->escape($search);
			$where = "title LIKE '%{$search}%'";
		}
		
		$order = UniteFunctionsUC::getVal($pagingOptions, "ordering");
		
		$filterCat = UniteFunctionsUC::getGetVar("category", "", UniteFunctionsUC::SANITIZE_TEXT_FIELD);
		
		if(!empty($filterCat) && $filterCat != '-'){
			$filterCat = (int)$filterCat;
			
			if(!empty($search))
				$search .= "AND catid LIKE '$filterCat' ";
			else 
				$search = "catid = '".$filterCat."' ";
		}
		
		$response = $this->db->fetchPage(GlobalsUC::$table_layouts, $pagingOptions, $where, $order);
		
		
		return($response);
	}
	
	
	/**
	 * get layout from data
	 */
	private function getLayoutFromData($data){
	
		$layoutID = UniteFunctionsUC::getVal($data, "layout_id");
		UniteFunctionsUC::validateNumeric($layoutID);
		UniteFunctionsUC::validateNotEmpty($layoutID);
	
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
	
		return($objLayout);
	}
	
	
	/**
	 * convert records to layouts objects
	 */
	private function recordsToLayouts($records){
		
		$arrLayouts = array();
		foreach($records as $record){
			$layoutID = UniteFunctionsUC::getVal($record, "id");
			$objLayout = new UniteCreatorLayout();
			$objLayout->initByID($layoutID);
		
			$arrLayouts[] = $objLayout;
		}
		
		return($arrLayouts);
	}
	
	
	/**
	 *
	 * get addons array
	 */
	public function getArrLayouts($order = "ordering", $params = array()){
		
		$response = $this->getRecords($order, $params);
		
		$arrLayouts = $this->recordsToLayouts($response);
		
		return($arrLayouts);
	}
	
	
	/**
	 * convert records to short
	 */
	private function recordsToShort($records){
		
		$arrShort = array();
		
		foreach($records as $record){
			
			
			dmp($record);
			exit();
		}
		
	}
	
	
	/**
	 * get layouts with paging data
	 */
	public function getArrLayoutsPaging($pagingOptions){
		
		$response = $this->getRecordsPaging($pagingOptions);
		
		$rows = $response["rows"];
		unset($response["rows"]);
		
		$arrLayouts = $this->recordsToLayouts($rows);
		
		$output = array();
		$output["paging"] = $response;
		$output["layouts"] = $arrLayouts;
		
		return($output);
	}
	
	
	/**
	 * get layouts array short version - without content
	 */
	public function getArrLayoutsShort($addEmpty = false, $params = array()){
		
		$where = $this->getWhereFromParams($params);
		
		$arrLayouts = $this->db->fetchFields(GlobalsUC::$table_layouts, "id, title", $where, "ordering");
		if(empty($arrLayouts))
			$arrLayouts = array();
		
		if($addEmpty == true){
			$arrItem = array("id"=>"empty", "title"=>"[Not Selected]");
			$arrAdd = array($arrItem);
			
			$arrLayouts = array_merge($arrAdd, $arrLayouts);
		}
		
		
		return($arrLayouts);
	}
	
	/**
	 * update category layout
	 * @param unknown_type $data
	 */
	public function updateLayoutCategoryFromData($data){
				
		$layoutID = UniteFunctionsUC::getVal($data, "layoutid");
		$catID = UniteFunctionsUC::getVal($data, "catid");
		
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		$objLayout->updateCategory($catID);
		
	}
	
	
	/**
	 * update layout properties from data
	 */
	public function updateParamsFromData($data){
		
		$layoutID = UniteFunctionsUC::getVal($data, "layoutid");
		$params = UniteFunctionsUC::getVal($data, "params");
		
		
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		$objLayout->updateParams($params);
	}
	
	
	/**
	 * update layout from data
	 */
	public function updateLayoutFromData($data){
				
		$layoutID = UniteFunctionsUC::getVal($data, "layoutid");
		
		$isTitleOnly = UniteFunctionsUC::getVal($data, "title_only");
		$isTitleOnly = UniteFunctionsUC::strToBool($isTitleOnly);
		
		$objLayout = new UniteCreatorLayout();
		
		$isUpdate = false;
		
		if(empty($layoutID)){
			
			$layoutID = $objLayout->create($data);
			$message = HelperUC::getText("layout_created");
			
		}else{
			
			$isUpdate = true;
			
			//update layout
			$objLayout->initByID($layoutID);
			
			if($isTitleOnly == true){
				$title = UniteFunctionsUC::getVal($data, "title");
				$objLayout->updateTitle($title);
				$message = __("Title Saved",ADDONLIBRARY_TEXTDOMAIN);
				
			}else{
				$objLayout->update($data);
				$message = HelperUC::getText("layout_updated");
				
			}
			
		}
		
		$response = array();
		$response["is_update"] = $isUpdate;
		$response["layout_id"] = $layoutID;
		$response["message"] = $message;
		
		return($response);
	}
	
	
	
	/**
	 * delete layout from data
	 */
	public function deleteLayoutFromData($data){
		
		$objLayout = $this->getLayoutFromData($data);
		
		$objLayout->delete();
		
	}
	
	/**
	 * duplicate layout from data
	 */
	public function duplicateLayoutFromData($data){
		
		$objLayout = $this->getLayoutFromData($data);
		
		$objLayout->duplicate();
		
	}
	
	
	/**
	 * check if layout exists by title
	 */
	public function isLayoutExistsByTitle($title){
				
		$response = $this->db->fetch(GlobalsUC::$table_layouts, array("title"=>$title));
	
		$isExists = !empty($response);
	
		return($isExists);
	}
	
	
	/**
	 * get layout name, and find name that don't exists in database using counter	 *
	 */
	public function getUniqueTitle($title){
	
		$counter = 1;
		
		$isExists = $this->isLayoutExistsByTitle($title);
				
		if($isExists == false)
			return($title);
				
		$limit = 1;
		while($isExists == true && $limit < 10){
			$limit++;
			$counter++;
			$newTitle = $title."-".$counter;
			$isExists = $this->isLayoutExistsByTitle($newTitle);
		}
		
		return($newTitle);
	}
	
	
	/**
	 *
	 * get max order from categories list
	 */
	public function getMaxOrder(){
	
		$tableLayouts = GlobalsUC::$table_layouts;
		
		$query = "select MAX(ordering) as maxorder from {$tableLayouts}";
		
		$rows = $this->db->fetchSql($query);
	
		$maxOrder = 0;
		if(count($rows)>0)
			$maxOrder = $rows[0]["maxorder"];
	
		if(!is_numeric($maxOrder))
			$maxOrder = 0;
	
		return($maxOrder);
	}
	
	
	/**
	 * shift addons in category from some order (more then the order).
	 */
	public function shiftOrder($order){
		
		UniteFunctionsUC::validateNumeric($order);
		
		$tableLayouts = GlobalsUC::$table_layouts;
		
		$query = "update {$tableLayouts} set ordering = ordering+1 where ordering > {$order}";
		
		$this->db->runSql($query);
	}
	
	
	/**
	 * export layout from get data
	 */
	public function exportLayout(){
		
		$layoutID = UniteFunctionsUC::getGetVar("id","",UniteFunctionsUC::SANITIZE_ID);
		$layoutID = (int)$layoutID;
		
		$objLayout = new UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		
		$exporter = new UniteCreatorLayoutsExporter();
		$exporter->initByLayout($objLayout);
		$exporter->export();
	}
	
	
	/**
	 * import layouts
	 */
	public function importLayouts($data){
		
		$layoutID = UniteFunctionsUC::getVal($data, "layoutID");
		if(!empty($layoutID))
			$layoutID = (int)$layoutID;
		
		$arrTempFile = UniteFunctionsUC::getVal($_FILES, "import_layout");
		
		$isOverwriteAddons = UniteFunctionsUC::getVal($data, "overwrite_addons");
		
		
		$exporter = new UniteCreatorLayoutsExporter();
		$exporter->import($arrTempFile, $layoutID, $isOverwriteAddons);
		
		if(empty($layoutID))
			$urlRedirect = HelperUC::getViewUrl_LayoutsList();
		else
			$urlRedirect = HelperUC::getViewUrl_Layout($layoutID);

		
		return($urlRedirect);
	}
	
	
	/**
	 * get items by id's
	 */
	private function getLayoutsByIDs($arrLayoutIDs){
		
		$tableLayouts = GlobalsUC::$table_layouts;
		$arrLayouts = $this->db->fetchByIDs($tableLayouts, $arrLayoutIDs);
		
		
		return($arrLayouts);
	}
	
		
	
	/**
	 *
	 * save items order from data
	 */
	public function updateOrderFromData($data){
		
		$layoutsIDs = UniteFunctionsUC::getVal($data, "layouts_order");
		if(empty($layoutsIDs))
			return(false);
		
		$this->db->updateRecordsOrdering(GlobalsUC::$table_layouts, $layoutsIDs);
		
	}
	
	
}