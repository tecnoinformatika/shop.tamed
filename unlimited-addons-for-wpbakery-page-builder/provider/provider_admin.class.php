<?php

defined('UNLIMITED_ADDONS_INC') or die('Restricted access');
	
   class UniteProviderAdminUC extends UniteCreatorAdmin{
   	
	   	private static $arrMenuPages = array();
	   	private static $arrSubMenuPages = array();
	   	private static $capability = "manage_options";
	   	
   		private $mainFilepath;
   		private $pluginFilebase;
   		private $dirPlugin;
   		
	   	private static $t;
	   	
	   	const ACTION_ADMIN_MENU = "admin_menu";
	   	const ACTION_ADMIN_INIT = "admin_init";
	   	const ACTION_ADD_SCRIPTS = "admin_enqueue_scripts";
   		const ACTION_AFTER_SETUP_THEME = "after_setup_theme";
   		const ACTION_PRINT_SCRIPT = "admin_print_footer_scripts";
   		const ACTION_AFTER_SWITCH_THEME = "after_switch_theme";
   		
   		//install addons from this folder in the addon library itself on activate
   		const DIR_INSTALL_ADDONS = "addons_install";	
   		   		   		
   		protected $coreAddonType;
   		protected $coreAddonsView;
   		
   		protected $textBuy; 
   		protected $linkBuy;
   		protected $pluginTitle;
   		
   		
		/**
		 *
		 * the constructor
		 */
		public function __construct($mainFilepath){
			self::$t = $this;
			
			$this->mainFilepath = $mainFilepath;
			
			$mainFilename = basename($mainFilepath);
			
			$pathPlugin = str_replace('\\', "/", GlobalsUC::$pathPlugin);
			
			$dirPlugins = dirname($pathPlugin)."/";
			
			$dirPlugin = str_replace($dirPlugins, "", $pathPlugin);
			$this->dirPlugin = $dirPlugin;
			$this->pluginFilebase = $dirPlugin.$mainFilename;
			
			UniteFunctionsUC::validateNotEmpty($this->coreAddonType, "core addon type");
			UniteFunctionsUC::validateNotEmpty($this->coreAddonsView, "core addons view");
			UniteFunctionsUC::validateNotEmpty($this->pluginTitle, "plugin title");
			
			
			//update globals
			GlobalsUC::$view_default = $this->coreAddonsView;
			GlobalsUC::$defaultAddonType = $this->coreAddonType;
						
			parent::__construct();
			
			$this->init();
		}		

			
		/**
		 * process activate event - install the db (with delta).
		 */
		public function onActivate(){
			
			$this->createTables();
			
			$this->importCurrentThemeAddons();
			
			//import addons that comes in the addon library package
			$this->importPackageAddons();
			
		}
		
		
		/**
		 * validate view if called single way
		 */
		public static function validateSingleView($view){
			
			switch($view){
				case GlobalsUC::VIEW_ADDONS_LIST:
					
					UniteFunctionsUC::throwError("Permission Denied to enter this view");
				break;
			}
			
		}
		
		
		/**
		 * after switch theme
		 */
		public function afterSwitchTheme(){
			
			$this->importCurrentThemeAddons();
		}
		
		
		/**
		 * do all actions on theme setup
		 */
		public function onThemeSetup(){
						
		}
		
		
		/**
		 * create the tables if not exists
		 */
		public function createTables(){
			
			$this->createTable(GlobalsUC::TABLE_ADDONS_NAME);
			$this->createTable(GlobalsUC::TABLE_CATEGORIES_NAME);
		}
		
		
		/**
		 *
		 * craete tables
		 */
		public function createTable($tableName){
		
			global $wpdb;
						
			//if table exists - don't create it.
			$tableRealName = $wpdb->prefix.$tableName;
			if(UniteFunctionsWPUC::isDBTableExists($tableRealName))
				return(false);
			
			$charset_collate = $wpdb->get_charset_collate();
			
			switch($tableName){
				case GlobalsUC::TABLE_LAYOUTS_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
					id int(9) NOT NULL AUTO_INCREMENT,
					title varchar(255) NOT NULL,
					layout_data mediumtext,					
					ordering int not NULL,
					catid int not NULL,
					layout_type varchar(60),
					relate_id int not NULL,
					parent_id int not NULL,
					params text NOT NULL,
					PRIMARY KEY (id)
					)$charset_collate;";
				break;
				case GlobalsUC::TABLE_CATEGORIES_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
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
				
				case GlobalsUC::TABLE_ADDONS_NAME:
					$sql = "CREATE TABLE " .$tableRealName ." (
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
					UniteFunctionsMeg::throwError("table: $tableName not found");
				break;
			}
		
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
		
		/**
		 *
		 * add ajax back end callback, on some action to some function.
		 */
		protected function addActionAjax($ajaxAction, $eventFunction){
			$this->addAction('wp_ajax_'.GlobalsUC::PLUGIN_NAME."_".$ajaxAction, $eventFunction, true);
			$this->addAction('wp_ajax_nopriv_'.GlobalsUC::PLUGIN_NAME."_".$ajaxAction, $eventFunction, true);
		}
		
		
		/**
		 *
		 * register the "onActivate" event
		 */
		protected function addEvent_onActivate($eventFunc = "onActivate"){
			
			register_activation_hook( $this->mainFilepath, array($this, $eventFunc) );
		}
		
		
		/**
		 *
		 * add menu page
		 */
		protected function addMenuPage($title,$pageFunctionName,$icon=null){
			self::$arrMenuPages[] = array("title"=>$title,"pageFunction"=>$pageFunctionName,"icon"=>$icon);
		}
		
		/**
		 *
		 * add sub menu page
		 */
		protected function addSubMenuPage($slug,$title,$pageFunctionName){
			self::$arrSubMenuPages[] = array("slug"=>$slug,"title"=>$title,"pageFunction"=>$pageFunctionName);
		}
		
		/**
		 * add admin menus from the list.
		 */
		public function addAdminMenu(){
					
			//return(false);
			foreach(self::$arrMenuPages as $menu){
				$title = $menu["title"];
				$pageFunctionName = $menu["pageFunction"];
				$icon = UniteFunctionsUC::getVal($menu, "icon");
				
				add_menu_page( $title, $title, self::$capability, GlobalsUC::PLUGIN_NAME, array(self::$t, $pageFunctionName), $icon );
			}
		
			foreach(self::$arrSubMenuPages as $key=>$submenu){
		
				$title = $submenu["title"];
				$pageFunctionName = $submenu["pageFunction"];
		
				$slug = GlobalsUC::PLUGIN_NAME."_".$submenu["slug"];
		
				if($key == 0)
					$slug = GlobalsUC::PLUGIN_NAME;
		
				add_submenu_page(GlobalsUC::PLUGIN_NAME, $title, $title, 'manage_options', $slug, array(self::$t, $pageFunctionName) );
			}
		
		}
		
		
		/**
		 *
		 * tells if the the current plugin opened is this plugin or not
		 * in the admin side.
		 */
		private function isInsidePlugin(){
			$page = UniteFunctionsUC::getGetVar("page","",UniteFunctionsUC::SANITIZE_KEY);
			
			if($page == GlobalsUC::PLUGIN_NAME || strpos($page, GlobalsUC::PLUGIN_NAME."_") !== false)
				return(true);
		
			return(false);
		}
		
				
		/**
		 *
		 * add some wordpress action
		 */
		protected function addAction($action, $eventFunction, $isStatic = false){
			
			if($isStatic == false){
				add_action( $action, array($this, $eventFunction) );
			}else{
				
				add_action( $action, array(self::$t, $eventFunction) );
			}
											
		}
		
		
		/**
		 * add local filter
		 */
		protected function addLocalFilter($tag, $func){
			
			add_filter($tag, array($this, $func) );
		}
		
		
		/**
		 *
		 * validate admin permissions, if no pemissions - exit
		 */
		protected function validateAdminPermissions(){
			
			if(UniteFunctionsWPUC::isAdminPermissions() == false){
				echo "access denied, no admin permissions";
				return(false);
			}
			
		}
		
		
		/**
		 * get addons view by type
		 */
		public static function getUrlViewAddonsByType($type){
			
			switch($type){
				case GlobalsProviderUC::ADDONSTYPE_VC:
					return HelperUC::getViewUrl(GlobalsProviderUC::VIEW_ADDONS_VC);
				break;
				case GlobalsProviderUC::ADDONSTYPE_ELEMENTOR:
					return HelperUC::getViewUrl(GlobalsProviderUC::VIEW_ADDONS_ELEMENTOR);
				break;
				default:
					UniteFunctionsUC::throwError("Wrong addons type: $type");
				break;
			}
			
		}
		
		
		/**
		 *
		 * admin main page function.
		 */
		public function adminPages(){
						
			if(is_multisite())
				$this->createTables();
			
			parent::adminPages();
			
		}
		
		/**
		 * add scripts to all admin pages
		 */
		public function addScriptsToAllAdminPages(){
			
			HelperUC::addStyleAbsoluteUrl( GlobalsUC::$url_provider."assets/provider_admin.css", "uc_provider_admin");
		}
		
		
		/**
		 * add outside plugin scripts
		 */
		public function onAddOutsideScripts(){
			
			try{
			
			//add outside scripts, only on posts or pages page
			$isPostsPage = UniteFunctionsWPUC::isAdminPostsPage();
						
			if($isPostsPage == false){
				$this->addScriptsToAllAdminPages();
			}
			
			
			}catch(Exception $e){
				
				HelperHtmlUC::outputException($e);
				
			}
		}

		
		/**
		 * print custom scripts
		 */
		public function onPrintFooterScripts(){

			HelperProviderUC::onPrintFooterScripts();
			
		}
		
		private static function a_________IMPORT_ADDONS________(){}
		
		
		/**
		 * install addosn from some path
		 */
		protected function installAddonsFromPath($pathAddons, $addonsType = null){
						
			if(empty($addonsType))
				$addonsType = $this->coreAddonType;
			
			if(is_dir($pathAddons) == false)
				return(false);
			
			$exporter = new UniteCreatorExporter();
			$exporter->setMustImportAddonType($addonsType);
			$exporter->importAddonsFromFolder($pathAddons);
						
		}
		
		
		/**
		 * import current theme addons
		 */
		private function importCurrentThemeAddons(){
			
			$pathCurrentTheme = get_template_directory()."/";
			
			$dirAddons = apply_filters("uc_path_theme_addons", GlobalsUC::DIR_THEME_ADDONS);
			
			$pathAddons = $pathCurrentTheme.$dirAddons."/";
			
			$this->installAddonsFromPath($pathAddons);
		}
		
		
		/**
		 * import package addons
		 */
		protected function importPackageAddons(){
			
			$pathAddons = GlobalsUC::$pathPlugin.self::DIR_INSTALL_ADDONS."/";
			
			if(is_dir($pathAddons) == false)
				return(false);
			
			$imported = false;
			
			//install vc addons
			$pathAddonsVC = $pathAddons.$this->coreAddonType."/";
			if(is_dir($pathAddonsVC)){
				$this->installAddonsFromPath($pathAddonsVC, $this->coreAddonType);
				$imported = true;
			}
			
			
			return($imported);
		}
		
		private static function a_________OTHERS________(){}
		
		
		/**
		 * return if creator plugin exists
		 */
		protected function isCreatorPluginExists(){
			$arrPlugins = get_plugins();
			
			$pluginName = "addon_library_creator/addon_library_creator.php";
			if(isset($arrPlugins[$pluginName]) == false)
				return(false);
			
			$isActive = is_plugin_active($pluginName);
			
			return($isActive);
						
		}
		
		
		/**
		 * modify addons manager
		 */
		public function modifyAddonsManager($objManager){
			
			$addonsView = HelperUC::getGeneralSetting("manager_addons_view");
			
			if($addonsView == "info")
				$objManager->setViewType(UniteCreatorManagerAddons::VIEW_TYPE_INFO);
			
		}
		
				
		
		/**
		 * after update plugin
		 * install package addons, then redirect to dashboard
		 */
		private function onAfterUpdatePlugin(){
			
			$isImported = $this->importPackageAddons();
			if($isImported == false)
				return(false);
			
			//redirect to main view
			$urlRedirect = HelperUC::getViewUrl_Default();
			
			dmp("addons installed, redirecting...");
			echo "<script>location.href='$urlRedirect'</script>";
			exit();
			
		}
		
		
		/**
		 * run provider action if exists - only if inside plugin
		 */
		private function runProviderAction(){
			
			$action = UniteFunctionsUC::getGetVar("provider_action", "", UniteFunctionsUC::SANITIZE_KEY);
			if(empty($action))
				return(false);
			
			switch($action){
				case "run_after_update":
					$this->onAfterUpdatePlugin();
				break;
			}
			
		}
		
		
		/**
		 * 
		 * plugin action links
		*/
		public function plugin_action_links( $links ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=' . Settings::PAGE_ID ), __( 'Settings', 'elementor' ) );
			
			array_unshift( $links, $settings_link );
	
			$links['go_pro'] = sprintf( '<a href="%s" target="_blank" class="elementor-plugins-gopro">%s</a>', Utils::get_pro_link( 'https://elementor.com/pro/?utm_source=wp-plugins&utm_campaign=gopro&utm_medium=wp-dash' ), __( 'Go Pro', 'elementor' ) );
			
			return $links;
		}
		
		
		/**
		* goto pro link
		*/
		public function checkRedirectLink() {
			
			$page = UniteFunctionsUC::getGetVar("page", "", UniteFunctionsUC::SANITIZE_KEY);
			
			if($page !== "unitecreator_gounlimited")
				return(false);
			
			$location = $this->linkBuy;
			
			header("Location: $location", true, 302);
			
			exit();
		}
		
		
		/**
		 * modify plugins view links
		 */
		public function modifyPluginViewLinks($arrLinks){
			
			if(GlobalsUC::$isProductActive == true)
				return($arrLinks);
			
			if(empty($this->linkBuy))
				return($arrLinks);
						
			$linkbuy = HelperHtmlUC::getHtmlLink($this->linkBuy, $this->textBuy,"","uc-link-gounlimited", true);
			
			$arrLinks["gounlimited"] = $linkbuy;
			
			return($arrLinks);
		}
		
		/**
		 * create tables if not created on multisite
		 */
		protected function checkMultisiteCreateTables(){
						
			global $wpdb;
			$tablePrefix = $wpdb->prefix;
						
			$option = "addon_library_tables_created_{$tablePrefix}";
			
			$isCreated = get_option($option);
			if($isCreated == true)
				return(true);
			
			$this->createTables();
			
			update_option($option, true);
		}
		
		
		/**
		 * check and add admin notices if needed
		 */
		protected function checkAdminNotices(){
			
			$enableMemoryTest = HelperUC::getGeneralSetting("enable_memory_usage_test");
			$enableMemoryTest = UniteFunctionsUC::strToBool($enableMemoryTest);
			
			if($enableMemoryTest == true)
				HelperUC::addAdminNotice("Memory test is enabled, please turn it off after the testing. It's slowing down the system.");
			
		}
		
				
		/**
		 * 
		 * init function
		 */
		protected function init(){
			
			$this->checkRedirectLink();
			
			$isCreatorExists = self::isCreatorPluginExists();
			
			parent::init();
			
			HelperProviderUC::globalInit();
			
			if(is_multisite() == true)
				$this->checkMultisiteCreateTables();
			
			//HelperUC::printGeneralSettings();
			
			$permission = HelperUC::getGeneralSetting("edit_permission");
			if($permission == "editor")
				self::$capability = "edit_posts";
			
			
			$urlMenuIcon = GlobalsUC::$url_provider."assets/images/icon_menu.png";
			
			$this->addMenuPage($this->pluginTitle, "adminPages", $urlMenuIcon);
			
			$arrSubmenuPages = array();
			
			$this->addSubMenuPage($this->coreAddonsView, __('My Addons',UNLIMITED_ADDONS_TEXTDOMAIN), "adminPages");
			
			if($isCreatorExists == true)
				$this->addSubMenuPage("assets", __('Assets Manager',UNLIMITED_ADDONS_TEXTDOMAIN), "adminPages");
			
			$this->addSubMenuPage("settings", __('General Settings',UNLIMITED_ADDONS_TEXTDOMAIN), "adminPages");
			
			if(GlobalsUC::$isProductActive == false && !empty($this->linkBuy))
				$this->addSubMenuPage("gounlimited", $this->textBuy, "adminPages");
			
			//add internal hook for adding a menu in arrMenus
			$this->addAction(self::ACTION_ADMIN_MENU, "addAdminMenu");
			
			//if not inside plugin don't continue
			if($this->isInsidePlugin() == true){
				$this->addAction(self::ACTION_ADD_SCRIPTS, "onAddScripts", true);
			}else{	
				$this->addAction(self::ACTION_ADD_SCRIPTS, "onAddOutsideScripts");
			}
			
			$this->addAction(self::ACTION_PRINT_SCRIPT, "onPrintFooterScripts");
			
			//self::addAction(self::ACTION_AFTER_SETUP_THEME, "onThemeSetup");
						
			$this->addAction(self::ACTION_AFTER_SWITCH_THEME, "afterSwitchTheme");
			
			$this->addEvent_onActivate();
			
			$this->addActionAjax("ajax_action", "onAjaxAction");
			
			//addon library actions
			$this->addAction(UniteCreatorFilters::ACTION_MODIFY_ADDONS_MANAGER, "modifyAddonsManager");
			
			$this->addLocalFilter("plugin_action_links_".$this->pluginFilebase, "modifyPluginViewLinks");
			
			//run provider action if exists (like after update)
			if($this->isInsidePlugin())
				$this->runProviderAction();
			
			$this->checkAdminNotices();
			
			
			//$this->addAction("init", "onLoadedTest");
	}

		
		
	}

?>