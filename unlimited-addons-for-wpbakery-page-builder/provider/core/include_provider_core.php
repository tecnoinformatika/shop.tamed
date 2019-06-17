<?php
/**
 * @package Unlimited Addons
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');

$pathProviderCore = dirname(__FILE__)."/";

require_once $pathProviderCore . 'helper_provider_core.class.php';

if(is_admin()){
require_once $pathProviderCore . 'visual_composer/unitevc_exporter.class.php';
}

require_once $pathProviderCore . 'visual_composer/settings_output_vc.class.php';
require_once $pathProviderCore . 'visual_composer/unitevc_integrate.class.php';

