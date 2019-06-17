<?php
/**
 * @package Blox Page Builder
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ADDONS_INC') or die('Restricted access');


if($this->showHeaderTitle == true){
	$headerTitle = HelperUC::getText("my_layouts");
	require HelperUC::getPathTemplate("header");
}


$urlViewCreateObject = HelperUC::getViewUrl_Layout();

$urlLayouts = HelperUC::getViewUrl_LayoutsList();

$objTable->setPagingData($urlLayouts, $pagingData);

$urlManageAddons = HelperUC::getViewUrl_Addons();

$sizeActions = UniteProviderFunctionsUC::applyFilters(UniteCreatorFilters::FILTER_LAYOUTS_ACTIONS_COL_WIDTH, 380);

$numLayouts = count($arrLayouts);


?>

	<div class="unite-content-wrapper">
		
		<?php if($this->showButtonsPanel == true):?>
		
			<a href="<?php echo $urlViewCreateObject?>" class="unite-button-primary unite-float-left"><?php HelperUC::putText("new_layout");?></a>
			
			<a id="uc_button_import_layout" href="javascript:void(0)" class="unite-button-secondary unite-float-left mleft_20"><?php HelperUC::putText("import_layouts");?></a>
			
			<a href="javascript:void(0)" id="uc_layouts_global_settings" class="unite-float-right mright_20 unite-button-secondary"><?php HelperUC::putText("layouts_global_settings");?></a>
			<a href="<?php echo $urlManageAddons?>" class="unite-float-right mright_20 unite-button-secondary"><?php _e("My Addons", ADDONLIBRARY_TEXTDOMAIN)?></a>
		
			<div class="vert_sap20"></div>
			
		<?php endif?>
		
		<?php
		
		$objTable->putActionsFormStart();
		$putSearchForm = ($numLayouts > 5);
		
		//$objTable->putFilterCategoryInput();
		$objTable->putSearchForm(HelperUC::getText("search_layout"),"Clear",$putSearchForm);
		
		?>
		
		<?php if(empty($arrLayouts)): ?>
		<div>
			<?php HelperUC::putText("no_layouts_found");?>
		</div>			
		<?php else:?>
	
			<!-- sort chars: &#8743 , &#8744; -->
			
			<table id="uc_table_layouts" class='unite_table_items' data-text-delete="<?php HelperUC::putText("are_you_sure_to_delete_this_layout")?>">
				<thead>
					<tr>
						<th width=''>
							<?php $objTable->putTableOrderHeader("title", HelperUC::getText("layout_title")) ?>
						</th>
						<th width='200'><?php _e("Shortcode",ADDONLIBRARY_TEXTDOMAIN); ?></th>
						<th width='200'><?php $objTable->putTableOrderHeader("catid", __("Category",ADDONLIBRARY_TEXTDOMAIN)) ?>
						<th width='<?php echo $sizeActions?>'><?php _e("Actions",ADDONLIBRARY_TEXTDOMAIN); ?></th>
						<th width='60'><?php _e("Preview",ADDONLIBRARY_TEXTDOMAIN); ?></th>						
					</tr>
				</thead>
				<tbody>

					<?php foreach($arrLayouts as $key=>$layout):
						
						$id = $layout->getID();
																
						$title = $layout->getTitle();

						$shortcode = $layout->getShortcode();
						$shortcode = UniteFunctionsUC::sanitizeAttr($shortcode);
												
						$editLink = HelperUC::getViewUrl_Layout($id);
												
						$previewLink = HelperUC::getViewUrl_LayoutPreview($id, true);
						
						$showTitle = HelperHtmlUC::getHtmlLink($editLink, $title);
						
						$rowClass = ($key%2==0)?"unite-row1":"unite-row2";
						
						$arrCategory = $layout->getCategory();
						
						$catID = UniteFunctionsUC::getVal($arrCategory, "id");
						$catTitle = UniteFunctionsUC::getVal($arrCategory, "name");
						
					?>
						<tr class="<?php echo $rowClass?>">
							<td><?php echo $showTitle?></td>
							<td>
								<input type="text" readonly onfocus="this.select()" class="unite-input-medium unite-cursor-text" value="<?php echo $shortcode?>" />
							</td>
							
							<td><a href="javascript:void(0)" class="uc-layouts-list-category" data-layoutid="<?php echo $id?>" data-catid="<?php echo $catID?>" data-action="manage_category"><?php echo $catTitle?></a></td>
							 
							<td>
								<a href='<?php echo $editLink?>' class="unite-button-primary float_left mleft_15"><?php HelperUC::putText("edit_layout"); ?></a>
								
								<a href='javascript:void(0)' data-layoutid="<?php echo $id?>" data-id="<?php echo $id?>" class="button_delete unite-button-secondary float_left mleft_15"><?php _e("Delete",ADDONLIBRARY_TEXTDOMAIN); ?></a>
								<span class="loader_text uc-loader-delete" style="display:none"><?php _e("Deleting", ADDONLIBRARY_TEXTDOMAIN)?></span>
								<a href='javascript:void(0)' data-layoutid="<?php echo $id?>" data-id="<?php echo $id?>" class="button_duplicate unite-button-secondary float_left mleft_15"><?php _e("Duplicate",ADDONLIBRARY_TEXTDOMAIN); ?></a>
								<span class="loader_text uc-loader-duplicate" style="display:none"><?php _e("Duplicating", ADDONLIBRARY_TEXTDOMAIN)?></span>
								<a href='javascript:void(0)' data-layoutid="<?php echo $id?>" data-id="<?php echo $id?>" class="button_export unite-button-secondary float_left mleft_15"><?php _e("Export",ADDONLIBRARY_TEXTDOMAIN); ?></a>
								<?php UniteProviderFunctionsUC::doAction(UniteCreatorFilters::ACTION_LAYOUTS_LIST_ACTIONS, $id); ?>
							</td>
							<td>
								<a href='<?php echo $previewLink?>' target="_blank" class="unite-button-secondary float_left"><?php _e("Preview",ADDONLIBRARY_TEXTDOMAIN); ?></a>					
							</td>
						</tr>							
					<?php endforeach;?>
					
				</tbody>		 
			</table>
			
			<?php 
			
				$objTable->putPaginationHtml();				
				$objTable->putInpageSelect();
				
			?>
			
		<?php endif?>
		
		<?php
		 
			$objTable->putActionsFormEnd();
			
			$pageBuilder->putLayoutsGlobalSettingsDialog();
			$this->putDialogImportLayout();
			
			$this->putDialogCategories();
			
			//put pages catalog if exists
			$this->putDialogPageCatalog();
		?>
		
		
	</div>
	
<script type="text/javascript">

	jQuery(document).ready(function(){

		var objAdmin = new UniteCreatorAdmin_LayoutsList();
		objAdmin.initObjectsListView();
		
	});

</script>

