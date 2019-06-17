<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


if($this->showHeader)
	$this->putHeaderHtml();


$slot1AddHtml = "";
if($this->isTestData1 == false)
	$slot1AddHtml = "style='display:none'";


$styleShow = "";
$styleHide = "style='display:none'";


?>

<div id="uc_testaddon_wrapper" class="uc-testaddon-wrapper">

<?php if($this->showToolbar):?>

<div class="uc-testaddon-panel">
		
		<a href="<?php echo $urlEditAddon?>" class="unite-button-secondary" ><?php _e("Edit This Addon", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<a class="unite-button-secondary uc-button-cat-sap" href="<?php echo HelperUC::getViewUrl_Addons($addonType)?>"><?php _e("Back to Addons List", ADDONLIBRARY_TEXTDOMAIN);?></a>
		
		<a id="uc_button_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo $isPreviewMode?$styleHide:$styleShow?>><?php _e("To Preview", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<a id="uc_button_close_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo $isPreviewMode?$styleShow:$styleHide?>><?php _e("Hide Preview", ADDONLIBRARY_TEXTDOMAIN)?></a>
		
		<a id="uc_button_preview_tab" href="javascript:void(0)" class="unite-button-secondary uc-button-cat-sap"><?php _e("Preview New Tab", ADDONLIBRARY_TEXTDOMAIN)?></a>
		
		<span id="uc_testaddon_slot1" class="uc-testaddon-slot" <?php echo $slot1AddHtml?>>
			<a id="uc_testaddon_button_restore" href="javascript:void(0)" class="unite-button-secondary"><?php _e("Restore Data", ADDONLIBRARY_TEXTDOMAIN)?></a>
			<span id="uc_testaddon_loader_restore" class="loader-text" style="display:none"><?php _e("loading...")?></span>
			<a id="uc_testaddon_button_delete" href="javascript:void(0)" class="unite-button-secondary"><?php _e("Delete Data", ADDONLIBRARY_TEXTDOMAIN)?></a>
			<span id="uc_testaddon_loader_delete" class="loader-text" style="display:none"><?php _e("deleting...")?></span>
		</span>
		
		<a id="uc_testaddon_button_save" href="javascript:void(0)" class="unite-button-secondary"><?php _e("Save Data", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<span id="uc_testaddon_loader_save" class="loader-text" style="display:none"><?php _e("saving...")?></span>
		
		<a id="uc_testaddon_button_clear" href="javascript:void(0)" class="unite-button-secondary"><?php _e("Clear", ADDONLIBRARY_TEXTDOMAIN)?></a>
	
</div>

<?php endif; ?>

<form name="form_test_addon">

<?php 

	//put helper editor if needed
	
	if($isNeedHelperEditor)
		UniteProviderFunctionsUC::putInitHelperHtmlEditor();

    $addonConfig->putHtmlFrame(); 
?>

</form>

</div>

<script type="text/javascript">

	jQuery(document).ready(function(){

		var objTestAddonView = new UniteCreatorTestAddon();
		objTestAddonView.init();

		<?php if($isPreviewMode == true):?>
		jQuery("#uc_button_preview").trigger("click");
		<?php endif?>
		
	});
	
		
</script>

			



