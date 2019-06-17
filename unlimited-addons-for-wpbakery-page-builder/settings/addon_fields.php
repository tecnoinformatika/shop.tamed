<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

		$filepathAddonSettings = GlobalsUC::$pathSettings."addon_fields.xml";
		
		UniteFunctionsUC::validateFilepath($filepathAddonSettings);
		
		$generalSettings = new UniteCreatorSettings();
		
		if(isset($this->objAddon)){
			$generalSettings->setCurrentAddon($this->objAddon);
		}
		
		$generalSettings->loadXMLFile($filepathAddonSettings);
