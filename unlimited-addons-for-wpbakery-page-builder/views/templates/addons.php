<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


if($this->showHeader == true){
	if(!isset($headerTitle))
		$headerTitle = __("Manage Addons", ADDONLIBRARY_TEXTDOMAIN);
	require HelperUC::getPathTemplate("header");
}

?>
	
	<?php 
		if($this->showButtons == true)
			UniteProviderFunctionsUC::putAddonViewAddHtml()
	?>
	
	<div class="content_wrapper">
		<?php $objManager->outputHtml() ?>
	</div>

	<?php 
		
		if(method_exists("UniteProviderFunctionsUC", "putUpdatePluginHtml"))
			UniteProviderFunctionsUC::putUpdatePluginHtml();
	
	?>