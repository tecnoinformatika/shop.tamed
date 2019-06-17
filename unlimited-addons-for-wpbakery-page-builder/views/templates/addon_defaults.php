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
if($this->isDataExists == false)
	$slot1AddHtml = "style='display:none'";


$styleShow = "";
$styleHide = "style='display:none'";

$strOptions = UniteFunctionsUC::jsonEncodeForHtmlData($arrOptions);

?>

<div id="uc_addondefaults_wrapper" class="uc-addondefaults-wrapper" data-options="<?php echo $strOptions?>">

<?php if($this->showToolbar):?>

<div class="uc-addondefaults-panel">
		
		<div class="uc-panel-save-wrapper">
			<a id="uc_addondefaults_button_save" href="javascript:void(0)" class="unite-button-primary"><?php _e("Save Defaults", ADDONLIBRARY_TEXTDOMAIN)?></a>
			<span id="uc_addondefaults_loader_save" class="loader-text" style="display:none"><?php _e("saving...")?></span>
		</div>
				
		<a id="uc_button_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo $isPreviewMode?$styleHide:$styleShow?>><?php _e("To Preview", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<a id="uc_button_close_preview" href="javascript:void(0)" class="unite-button-secondary" <?php echo $isPreviewMode?$styleShow:$styleHide?>><?php _e("Hide Preview", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<span class="hor_sap10"></span>
				
		<a id="uc_button_preview_tab" href="javascript:void(0)" class="unite-button-secondary uc-button-cat-sap"><?php _e("Preview New Tab", ADDONLIBRARY_TEXTDOMAIN)?></a>
		
		<span class="hor_sap30"></span>
		
		<a href="<?php echo $urlEditAddon?>" class="unite-button-secondary" ><?php _e("Edit This Addon", ADDONLIBRARY_TEXTDOMAIN)?></a>
		<span class="hor_sap15"></span>
		
		<a class="unite-button-secondary uc-button-cat-sap" href="<?php echo HelperUC::getViewUrl_Addons($addonType)?>"><?php _e("Back to Addons List", ADDONLIBRARY_TEXTDOMAIN);?></a>
		
</div>

<?php endif; ?>

<form name="form_addon_defaults">

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
		
		var objAddonDefaultsView = new UniteCreatorAddonDefaultsAdmin();
		objAddonDefaultsView.init();
		
		<?php if($isPreviewMode == true):?>
		jQuery("#uc_button_preview").trigger("click");
		<?php endif?>
		
	});
	
		
</script>

