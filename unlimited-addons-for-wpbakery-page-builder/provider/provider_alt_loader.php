<?php


/**
 * return if addon creator plugin exists and active
 */
function UCIsAddonLibraryPluginExists(){
	$alPlugin = "addon-library/addonlibrary.php";
	
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	$arrPlugins = get_plugins();
		
	if(isset($arrPlugins[$alPlugin]) == false)
		return(false);
	
	//$isActive = is_plugin_active($alPlugin);
	
	return(true);

}


if(UCIsAddonLibraryPluginExists()){
	require_once dirname(__FILE__)."/views/compatability_message.php";
}else{
	
	require_once $currentFolder.'/includes.php';
	require_once  GlobalsUC::$pathProvider."core/provider_main_file.php";
}

