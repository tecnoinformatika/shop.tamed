function UniteCreatorAdmin_Layout(){
	
	var t = this;
	var g_providerAdmin = new UniteProviderAdminUC();
	var g_gridBuilder, g_layoutID, g_objWrapper;
	
	var g_temp = {
			is_save_enabled: true
	}
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	
	function _______GENERAL_________(){}
	
	
	/**
	 * hide new page stuff and show existing page stuff
	 */
	function switchInterfaceToExisting(layoutID){
		
		g_layoutID = layoutID;
		
		g_objWrapper.data("layoutid", g_layoutID);
		
		jQuery(".uc-layout-newpage").hide();
		
		jQuery(".uc-layout-existingpage").show();
		
		updateShortcode();
		
		//update preview button
		var buttonPreview = jQuery("#uc-button-preview-layout");
		var url = buttonPreview.attr("href");
		
		url = url.replace("id=0","id="+layoutID);
		buttonPreview.attr("href", url);
		
		//update button mode
		var buttonMode = jQuery("#uc_button_edit_mode");
		
		var urlBox = buttonMode.data("urlbox");
		urlBox += "&id="+layoutID;
		buttonMode.data("urlbox", urlBox);
		
		var urlLive = buttonMode.data("urllive");
		urlLive += "&id="+layoutID;
		buttonMode.data("urllive", urlLive);
		
		location.hash = "layoutid="+layoutID;
	}
	
	
	/**
	 * on update layout button click
	 */
	function onUpdateClick(isTitleOnly, funcOnSuccess){
		
		var title = jQuery("#uc_layout_title").val();
		
		var data = {
				layoutid: g_layoutID,
				title: title
		};
		
		if(isTitleOnly !== true){
			var dataGrid = g_gridBuilder.getGridData();
			var jsonData = JSON.stringify(dataGrid);
			var strEncodedData = g_ucAdmin.encodeContent(jsonData);
			
			data["grid_data"] = strEncodedData;
			
			g_ucAdmin.setAjaxLoaderID(function(eventName){
				
				if(eventName == "show_loader"){
					
					jQuery("#uc_layout_status_loader").show();
					jQuery("#uc_layout_save_button_icon").hide();
					jQuery("#uc_layout_save_button_loader").show();					
				}else{
					jQuery("#uc_layout_status_loader").hide();					
					jQuery("#uc_layout_save_button_loader").hide();					
					jQuery("#uc_layout_save_button_icon").show();
					
				}
				
			});
			
			
		}else{
			
			g_ucAdmin.setAjaxLoaderID("uc_button_rename_page_loader");
			g_ucAdmin.setAjaxHideButtonID("uc_button_rename_page");
			
			data["title_only"] = true;
		}
		
		
		//var data
		g_ucAdmin.setSuccessMessageID("uc_layout_status_success");
		
		
		g_ucAdmin.setErrorMessageOnHide(function(){
			jQuery("#uc_layout_status_error").hide();
		});
			
		
		g_ucAdmin.setErrorMessageID(t.showErrorMessage);
		
		g_ucAdmin.ajaxRequest("update_layout", data, function(response){
				
				jQuery("#uc_page_title").html(title);
				
				//handle new page
				if(!g_layoutID){
					
					switchInterfaceToExisting(response.layout_id);
				}
					
					
				if(funcOnSuccess)
					funcOnSuccess();
		});
	}
	
	
	
    /*
     * on save title button click
     */
    function onSaveTitleClick(){
       
    	onUpdateClick(true);
	}
	
    
    /**
     * switch between views, save first
     */
	function onEditModeButtonClick(){
		var objLink = jQuery(this);
		
		var mode = objLink.data("mode");
		
		if(mode == "box"){
			var urlLinkRedirect = objLink.data("urllive");
		}
		else{
			var urlLinkRedirect = objLink.data("urlbox");
		}
		
		if(g_temp.is_save_enabled == false){
			location.href = urlLinkRedirect;
			return(false);
		}
		
		var message = objLink.data("message");
		
		onUpdateClick(false, function(){
			jQuery("#uc_layout_status_loader").show();
			jQuery("#uc_layout_status_loader span").html(message);
			
			location.href = urlLinkRedirect;
		});
		
		
	}
	
	/**
	 * show loader message
	 */
	function showLoaderMessage(message){
		
		jQuery("#uc_layout_status_loader").show();
		
		if(message)
			jQuery("#uc_layout_status_loader span").html(message);
			
	}
	
	
	/**
	 * show error message
	 */
	function showErrorMessage(message){
		jQuery("#uc_layout_status_error").show();
		jQuery("#uc_layout_status_error span").html(message);
	}	
	
	
	/**
	 * show error message
	 */
	this.showErrorMessage = function(message){		//for outside
		showErrorMessage(message);
	}

	
	/**
	 * hide save button
	 */
	function hideSaveButton(){
		jQuery("#uc_button_update_layout").hide();
		g_temp.is_save_enabled = false;
		
	}
	
	
	/**
	 * disable save button
	 */
	this.hideSaveButton = function(){		//for outside
		hideSaveButton();
	}
	
	
	
	function _______SHORTCODE_________(){}
	
	/**
	 * set shortcode
	 */
	function updateShortcode(){
		
		var objShortcode = jQuery("#uc_layout_shortcode");
		
		
		
		var titleText = jQuery("#uc_layout_title").val();
		titleText = g_ucAdmin.stripslashes(titleText);
		
		titleText = g_ucAdmin.escapeDoubleQuote(titleText);
		
		var wrappersType = objShortcode.data("wrappers");
		var shortcodeName = objShortcode.data("shortcode");
		
		var wrapperLeft = "{", wrapperRight = "}";
		
		if(wrappersType == "wp"){
			wrapperLeft = "[";
			wrapperRight = "]";
		}
		
		var shortcode = wrapperLeft+shortcodeName+" id="+g_layoutID+" title=\""+titleText+"\" " + wrapperRight;
		
		objShortcode.val(shortcode);
	}
	
	/**
	 * on copy click
	 */
	function onCopyShortcodeClick(){
				
		jQuery("#uc_layout_shortcode").focus().select();
		
		document.execCommand("copy");
		
	}
	
	
	/**
	 * init shortcode
	 */
	function initShortcode(){
		
		var objShortcode = jQuery("#uc_layout_shortcode");
		if(objShortcode.length == 0)
			return(false);
				
		updateShortcode();
		
		jQuery("#uc_layout_title").change(updateShortcode);
		
		jQuery("#uc_link_copy_shortcode").click(onCopyShortcodeClick);
	}

	function _______IMPORT_EXPORT_________(){}
	
	
	/**
	 * open import layout dialog
	 */
	function openImportLayoutDialog(){
		
		jQuery("#dialog_import_layouts_file").val("");
		
		var options = {minWidth:700};
		
		g_ucAdmin.openCommonDialog("#uc_dialog_import_layouts", null, options);
		
	}
	
	
	/**
	 * init import layout dialog
	 */
	function initImportLayoutDialog(){
		
		
		var objButtonImport = jQuery("#uc_button_import_layout");
		
		if(objButtonImport.length == 0)
			return(false);
				
		
		objButtonImport.click(openImportLayoutDialog);
		
		jQuery("#uc_dialog_import_layouts_action").click(function(){
			
			var isOverwrite = jQuery("#dialog_import_layouts_file_overwrite").is(":checked");
	        var data = {overwrite_addons:isOverwrite};
	        
	        data.layoutID = g_layoutID;
			
	        if(!g_layoutID)
	        	throw new Error("layout id not found");
	        
	        
	        var objData = new FormData();
	        var jsonData = JSON.stringify(data);
	    	objData.append("data", jsonData);
	    	
	    	g_ucAdmin.addFormFilesToData("dialog_import_layouts_form", objData);
	    	
			g_ucAdmin.dialogAjaxRequest("uc_dialog_import_layouts", "import_layouts", objData);
			
		});
		
	}
	
	/**
	 * export layout
	 */
	function exportLayoutClick(){
		
		var params = "id="+g_layoutID;
		var urlExport = g_ucAdmin.getUrlAjax("export_layout", params);
		location.href=urlExport;
	}
		
	
	function _______INIT_________(){}
    
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		jQuery("#uc_button_update_layout").click(onUpdateClick);
		jQuery("#uc_button_rename_page").click(onSaveTitleClick);
		
		var objButtonExport = jQuery("#uc_button_export_layout");
		if(objButtonExport.length)
			objButtonExport.click(exportLayoutClick);
		
		jQuery("#uc_button_edit_mode").click(onEditModeButtonClick);
		
		jQuery(".uc-save-status-close").click(function(){
			var objMessage = jQuery(this).parents(".uc-save-status");
			objMessage.hide();
		});
		
	}
	
	
	/**
	 * check and redirect if needed
	 */
	function checkRedirectLayout(){
		
		if(g_layoutID)
			return(false);
		
		var hash = location.hash;
		if(!hash)
			return(false);
		
		var layoutID = hash.replace("#layoutid=","");
		
		layoutID = jQuery.trim(layoutID);
		if(jQuery.isNumeric(layoutID) == false)
			return(false);
			
		var viewLayout = g_ucAdmin.getUrlView("layout","id="+layoutID);
		
		showLoaderMessage("Loading Page...");
		
		location.href = viewLayout;
		
		return(true);
	}
	
	
	/**
	 * objects list view
	 */
	this.initLayoutView = function(){
		
		//check redirect:
		
		//get layout ID - if exists
		g_objWrapper = jQuery("#uc_edit_layout_wrapper");
		if(g_objWrapper.length == 0)
			throw new Error("No edit layout wrapper found");
		
		g_layoutID = g_objWrapper.data("layoutid");
		if(!g_layoutID)
			g_layoutID = null;
		
		var success = checkRedirectLayout();
		if(success == true)
			return(false);
				
		g_gridBuilder = new UniteCreatorGridBuilder();
		g_gridBuilder.init("#uc_grid_builder", t);
		
		initEvents();
		
		initShortcode();
		
		initImportLayoutDialog();
	}
	
	
	
	
}