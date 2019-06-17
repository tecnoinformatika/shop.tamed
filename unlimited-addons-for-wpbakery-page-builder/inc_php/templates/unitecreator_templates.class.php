<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorTemplates extends UniteElementsBaseUC{
	
	
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
	 * update template from data
	 */
	private function updateTemplateFromData($data){
		
		
		dmp("update tempate");
		dmp($data);
		exit();
		
	}
	
	
	/**
	 * update template from data
	 */
	private function createTemplateFromData($data){
		
		$params = UniteFunctionsUC::getVal($data, "params");
		
		$template = new UniteCreatorTemplate();
		$templateID = $template->create($params);
				
		return($templateID);
	}
	
	
	/**
	 * create or update template from data
	 */
	public function createUpdateTemplateFromData($data){
		
		$templateID = UniteFunctionsUC::getVal($data, "templateid");
		if(empty($templateID)){
			
			$templateID = $this->createTemplateFromData($data);
			
			return($templateID);
		}
		else
			$response = $this->updateTemplateFromData($data);
			
	}
	
	
	/**
	 * get layout from data
	 */
	private function getTemplateFromData($data){
			
		$templateID = UniteFunctionsUC::getVal($data, "id");
		UniteFunctionsUC::validateNumeric($templateID);
		UniteFunctionsUC::validateNotEmpty($templateID);
		
		$objTemplate = new UniteCreatorTemplate();
		$objTemplate->initByID($templateID);
		
		return($objTemplate);
	}
	
	
	/**
	 * delete layout from data
	 */
	public function deleteTemplateFromData($data){
		
		$objTemplate = $this->getTemplateFromData($data);
		
		$objTemplate->delete();
		
	}
	
	
	
}