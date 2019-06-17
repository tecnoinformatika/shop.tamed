<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorLayoutPreview{
	
	protected $showHeader = false;
	protected $showToolbar = true;
	protected $layoutID;
	protected $layout;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$layoutID = UniteFunctionsUC::getGetVar("id", null, UniteFunctionsUC::SANITIZE_ID);
		UniteFunctionsUC::validateNotEmpty($layoutID, "Layout ID var");
		
		$this->layoutID = $layoutID;
		
		$this->layout = new UniteCreatorLayout();
		$this->layout->initByID($layoutID);
	}
	
	
	/**
	 * get header title
	 */
	protected function getHeaderTitle(){
		
		$titleText = $this->layout->getTitle();
		
		$title = HelperUC::getText("preview_layout")." - ";
		
		return($title);
	}
	
	
	/**
	 * display
	 */
	protected function display(){
		
		$layoutID = $this->layoutID;
		
		require HelperUC::getPathTemplate("layout_preview");
	}
	
	
}


$pathProviderLayout = GlobalsUC::$pathProvider."views/layout_preview.php";
require_once $pathProviderLayout;

new UniteCreatorLayoutPreviewProvider();
