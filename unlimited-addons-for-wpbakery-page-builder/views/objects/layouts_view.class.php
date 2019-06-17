<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


class UniteCreatorLayoutsView{
	
	protected $showButtonsPanel = true;
	protected $showHeaderTitle = true;
	protected $browserAddonType;
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->browserAddonType = GlobalsUC::$layoutsAddonType;
		
	}
	
	
	/**
	 * put page catalog browser
	 */
	public function putDialogPageCatalog(){
		
		$webAPI = new UniteCreatorWebAPI();
		$isPageCatalogExists = $webAPI->isPagesCatalogExists();
		if($isPageCatalogExists == false)
			return(false);
		
		$objBrowser = new UniteCreatorBrowser();
		$objBrowser->setPagesMode();
		$objBrowser->initAddonType($this->browserAddonType);
		$objBrowser->putBrowser();
	}
	
	
	/**
	 * put manage categories dialog
	 */
	public function putDialogCategories(){
		
		$prefix = "uc_dialog_add_category";
		
		?>
			<div id="uc_dialog_add_category"  title="<?php HelperUC::putText("manage_layout_categories")?>" style="display:none; height: 300px;" class="unite-inputs">
				
				<div class="unite-dialog-top">
				
					<input type="text" class="uc-catdialog-button-clearfilter" style="margin-bottom: 1px;">
					<a class='uc-catdialog-button-filter unite-button-secondary' href="javascript:void(0)"><?php _e("Filter", ADDONLIBRARY_TEXTDOMAIN)?></a>
					<a class='uc-catdialog-button-filter-clear unite-button-secondary' href="javascript:void(0)"><?php ?><?php _e("Clear Filter", ADDONLIBRARY_TEXTDOMAIN)?></a>
						
					<h3>
						<?php _e("List of categories (sort: ",ADDONLIBRARY_TEXTDOMAIN)?>
						<a href="javascript:void(0)" class="uc-link-change-cat-sort" data-type="a-z">a-z</a>
						, 
						<a href="javascript:void(0)" class="uc-link-change-cat-sort" data-type="z-a">z-a</a>
						):
					</h3>
				</div>
				
				<div id="list_layouts_cats"></div>
				
				<hr/>
				
					<?php _e("Add New Category", ADDONLIBRARY_TEXTDOMAIN)?>: 
					<input id="uc_dialog_add_category_catname" type="text" class="unite-input-regular" value="">
					
					<a id="uc_dialog_add_category_button_add" href="javascript:void(0)" class="unite-button-secondary" data-action="add_category"><?php _e("Create Category", ADDONLIBRARY_TEXTDOMAIN)?></a>
					
				<div>
				
					<?php 
					$buttonTitle = __("Set Category to Page", ADDONLIBRARY_TEXTDOMAIN);
					$loaderTitle = __("Updating Category...", ADDONLIBRARY_TEXTDOMAIN);
					$successTitle = __("Category Updated", ADDONLIBRARY_TEXTDOMAIN);
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
				
				
			</div>
			
			<div id="uc_layout_categories_message" title="<?php _e("Categories Message", ADDONLIBRARY_TEXTDOMAIN)?>">
			</div>
			
		</div>
		
		<?php 
	}
	
	
	/**
	 * put import addons dialog
	 */
	public function putDialogImportLayout(){
	
		$dialogTitle = HelperUC::getText("import_layout");
		
		?>
		
			<div id="uc_dialog_import_layouts" class="unite-inputs" title="<?php echo $dialogTitle?>" style="display:none;">
				
				<div class="unite-dialog-top"></div>
				
				<div class="unite-inputs-label">
					<?php HelperUC::putText("select_layouts_export_file")?>:
				</div>
				
				<form id="dialog_import_layouts_form" name="form_import_layouts">
					<input id="dialog_import_layouts_file" type="file" name="import_layout">
							
				</form>	
				
				<div class="unite-inputs-sap-double"></div>
				
				<div class="unite-inputs-label" >
					<label for="dialog_import_layouts_file_overwrite">
						<?php _e("Overwrite Addons", ADDONLIBRARY_TEXTDOMAIN)?>:
					</label>
					<input type="checkbox" id="dialog_import_layouts_file_overwrite">
				</div>
				
				
				<div class="unite-clear"></div>
				
				<?php 
					$prefix = "uc_dialog_import_layouts";
					$buttonTitle = HelperUC::getText("import_layouts");
					$loaderTitle = HelperUC::getText("uploading_layouts_file");
					$successTitle = HelperUC::getText("layouts_added_successfully");
					HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>
					
			</div>		
		
	<?php
	}
		
	
	/**
	* display layouts view
	 */
	public function display(){
		
		//table object
		$objTable = new UniteTableUC();
		$objTable->setDefaultOrderby("title");
		
		$objLayouts = new UniteCreatorLayouts();
		$pageBuilder = new UniteCreatorPageBuilder();
		
		$pagingOptions = $objTable->getPagingOptions();
		
		$response = $objLayouts->getArrLayoutsPaging($pagingOptions);
		
		$arrLayouts = $response["layouts"];
		$pagingData = $response["paging"];
		
		
		require HelperUC::getPathTemplate("layouts_list");		
	}
	
}

