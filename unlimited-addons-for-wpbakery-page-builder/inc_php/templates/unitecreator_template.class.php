<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorTemplate extends UniteElementsBaseUC{
	
	protected $id, $title, $params, $ordering;

	
	/**
	 * construct the layout
	 */
	public function __construct(){
		parent::__construct();
		
	}
	
	/**
	 * validate tempalte is inited
	 * Enter description here ...
	 */
	private function validateInited(){
		
		if(empty($this->id))
			UniteFunctionsUC::throwError("Template is not inited");
	}
	
	/**
	 * init layout by record
	 */
	protected function initByRecord($record){
		
		$this->title = UniteFunctionsUC::getVal($record, "title");
		$this->ordering = UniteFunctionsUC::getVal($record, "ordering");
						
		$params = UniteFunctionsUC::getVal($record, "params");
		if(!empty($params))
			$this->params = UniteFunctionsUC::jsonDecode($params);
		
		if(empty($this->params))
			$this->params = array();
		
	}
	
	/**
	 * get title
	 */
	public function getTitle($encodeChars = false){
		
		$title = $this->title;
		
		if($encodeChars == true)
			$title = htmlspecialchars($title);
		
		
		return($title);
	}
	
	
	/**
	 * get template ID
	 */
	public function getID(){
		
		$this->validateInited();
		return($this->id);
	}
	
	
	/**
	 * 
	 * get params
	 */
	public function getParams(){
		
		$params = $this->params;
		$params["title"] = $this->title;
		
		return($params);
	}
	
	
	/**
	 * init layout by id
	 */
	public function initByID($id){
		
		$id = (int)$id;
		if(empty($id))
			UniteFunctionsUC::throwError("Empty layout ID");
		
		$options = array();
		$options["tableName"] = GlobalsUC::$table_templates;
		$options["where"] = "id=".$id;
		$options["errorEmpty"] = "template with id: {$id} not found";
		
		$record = $this->db->fetchSingle($options);
		
		$this->id = $id;
		
		$this->initByRecord($record);
	}
	
	
	/**
	 * create layout from data
	 */
	public function create($params){
		
		$title = UniteFunctionsUC::getVal($params, "title");
		
		unset($params["title"]);
		
		UniteFunctionsUC::validateNotEmpty($title, __("Template Title", ADDONLIBRARY_TEXTDOMAIN));
		
		$arrInsert = array();
		$arrInsert["title"] = $title;
		$jsonParams = json_encode($params);
		$arrInsert["params"] = $jsonParams;
		
		$id = $this->db->createObjectInDB(GlobalsUC::$table_templates, $arrInsert);
		
		return($id);
	}
	
	
	/**
	 * delete layout
	 */
	public function delete(){
		$this->validateInited();
		
		$this->db->delete(GlobalsUC::$table_templates, "id=".$this->id);
	}
	
	
     /**
	 * get layout category info
	 */
    public function getCategoryInfo(){
		
		$this->validateInited();
        $category_info = array("templateid" => $this->getID());
        $objCats = new UniteCreatorCategories();
         
       	 $cat_id = UniteFunctionsUC::getVal($this->record, "catid");
         
          if(empty($cat_id)){
		    $category_info["title"] = "Uncategorized";
		    $category_info["id"] = 0;
		    
		  } else {
             $cat = $objCats->getCat($cat_id);
             $category_info["title"] = $cat["title"];
			 $category_info["id"] = $cat["id"];
            }
		return $category_info;
	}
	
	
}