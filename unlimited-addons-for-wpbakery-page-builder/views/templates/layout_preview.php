<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

$urlLayoutsList = HelperUC::getViewUrl_LayoutsList();
$urlEdit = HelperUC::getViewUrl_Layout($layoutID);

if($this->showHeader == true){
	$headerTitle = $this->getHeaderTitle();
	require HelperUC::getPathTemplate("header");
}

?>

<div class="unite-content-wrapper unite-inputs">
	
		<?php if($this->showToolbar == true):?>
		<div class="uc-layout-preview-toolbar">
			<a href="<?php echo $urlLayoutsList?>" class="unite-button-secondary"><?php HelperUC::putText("back_to_layouts_list");?></a>
			<a href="<?php echo $urlEdit?>" class="mleft_10 unite-button-secondary"><?php HelperUC::putText("edit_layout")?></a>
		</div>
		<?php endif?>
		
		
		<div class="uc-layout-preview-wrapper">
		
			<?php HelperUC::outputLayout($layoutID); ?>
			
			<div class="unite-clear"></div>
		</div>
		
</div>
