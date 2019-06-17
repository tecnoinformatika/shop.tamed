<?php

$pathProvider = dirname(__FILE__)."/";

if(GlobalsUC::$is_admin){
	require_once $pathProvider . 'provider_gridbuilder.class.php';
}

require_once $pathProvider . 'core/include_provider_core.php';

require_once $pathProvider . 'widget_layout.class.php';


HelperProviderUC::registerPlugins();
