
function UniteCreatorPageBuilder(){

	var t = this;
	var g_objIframe, g_objWrapper, g_panel, g_layoutID;
	var g_panelActions = new UniteCreatorGridActionsPanel();
	var g_gridBuilder, g_objPanelWrapper;
	var g_objBuffer = new UniteCreatorBuffer();
	var g_panel = new UniteCreatorGridPanel();
	var g_objBrowser = new UniteCreatorBrowser();
	
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	this.events = {
			VIEW_CHANGED: "view_changed",
			IFRAME_INITED: "iframe_inited"
	};
	
	this.vars = {
			VIEW_MOBILE: "mobile",
			VIEW_TABLET: "tablet",
			VIEW_DESKTOP: "desktop"
	};
	
	var g_temp = {
			is_save_enabled: false,
			view: t.vars.VIEW_DESKTOP,
			skip_message_onexit: false
	};
	
	
	
    
    
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
		
		updateLayout(false, function(){
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
	};
	
	
	/**
	 * hide save button
	 */
	function hideSaveButton(){
		jQuery("#uc_button_update_layout").hide();
		g_temp.is_save_enabled = false;
		
	};
	
	
	/**
	 * disable save button
	 */
	this.hideSaveButton = function(){		//for outside
		hideSaveButton();
	};

	/**
	 * hide new page stuff and show existing page stuff
	 */
	function switchInterfaceToExisting(layoutID){
		
		g_layoutID = layoutID;
		
		//g_objWrapper.data("layoutid", g_layoutID);
		
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
			
		var viewLayout = g_ucAdmin.getUrlView("layout_outer","id="+layoutID+"&ucwindow=blank");
		
		showLoaderMessage("Loading Page...");
		
		location.href = viewLayout;
		
		return(true);
	}

	
	/**
	 * on actions panel button click
	 */
	function runAction(action, params){
		
		switch(action){
			case "open_main_menu":
				g_panel.toggle("main-menu");
			break;
			case "view_desktop":
				changeView(t.vars.VIEW_DESKTOP);
			break;
			case "view_mobile":
				changeView(t.vars.VIEW_MOBILE);
			break;
			case "view_tablet":
				changeView(t.vars.VIEW_TABLET);				
			break;
			case "import":
				openImportLayoutDialog();				
			break;
			case "export":
				exportLayout();
			break;
			case "save_exit":
				var urlBack = g_ucAdmin.getVal(params, "url_back");
				var message = g_ucAdmin.getVal(params, "message");
				updateLayoutAndExit(urlBack, message);
			break;			
			default:
				throw new Error("wrong action: "+action);
			break;
		}
		
	}
	
	function _______SHORTCODE_________(){}
	
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
	
	
	function _______SAVE_LAYOUT_________(){}
	
	
	/**
	 * on update layout button click
	 */
	function updateLayout(isTitleOnly, funcOnSuccess){
		
		if(!isTitleOnly && g_temp.is_save_enabled == false)
			return(true);
		
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
				}else{
					
					if(isTitleOnly !== true)
						disableSave();
				}
					
					
				if(funcOnSuccess)
					funcOnSuccess();
		});
	}

    /*
     * on save title button click
     */
    function onSaveTitleClick(){
       
    	updateLayout(true);
	}
	    
    /**
     * update layout and exit
     */
    function updateLayoutAndExit(urlBack, message){
    	
    	g_ucAdmin.validateNotEmpty(urlBack, "back url");
    	g_ucAdmin.validateNotEmpty(message, "message");
    	
    	g_temp.is_save_enabled = true;
    	
    	updateLayout(false, function(){
    		showLoaderMessage(message);
    		
    		g_temp.skip_message_onexit = true;
    		
    		setTimeout(function(){
        		location.href = urlBack;
    		},500);
    		
    	});
    	
    }
	
		
	function _______IMPORT_EXPORT_________(){}
	
	/**
	 * export layout
	 */
	function exportLayout(){
		
		var params = "id="+g_layoutID;
		var urlExport = g_ucAdmin.getUrlAjax("export_layout", params);
		location.href=urlExport;
		
		g_temp.skip_message_onexit = true;
	}
	
	
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
	 * change view - desktop / mobile / tablet
	 */
	function changeView(view){
		
		switch(view){
			case t.vars.VIEW_DESKTOP:
				g_objWrapper.removeClass("uc-view-mobile");
				g_objWrapper.removeClass("uc-view-tablet");
				g_objWrapper.addClass("uc-view-desktop");				
			break;
			case t.vars.VIEW_MOBILE:
				g_objWrapper.removeClass("uc-view-desktop");
				g_objWrapper.removeClass("uc-view-tablet");
				g_objWrapper.addClass("uc-view-mobile");
			break;
			case t.vars.VIEW_TABLET:
				g_objWrapper.removeClass("uc-view-desktop");
				g_objWrapper.removeClass("uc-view-mobile");
				g_objWrapper.addClass("uc-view-tablet");
			break;
			default:
				throw new Error("Wrong view: "+t.vars.VIEW_MOBILE);
			break;
		}
				
		
		g_temp.view = view;
		triggerEvent(t.events.VIEW_CHANGED, view);
	}
	
	
	/**
	 * disable save button
	 */
	function disableSave(){
		
		g_objWrapper.addClass("uc-state-saved");
		g_temp.is_save_enabled = false;
		
	}
	
	/**
	 * enable save button
	 */
	function enableSave(){
		
		g_objWrapper.removeClass("uc-state-saved");
		g_temp.is_save_enabled = true;
		
	}
	
	function ____________EVENTS______________(){}
	
	/**
	 * detect if menu opened, and update wrapper class
	 */
	function checkPanelMenuState(){
		
		var paneName = g_panel.getActivePaneName();
		var isVisible = g_panel.isVisible();
		
		var className = "uc-main-menu-opened";
		
		if(paneName == "main-menu" && isVisible == true)
			g_objWrapper.addClass(className);
		else
			g_objWrapper.removeClass(className);
		
	}
	
	
	/**
	 * run on grid some change taken
	 */
	function onGridChangeTaken(event, origEventName){
		
		enableSave();
	}
	
	
	/**
	 * trigger event
	 */
	function triggerEvent(eventName, options){
		
		g_objWrapper.trigger(eventName, options);
		
	}
	
	
	/**
	 * on some event
	 */
	this.onEvent = function(eventName, func){
		
		g_objWrapper.on(eventName, func);
		
	};
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		jQuery("#uc_button_update_layout").click(updateLayout);
		jQuery("#uc_button_rename_page").click(onSaveTitleClick);
						
		jQuery("#uc_button_edit_mode").click(onEditModeButtonClick);
		
		jQuery(".uc-save-status-close").click(function(){
			var objMessage = jQuery(this).parents(".uc-save-status");
			objMessage.hide();
		});
		
		jQuery("#uc_button_grid_settings").click(function(){
			
			g_gridBuilder.doAction("open_grid_settings");
			
		});
		
		//on action buttons click
		g_panelActions.onEvent(g_panelActions.events.BUTTON_CLICK, function(event, action){
			runAction(action);
		});
		
		g_panel.onEvent(g_panel.events.ACTION_BUTTON_CLICK,function(event, action, params){
			runAction(action, params);
		});
		
		g_panel.onEvent(g_panel.events.SWITCH_PANE, checkPanelMenuState);
		g_panel.onEvent(g_panel.events.SHOW, checkPanelMenuState);
		g_panel.onEvent(g_panel.events.HIDE, checkPanelMenuState);
		
		
		//init grid events:
		t.onEvent(t.events.IFRAME_INITED, function(){
			
			g_gridBuilder.onEvent(g_gridBuilder.events.CHANGE_TAKEN, onGridChangeTaken);
			
		});
		
		
	}
	
	
	
	/**
	 * init grid builder from inside of iframe
	 */
	this.initGridBuilder = function(objGridBuilder){
		
		g_gridBuilder = objGridBuilder;
		
		triggerEvent(t.events.IFRAME_INITED);
	};
	
	
	/**
	 * get panel wrapper
	 */
	this.getSidePanel = function(){
		
		return(g_panel);
	};
	
	
	/**
	 * init memory container
	 */
	function initBufferContainer(){
		
		g_objBuffer.addType("row", g_uctext["row"]);
		g_objBuffer.init();
		
	}
	
	/**
	 * get buffer
	 */
	this.getObjBuffer = function(){
		
		return(g_objBuffer);
	};
	
	/**
	 * get browser
	 */
	this.getObjBrowser = function(){
		
		return(g_objBrowser);
	};
	
	/**
	 * get iframe
	 */
	this.getIframe = function(){
		return(g_objIframe);
	};
	
	/**
	 * on before unload - decide if show message before depend on save state
	 */
	this.onBeforeUnload = function(){
		
		if(g_temp.skip_message_onexit == true){
			g_temp.skip_message_onexit = false;
			return(false);
		}
		
		return g_temp.is_save_enabled;
	};
	
	
	/**
	 * init the outer grid builder
	 */
	this.init = function(){
		
		g_objWrapper = jQuery("#uc_page_builder");
		g_ucAdmin.validateDomElement(g_objWrapper, "page builder wrapper");
		
		g_objIframe = g_objWrapper.find("iframe.uc-layout-iframe");
		g_ucAdmin.validateDomElement(g_objIframe, "page builder iframe");
		
		checkRedirectLayout();
		
		//get layout ID - if exists		
		g_layoutID = g_objWrapper.data("pageid");
				
		//init browser
		var objBrowserWrapper = jQuery("#uc_addon_browser");
		
		g_ucAdmin.validateDomElement(objBrowserWrapper, "addon browser");
		g_objBrowser.init(objBrowserWrapper);
		
		
		//init buffer
		initBufferContainer();
		
		//init side panel
		g_objPanelWrapper = g_objWrapper.find(".uc-grid-panel");
		g_ucAdmin.validateDomElement(g_objPanelWrapper, "grid panel wrapper");
		
		g_panel.init(g_objPanelWrapper, g_objBuffer);
		
		if(!g_layoutID)
			g_layoutID = null;
		
		initShortcode();
		
		//init actions panel
		var objActionsPanelWrapper = g_objWrapper.find(".uc-edit-layout-panel");
		g_ucAdmin.validateDomElement(objActionsPanelWrapper, "actions panel");
		
		g_panelActions.init(objActionsPanelWrapper);
		
		initEvents();		
		
		initImportLayoutDialog();
		
	};
	
}