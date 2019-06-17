<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net
 * @copyright (C) 2012 Unite CMS, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

class UniteCreatorLayoutOutput extends UniteCreatorLayoutOutputWork{

	/**
	 * construct
	 */
	public function __construct(){
		
		$this->isScriptsHardCoded = false;		//the js scripts are put not hard coded
		$this->cssToBody = true;
		
	}
	
	
}

