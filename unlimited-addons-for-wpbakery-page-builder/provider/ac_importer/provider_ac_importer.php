<?php

class AddonLibraryAcImporterUC{
	
	private static $arrMenuPages = array();
	private static $arrSubMenuPages = array();
	private static $capability = "manage_options";
	private static $urlProvider;
	private static $pathImporter;
	private static $urlComponent;
	
	private static $db;
	private static $tableCreatorAddons;
	private static $tableLibraryAddons;
	private static $tableLibraryCategories;
	private static $status;
	
	const PLUGIN_NAME = "addon-library";
	const ACTION_ADMIN_MENU = "admin_menu";
	const ACTION_ADMIN_INIT = "admin_init";
	const ACTION_ADD_SCRIPTS = "admin_enqueue_scripts";
	
	private static $t;
	
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		self::$t = $this;
		
		$this->init();
	}
	
	/**
	 * get value from array. if not - return alternative
	 */
	public static function getVal($arr,$key,$altVal=""){
	
		if(isset($arr[$key]))
			return($arr[$key]);
	
		return($altVal);
	}
	
	
	/**
	 * init variables
	 */
	private function initVars(){
		$pathBase = ABSPATH;
		$pathPlugin = realpath(dirname(__FILE__)."/../../")."/";
		$pathProvider = $pathPlugin."provider/";
		$pathImporter = $pathProvider."ac_importer/";
		
		$urlPlugin = plugins_url(self::PLUGIN_NAME)."/";
		$urlProvider = $urlPlugin."provider/";
		
		self::$urlComponent = admin_url()."admin.php?page=".self::PLUGIN_NAME;
		
		self::$urlProvider = $urlProvider;
		self::$pathImporter = $pathImporter;

		
	}
	
	/**
	 * init vars only in admin pages
	 */
	public static function initAdminPagesVars(){
		//include framework
		if(class_exists("UniteCreatorDB") == false){
			require_once $pathPlugin."inc_php/framework/include_framework.php";
		}
		
		self::$db = new UniteCreatorDB();
		
		global $wpdb;
		$tablePrefix = $wpdb->base_prefix;
		
		self::$tableCreatorAddons = $tablePrefix."unitecreator_addons";
		self::$tableLibraryAddons = $tablePrefix."addonlibrary_addons";
		self::$tableLibraryCategories = $tablePrefix."addonlibrary_categories";
		
	}
	
	
	
	/**
	 *
	 * add menu page
	 */
	protected static function addMenuPage($title,$pageFunctionName,$icon=null){
		self::$arrMenuPages[] = array("title"=>$title,"pageFunction"=>$pageFunctionName,"icon"=>$icon);
	}
	
	/**
	 *
	 * add sub menu page
	 */
	protected static function addSubMenuPage($slug,$title,$pageFunctionName){
		self::$arrSubMenuPages[] = array("slug"=>$slug,"title"=>$title,"pageFunction"=>$pageFunctionName);
	}
	
	/**
	 * add admin menus from the list.
	 */
	public static function addAdminMenu(){
	
		//return(false);
		foreach(self::$arrMenuPages as $menu){
			$title = $menu["title"];
			$pageFunctionName = $menu["pageFunction"];
			$icon = self::getVal($menu, "icon");
	
			add_menu_page( $title, $title, self::$capability, self::PLUGIN_NAME, array(self::$t, $pageFunctionName), $icon );
		}
	
		foreach(self::$arrSubMenuPages as $key=>$submenu){
	
			$title = $submenu["title"];
			$pageFunctionName = $submenu["pageFunction"];
	
			$slug = self::PLUGIN_NAME."_".$submenu["slug"];
	
			if($key == 0)
				$slug = self::PLUGIN_NAME;
	
			add_submenu_page(self::PLUGIN_NAME, $title, $title, 'manage_options', $slug, array(self::$t, $pageFunctionName) );
		}
	
	}
	
	
	/**
	 *
	 * tells if the the current plugin opened is this plugin or not
	 * in the admin side.
	 */
	private function isInsidePlugin(){
		$page = self::getGetVar("page","",UniteFunctionsUC::SANITIZE_KEY);
		
		if($page == self::PLUGIN_NAME || strpos($page, self::PLUGIN_NAME."_") !== false)
			return(true);
	
		return(false);
	}
	
	/**
	 *
	 * add some wordpress action
	 */
	protected static function addAction($action,$eventFunction){
	
		add_action( $action, array(self::$t, $eventFunction) );
	}
	
	public static function a______________PUT_HTML_____________(){}
	
	/**
	 * put style
	 */
	public static function putHtmlStyle(){
		?>
		<style>
			
			.uc-importer-wrapper{
				margin-top:50px;
			}
			
			.import-done{
				font-weight:bold;
				color:green;
			}
			
			.import-now{
				font-weight:bold;
			}
			
		</style>
		
		<?php 
	}
	
	
	/**
	 * return if the library creator extension exists
	 */
	public static function isLibraryCreatorPluginExists(){
		$plugins = get_plugins();
		
		if(isset($plugins["addon_library_creator/addon_library_creator.php"]))
			return(true);
		
		return(false);		
	}
	
	
	/**
	 * put html start import
	 */
	public static function putHtmlStart(){
		
			$class = "";
			$classInstall = "";
			$addText = "";
			if(self::$status == "delete"){
				$class = "import-done";
				$classInstall = "import-now";
				$addText = " - DONE!";
			}
			
			//set creator plugin class
			$classCreator = "";
			$addTextCreator = "";
			$isCreatorExtensionExists = self::isLibraryCreatorPluginExists();
			if($isCreatorExtensionExists == true){
				$classCreator = "import-done";
				$addTextCreator = " - DONE!";
			}
			
			
		?>
		<div class="uc-importer-wrapper">
			
			<h1 class='uc-importer-header'>Import Addon Creator to Library</h1>
			
			<div class="uc-importer-text">
				The addon library can't work together with the addon creator plugin.
				<br/>
				The Addon Library is more advanced product and have all the addon creator functionality and many more. 
				<br/>
				What you need to do is: 
				<ul>
				
					<li id="uc_import_text" class="<?php echo $class?>">
						1. Import all the addon creator plugins to the addon library <?php echo $addText?>
					</li>
					
					<li class="<?php echo $classCreator?>">2. <span id="uc_install_text" class="<?php echo $classInstall?>"> Install the "addon library creator" plugin </span> that will give the library the "creator" functionality. <br> &nbsp;&nbsp;&nbsp; It's located in the current version of the addon creator plugin in the zip package <?php echo $addTextCreator?></li>
					
					<li>3. Uninstall and remove the addon creator plugin</li>
					
				</ul>
								
			</div>
			
		</div>
		
		<?php 
	}
	
	/**
	 * put import button html
	 */
	public static function putImportButton(){
		$urlImport = self::$urlComponent."&status=import";
		
		?>
			<br><br>

			<a href="<?php echo $urlImport?>" class="button-primary">Import Addons</a>
		
		<?php 
	}
	
	/**
	 * put import button html
	 */
	public static function putImportStatus(){
		?>
		<br><br>
		<div id="uc_status_importing">
			Importing... please wait...
		</div>
		
		<?php
	}
	
		
	
	/**
	 * put after successfully import html
	 */
	public static function putAfterImportHtml(){
		
		$urlDelete = self::$urlComponent."&status=delete_operation";
		
		?>
		
		<br><br>
		<div >
			Addons imported successfully
		</div>
		
		<script>
			jQuery(document).ready(function(){
				jQuery("#uc_status_importing").hide();		
				jQuery("#uc_import_text").addClass("import-done");
			});
		</script>
		
		<?php
	}
	
	
	/**
	 * get get variable
	 */
	public static function getGetVar($name, $initVar = "", $filter){
	
		$var = $initVar;
		if(isset($_GET[$name]))
			$var = $_GET[$name];
		
		if($filter)
			$var = UniteProviderFunctionsUC::sanitizeVar($var, $filter);
	
		return($var);
	}
	
	
	public static function a______________IMPORT_____________(){}
	
	
	/**
	 * get category by title
	 * if not found - return null
	 */
	public static function getCatByTitle($title, $type=""){
	
	
		$arrWhere = array();
		$arrWhere["title"] = $title;
		$arrWhere["type"] = $type;
	
		try{
			$arrCat = self::$db->fetchSingle(self::$tableLibraryCategories, $arrWhere);
	
			if(empty($arrCat))
				return(null);
	
			return($arrCat);
	
		}catch(Exception $e){
			return(null);
		}
	}
	
	
	/**
	 * get category id by title. If the title not exists - create it
	 */
	public static function getCreateCatByTitle($title, $type="vc"){
	
		//if found, return id
		$arrCat = self::getCatByTitle($title, $type);
		if(!empty($arrCat)){
			$catID = $arrCat["id"];
			return($catID);
		}
	
		try{
	
			$createData = self::addCategory($title, $type);
			$catID = $createData["id"];
	
			return($catID);
	
		}catch(Exception $e){
	
			return(0);
		}
	
	}
	
	
	/**
	 *
	 * add category
	 */
	public static function addCategory($title, $type="vc"){
				
		//prepare insert array
		$arrInsert = array();
		$arrInsert["title"] = $title;
		$arrInsert["type"] = $type;
		$arrInsert["ordering"] = 1;
	
		//insert the category
		$catID = self::$db->insert(self::$tableLibraryCategories, $arrInsert);
	
		//prepare output
		$returnData = array("id"=>$catID,"title"=>$title);
		return($returnData);
	}
	
	/**
	 * import creator addons
	 */
	private static function importCreatorAddons(){
		
		//create category
		try{
			
			$arrCreatorAddons = self::$db->fetch(self::$tableCreatorAddons);
			
			if(empty($arrCreatorAddons))
				return(true);
			
			$catID = self::getCreateCatByTitle("Addon Creator");
			$arrLibraryAddons = self::$db->fetch(self::$tableLibraryAddons,"catid=$catID");
			
			if(!empty($arrLibraryAddons))
				return(true);
			
			foreach($arrCreatorAddons as $addon){
				$name = $addon["name"];
				
				unset($addon["id"]);
				$addon["catid"] = $catID;
				$addon["addontype"] = "vc";
				$addon["alias"] = $name;
				$addon["name"] = $name."_vc";
				
				self::$db->insert(self::$tableLibraryAddons, $addon);
			}
		
		}catch(Exception $e){
			$message = $e->getMessage();
			echo "<span style='color:red'> Error: ".$message."</span>";
			return(false);
		}
		
		return(true);
	}
	
	
	
	
	/**
	 * import addons
	 */
	public static function importAddons(){

		$isImported = self::importCreatorAddons();
		
		if($isImported == true)
		update_option("uc_creator_addons_imported", true);
		
		self::putAfterImportHtml();
	}
	
	
	/**
	 * get status
	 */
	private static function getStatus(){
		
		$isImported = get_option("uc_creator_addons_imported");
		
		$status = self::getGetVar("status","","");
		if(empty($status)){
			$status = "start";
						
			if($isImported == true)
				$status = "delete";
						
		}
		
		if($status == "import" && $isImported == true)
			$status = "delete";
		
		
		return($status);
	}

	/**
	 *
	 * create the tables if not exists
	 */
	public static function createTables(){
	
		self::createTable(self::$tableLibraryAddons);
		self::createTable(self::$tableLibraryCategories);
	}
	
	
	/**
	 *
	 * admin main page function.
	 */
	public static function adminPages(){
		
		self::initAdminPagesVars();
			
		self::createTables();
		
		$status = self::getStatus();
		
		self::$status = $status;
				
		self::putHtmlStyle();
		
		switch($status){
			case "start":
			default:
				self::putHtmlStart();
				self::putImportButton();
			break;
			case "import":
				self::putHtmlStart();
				self::putImportStatus();
				self::importAddons();
			break;
			case "delete":
				self::putHtmlStart();
			break;
		}
			
	}
	
	
	/**
	 * test clear the importer
	 */
	private function test_clear(){
		
		delete_option("uc_creator_addons_imported");
		self::$db->runSql("drop table wp_addonlibrary_addons");
		self::$db->runSql("drop table wp_addonlibrary_categories");
	}
	
	/**
	 *
	 * craete tables
	 */
	public static function createTable($tableName){
	
		global $wpdb;
		
		if(UniteFunctionsWPUC::isDBTableExists($tableName))
			return(false);
	
		$charset_collate = $wpdb->get_charset_collate();
	
		switch($tableName){
			case self::$tableLibraryCategories:
				$sql = "CREATE TABLE " .$tableName ." (
				id int(9) NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				alias varchar(255),
				ordering int not NULL,
				params text NOT NULL,
				type tinytext,
				parent_id int(9),
				PRIMARY KEY (id)
				)$charset_collate;";
				break;
	
			case self::$tableLibraryAddons:
				$sql = "CREATE TABLE " .$tableName ." (
				id int(9) NOT NULL AUTO_INCREMENT,
				title varchar(255),
				name varchar(128),
				alias varchar(128),
				addontype varchar(128),
				description text,
				ordering int not NULL,
				templates text,
				config text,
				catid int,
				is_active tinyint,
				test_slot1 text,
				test_slot2 text,
				test_slot3 text,
				PRIMARY KEY (id)
				)$charset_collate;";
				break;
			default:
				UniteFunctionsUC::throwError("table: $tableName not found");
			break;
		}
	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	
	/**
	 * init function
	 */
	private function init(){
				
		$this->initVars();
		
		//$this->test_clear();
		
		$urlMenuIcon = self::$urlProvider."assets/images/icon_menu.png";
		
		self::addMenuPage('Addon Library', "adminPages", $urlMenuIcon);
		
		//add internal hook for adding a menu in arrMenus
		self::addAction(self::ACTION_ADMIN_MENU, "addAdminMenu");
		
	}
	
}

new AddonLibraryAcImporterUC();
