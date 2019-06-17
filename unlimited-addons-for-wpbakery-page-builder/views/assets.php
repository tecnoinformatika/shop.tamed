<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


$headerTitle = __("Assets Manager", ADDONLIBRARY_TEXTDOMAIN);
require HelperUC::getPathTemplate("header");


$objAssets = new UniteCreatorAssetsWork();
$objAssets->initByKey("assets_manager");

?>
<div class="uc-assets-manager-wrapper">

	<?php 
	$objAssets->putHTML();
	?>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
	
		var objAdmin = new UniteCreatorAdmin();
		objAdmin.initAssetsManagerView();
	
	});

</script>
<?php 