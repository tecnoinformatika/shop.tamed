<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

require_once GlobalsUC::$pathViewsObjects."addon_view.class.php";


$pathProviderAddon = GlobalsUC::$pathProvider."views/addon.php";

if(file_exists($pathProviderAddon) == true){
	require_once $pathProviderAddon;
	new UniteCreatorAddonViewProvider();
}
else{
	new UniteCreatorAddonView();
}

