<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class BloxViewLayoutOuter{
	
	protected $objPageBuilder;
	protected $objLayout;
	
	
	/**
	 * the constructor
	 */
	public function __construct(){
		
		$layoutID = UniteFunctionsUC::getGetVar("id", null, UniteFunctionsUC::SANITIZE_ID);
		
		$this->objLayout = new UniteCreatorLayout();
		
		if(!empty($layoutID)){
			$this->objLayout = new UniteCreatorLayout();
			$this->objLayout->initByID($layoutID);
		}
		
		$this->objPageBuilder = new UniteCreatorPageBuilder();
		$this->objPageBuilder->initOuter($this->objLayout);
		
	}
		
	
	/**
	 * display
	 */
	protected function display(){
		
		$this->objPageBuilder->displayOuter();
						
	}
	
}


$pathProviderLayoutOuter = GlobalsUC::$pathProvider."views/layout_outer.php";

require_once $pathProviderLayoutOuter;

new BloxViewLayoutOuterProvider();
