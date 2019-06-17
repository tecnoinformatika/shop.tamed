<?php

defined('UNLIMITED_ADDONS_INC') or die;

class UniteCreatorAddonViewProvider extends UniteCreatorAddonView{
	
	
	/**
	 * get thumb sizes
	 */
	protected function getThumbSizes(){
		
		$arrThumbSizes = UniteFunctionsWPUC::getArrThumbSizes();
		
		//modify sizes
		$arrSizesModified = array();
		
		foreach($arrThumbSizes as $key => $size){
			
			if($key == "medium")
				continue;
				
			$key = str_replace("-", "_", $key);
			
			$arrSizesModified[$key] = $size;
		}
		
		return($arrSizesModified);
	}
	
	
}