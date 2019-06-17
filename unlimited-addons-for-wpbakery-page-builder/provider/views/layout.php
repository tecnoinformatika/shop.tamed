<?php

defined('UNLIMITED_ADDONS_INC') or die;

class AddonLibraryViewLayoutProvider extends AddonLibraryViewLayout{
	
	
	/**
	 * add toolbar
	 */
	function __construct(){
		parent::__construct();
		
		$this->shortcodeWrappers = "wp";
		$this->shortcode = "uc_layout";
		
		//$this->showButtons = false;
		//$this->showHeader = false;
		
		$this->display();
	}
	
	
}