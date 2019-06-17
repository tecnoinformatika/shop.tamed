<?php
/**
* @package Blox Page Builder
* @author UniteCMS.net
* @copyright (C) 2017 Unite CMS, All Rights Reserved. 
* @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
if(!defined('UNLIMITED_ADDONS_INC'))
    define('UNLIMITED_ADDONS_INC', true);

if(!defined('ADDON_LIBRARY_INC'))
    define('ADDON_LIBRARY_INC', true);
    
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');
        
if(!defined("ADDON_LIBRARY_VERSION"))
	define("ADDON_LIBRARY_VERSION", "1.0.40");


$currentFile = __FILE__;
$currentFolder = dirname($currentFile);
$folderIncludesMain = $currentFolder."/inc_php/";

//include frameword files
require_once $folderIncludesMain . 'framework/include_framework.php';

require_once $folderIncludesMain . 'unitecreator_globals.class.php';
require_once $folderIncludesMain . 'unitecreator_operations.class.php';
require_once $folderIncludesMain . 'unitecreator_category.class.php';
require_once $folderIncludesMain . 'unitecreator_categories.class.php';
require_once $folderIncludesMain . 'unitecreator_addon.class.php';
require_once GlobalsUC::$pathProvider . 'provider_addon.class.php';
require_once $folderIncludesMain . 'unitecreator_params_processor.class.php';
require_once GlobalsUC::$pathProvider . 'provider_params_processor.class.php';
require_once $folderIncludesMain . 'unitecreator_addons.class.php';
require_once $folderIncludesMain . 'unitecreator_helper.class.php';
require_once $folderIncludesMain . 'unitecreator_helperhtml.class.php';
require_once $folderIncludesMain . 'unitecreator_output.class.php';
require_once GlobalsUC::$pathProvider . 'provider_output.class.php';
require_once $folderIncludesMain . 'unitecreator_variables_output.class.php';
require_once $folderIncludesMain . 'unitecreator_actions.class.php';

require_once $folderIncludesMain . 'unitecreator_template_engine.class.php';
require_once GlobalsUC::$pathProvider . 'provider_template_engine.class.php';
require_once $folderIncludesMain . 'unitecreator_settings.class.php';
require_once GlobalsUC::$pathProvider . 'provider_settings.class.php';

require_once $folderIncludesMain . 'unitecreator_library.class.php';
require_once $folderIncludesMain . 'unitecreator_web_api.class.php';

require_once $folderIncludesMain . 'plugins/unitecreator_plugin_filters.class.php';
require_once $folderIncludesMain . 'plugins/unitecreator_plugin_base.class.php';
require_once $folderIncludesMain . 'plugins/unitecreator_plugins.class.php';

require_once $folderIncludesMain . 'layouts/unitecreator_layout.class.php';
require_once GlobalsUC::$pathProvider . 'provider_layout.class.php';
require_once $folderIncludesMain . 'layouts/unitecreator_layout_output.class.php';
require_once GlobalsUC::$pathProvider . 'provider_layout_output.class.php';
require_once GlobalsUC::$pathProvider . 'provider_library.class.php';
require_once GlobalsUC::$pathProvider . 'provider_library.class.php';
require_once $folderIncludesMain . 'unitecreator_dialog_param.class.php';
require_once GlobalsUC::$pathProvider."provider_dialog_param.class.php";
require_once $folderIncludesMain . 'templates/unitecreator_template.class.php';
require_once $folderIncludesMain . 'unitecreator_form.class.php';



//admin only, maybe split later
if(GlobalsUC::$is_admin){

	require_once $folderIncludesMain . 'unitecreator_client_text.php';
	require_once $folderIncludesMain . 'unitecreator_assets.class.php';
	require_once $folderIncludesMain . 'unitecreator_assets_work.class.php';
	require_once $folderIncludesMain . 'manager/unitecreator_manager.class.php';
	require_once $folderIncludesMain . 'manager/unitecreator_manager_pages.class.php';
	require_once $folderIncludesMain . 'manager/unitecreator_manager_addons.class.php';
	require_once $folderIncludesMain . 'manager/unitecreator_manager_inline.class.php';
	require_once $folderIncludesMain . 'unitecreator_browser.class.php';
	require_once $folderIncludesMain . 'unitecreator_addon_config.class.php';
	require_once $folderIncludesMain . 'unitecreator_dialog_param.class.php';
	require_once $folderIncludesMain . 'unitecreator_params_editor.class.php';
	require_once $folderIncludesMain . 'unitecreator_exporter_base.class.php';
	require_once $folderIncludesMain . 'unitecreator_exporter.class.php';
	require_once $folderIncludesMain . 'unitecreator_settingsview.class.php';
	require_once $folderIncludesMain . 'layouts/unitecreator_grid_builder.class.php';
	require_once $folderIncludesMain . 'layouts/unitecreator_grid_builder_panel.class.php';
	require_once $folderIncludesMain . 'layouts/unitecreator_layouts.class.php';
	require_once GlobalsUC::$pathProvider . 'provider_layouts.class.php';
	require_once $folderIncludesMain . 'layouts/unitecreator_layouts_exporter.class.php';
	require_once GlobalsUC::$pathProvider . 'provider_laytouts_exporter.class.php';
	require_once $folderIncludesMain . 'templates/unitecreator_templates.class.php';
}

 $filepathIncludeProviderAfter = GlobalsUC::$pathProvider."include_provider_after.php";
 if(file_exists($filepathIncludeProviderAfter))
 	require_once $filepathIncludeProviderAfter;
 
 GlobalsUC::initAfterIncludes();
 
 
?>