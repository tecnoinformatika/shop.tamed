<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

$bottomLineClass = "";
if($view == "layout")
    $bottomLineClass = " unite-position-right";

?>

<?php HelperHtmlUC::putGlobalsHtmlOutput(); ?>

	<script type="text/javascript">
		var g_view = "<?php echo self::$view?>";
	</script>

<?php HelperHtmlUC::putInternalAdminNotices()?>


<div id="viewWrapper" class="unite-view-wrapper unite-admin unite-inputs">

<?php
	self::requireView($view);
	
	//include provider view if exists
	$filenameProviderView = GlobalsUC::$pathProviderViews.$view.".php";
	if(file_exists($filenameProviderView))
		require_once($filenameProviderView);
?>

</div>

<?php 
	$filepathProviderMasterView = GlobalsUC::$pathProviderViews."master_view.php";
	if(file_exists($filepathProviderMasterView))
		require_once $filepathProviderMasterView;
?>

<?php if(GlobalsUC::$blankWindowMode == false):?>

<div id="uc_dialog_version" title="<?php _e("Version Release Log. Current Version: ".ADDON_LIBRARY_VERSION." ", ADDONLIBRARY_TEXTDOMAIN)?>" style="display:none;">
	<div class="unite-dialog-inside">
		<div id="uc_dialog_version_content" class="unite-dialog-version-content">
			<div id="uc_dialog_loader" class="loader_text"><?php _e("Loading...", ADDONLIBRARY_TEXTDOMAIN)?></div>
		</div>
	</div>
</div>


<div class="unite-clear"></div>
<div class="unite-plugin-version-line unite-admin <?php echo $bottomLineClass?>">
	<?php UniteProviderFunctionsUC::putFooterTextLine() ?>
	<?php _e("Component verson", ADDONLIBRARY_TEXTDOMAIN)?> <?php echo ADDON_LIBRARY_VERSION?>, 
	<a id="uc_version_link" href="javascript:void(0)" class="unite-version-link">
		<?php _e("view change log", ADDONLIBRARY_TEXTDOMAIN)?>
	</a>
	
	<?php HelperHtmlUC::putPluginVersionHtml() ?>
	
	<?php UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_BOTTOM_PLUGIN_VERSION)?>
	
</div>

<?php endif?>
