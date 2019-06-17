function UniteCreatorParamsPanel(){
	
	var g_objWrapper, g_prefix = "", g_type, g_arrConstants = {};
	var g_objFiltersWrapper, g_activeFilter = null, g_objThumbSizes = null;
	var g_objChildKeys = null, g_objAddKeys = null, g_objTemplateCode = null;
	
	
	var t = this;
	var g_temp = {
			funcOnClick: function(){}
	};
	
	var events = {
			DELETE_VARIABLE: "delete_variable",
			EDIT_VARIABLE: "edit_variable"
	};
	
	if(!g_ucAdmin)
		var g_ucAdmin = new UniteAdminUC();
	
	
	/**
	 * validate that the panel is inited
	 */
	function validateInited(){
		if(!g_objWrapper)
			throw new Error("The panel is not inited");
	}

	/**
	 * get prefix by fitler
	 */
	function getPrefix(filter){
		
		if(typeof g_prefix == "string")
			return(g_prefix);
		
		if(!filter || typeof g_prefix != "object")
			return("");
		
		if(g_prefix.hasOwnProperty(filter) == false)
			return("");
		
		var prefix = g_prefix[filter];
		
		return(prefix);
	}
	
	
	/**
	 * get template code by name
	 */
	function getTemplateCode(key, paramName, parentName){
		
		var strCode = g_ucAdmin.getVal(g_objTemplateCode, key);
		
		if(!strCode)
			throw new Error("Template code with key: "+key+" not found");
		
		if(paramName)
			strCode = strCode.replace("[param_name]", paramName);
				
		if(parentName)
			strCode = strCode.replace("[param_prefix]", parentName);
		
		return(strCode);
	}
	
	
	function ___________ADD_PARAMS___________(){}
	

	/**
	 * add image base params
	 */
	function addImageBaseParams(objParam, filter){
		
		//var arrParams = ["image","thumb","description","enable_link","link"];
		var arrParams = ["image","thumb","description"];
		
		jQuery.each(arrParams, function(index, name){
			addParam(name, null, filter);
		});
		
	}
	
	
	/**
	 * add textare param fields
	 */
	function addTextareaParam(objParam, filter){
		
		var name = objParam.name;
		
		addParam(name, null, filter);
		addParam(name+"|raw", null, filter);
		
	}
	
	
	
	/**
	 * add image param
	 */
	function addImageParam(objParam, filter){
				
		var isAddThumb = g_ucAdmin.getVal(objParam, "add_thumb", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		var isAddThumbLarge = g_ucAdmin.getVal(objParam, "add_thumb_large", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		
		var name = objParam.name;
		
		var parentName = g_ucAdmin.getVal(objParam, "parent_name");
		var isParamChild = (parentName != "");
		var isAddThumbSizes = (g_objThumbSizes != null) && (isParamChild == false);
				
		//create thumb param
		var objParamThumb = jQuery.extend({}, objParam);
		objParamThumb.name = name + "_thumb";
		objParamThumb.tooltip = null;
		objParamThumb.type = null;
				
		if(isAddThumbSizes == true){
			
			var objParamParent = jQuery.extend({}, objParam);
			
			objParamParent.type = null;
			objParamParent.is_parent = true;
			objParamParent.tooltip = "Show All Thumbs";
			
			addParam(objParamParent, null, filter);
		}
		else{			//if only image present
			
			addParam(objParam, "uc_textfield", filter);
		}
		
		//add child sizes
		if(isAddThumbSizes == false){
			
			if(isAddThumb){
				objParamThumb.tooltip = "Thumb";
				
				addParam(objParamThumb, null, filter);
			}
			
			if(isAddThumbLarge){
				objParamThumb.name = name+"_thumb_large";
				objParamThumb.tooltip = "Large";
				addParam(objParamThumb, null, filter);
			}
			
		}else{
			
			//add all the sizes
			
			objParamThumb.parent_name = name;
			
			//add thumb
			objParamThumb.tooltip = "Thumb";
			
			addParam(objParamThumb, null, filter);
						
			//add other sizes
			jQuery.each(g_objThumbSizes, function(size, sizeTitle){
				
				objParamThumb.name = name+"_thumb_"+size;
				objParamThumb.tooltip = sizeTitle;
				
				if(size == "large" && isAddThumbLarge)
					objParamThumb.type = "uc_textfield";
				
				
				addParam(objParamThumb, null, filter);
			});
			
		}
			
	}
	
	
	/**
	 * add child params
	 */
	function addChildParams(objParentParam, arrChildKeys, filter){
		
		var baseName = objParentParam.name;
		var parentName = baseName;
		
		//add parent param
		var paramParent = {
				name: parentName,
				is_parent: true,
				parent_open_onclick: true
			};
		
		addParam(paramParent, null, filter);
		
		//add child params:
		jQuery.each(arrChildKeys, function(index, objChildParam){
			
			var objParamInsert = jQuery.extend({}, objChildParam);
			
			objParamInsert.name = baseName + "." + objChildParam.name;
			objParamInsert.parent_name = parentName;
			objParamInsert.is_child = true;
			objParamInsert.original_name = objChildParam.name;
			
			//put parent param
			addParam(objParamInsert, null, filter);
			
		});
		
	}
	
	
	/**
	 * add child params
	 */
	function addAddParams(objParentParam, arrAddKeys, filter){
		
		var parentName = objParentParam["name"];
						
		jQuery.each(arrAddKeys,function(index, objAddParam){
			
			var objParamInsert = jQuery.extend({}, objAddParam);
			
			if(g_type != "item"){
				objParamInsert["name"] = parentName + "_"+objParamInsert["name"];
			}
			
			addParam(objParamInsert, null, filter);
			
		});
		
	}
		
	
	/**
	 * add param to panel
	 * can accept name:string, type:string or object
	 */
	function addParam(objParam, type, filter){
		
		
		if(typeof objParam == "string"){
			objParam = {
				name: objParam,
				type: "uc_textfield"
			};
		}
		
		
		//get param type
		if(type)
			objParam.type = type;
		
		var paramType = g_ucAdmin.getVal(objParam, "type");
		var name = objParam.name;
		
		
		//check for param groups
		var rawInsertText = null;
		var paramVisual = null;
		
		//modify by param type
				
		switch(paramType){
			case "uc_hr":
				return(false);	//don't add hr
			break;
			case "uc_imagebase":
				addImageBaseParams(objParam, filter);
				return(false);
			break;
			case "uc_textarea":
				addTextareaParam(objParam, filter);
				return(false);
			break;
			case "uc_image":
				
				addImageParam(objParam, filter);
				return(false);
			break;
			case "uc_posts_list":
				
				rawInsertText = getTemplateCode("no_items_code", name);
				paramVisual = name + " wrapping code";
				
			break;
			case "uc_font_override":
				
				if(g_type != "css")
					return(false);
					
				rawInsertText = "{{put_font_override('"+name+"','.selector',true)}}";
				paramVisual = "{{"+name + "_font_override"+"}}";
				
			break;
			default:
				
				//check child keys
				var arrChildKeys = g_ucAdmin.getVal(g_objChildKeys, objParam.type);
				if(arrChildKeys){
					addChildParams(objParam, arrChildKeys, filter);
					return(false);
				}
				
				//add "add" keys, additional keys for this param
				var arrAddKeys = g_ucAdmin.getVal(g_objAddKeys, objParam.type);
				if(arrAddKeys){
					addAddParams(objParam, arrAddKeys, filter);
					return(false);
				}
				
			break;
		}
		
		var originalName = g_ucAdmin.getVal(objParam, "original_name");
		
		//modify by param name
		switch(originalName){
			case "no_items_code":
				var childParamName = objParam.parent_name+"."+ objParam.child_param_name;
				
				rawInsertText = getTemplateCode("no_items_code", childParamName, objParam.parent_name);
			break;
		}
		
		if(!rawInsertText){
			rawInsertText = g_ucAdmin.getVal(objParam, "raw_insert_text");
		}
		
		
		//get param class type 
		var paramClassType = "uc-type-param";
		switch(objParam.type){
			case "uc_function":
				paramClassType = "uc-type-function";
			break;
			case "uc_constant":
				paramClassType = "uc-type-constant";
			break;
		}
		
		//set filter class
		var classFilter = getFilterClass(filter);
		
		var specialParamType = "regular";
		
		var isParent = g_ucAdmin.getVal(objParam, "is_parent", false, g_ucAdmin.getvalopt.FORCE_BOOLEAN);
		if(isParent === true)
			specialParamType = "parent";
		else{
			var parentName = g_ucAdmin.getVal(objParam, "parent_name");
			if(parentName)
				specialParamType = "child";
		}
		
		
		//set ending
		var ending = "";
		switch(objParam.type){
			case "uc_joomla_module":
			case "uc_editor":
				ending = "|raw";
			break;
		}
		
		var prefix = getPrefix(filter);
		
		var textNoSlashes = prefix+name+ending;
		var textNoSlashesParent = prefix+name;
		
		if(specialParamType == "child")
			textNoSlashesParent = prefix+parentName;
			
		var text = "{{"+textNoSlashes+"}}";
		
		if(rawInsertText){
			rawInsertText = rawInsertText.replace("[param_name]", textNoSlashes);
			rawInsertText = rawInsertText.replace("[param_prefix]", textNoSlashesParent);
			rawInsertText = g_ucAdmin.htmlspecialchars(rawInsertText);
		}
		
		//check if hidden by filter
		var style = "";
		if(g_activeFilter && filter && g_activeFilter !== filter)
			style = "style='display:none'";
		
		var htmlClass = "uc-link-paramkey " + paramClassType +" " + classFilter;
		var htmlTip = "";
		
		var tooltip = g_ucAdmin.getVal(objParam, "tooltip");
		
		var addHtml = "";
				
		if(rawInsertText){
			addHtml += " data-rawtext=\""+rawInsertText+"\"";
		}
		
		//special output
		switch(specialParamType){
			case "parent":
								
				if(!tooltip)
					tooltip = "Show All Fields";
				
				var isOpenOnClick = g_ucAdmin.getVal(objParam, "parent_open_onclick");
				if(isOpenOnClick === true){
					addHtml = " data-openonclick='true'";
					text = textNoSlashes;
				}
				
				var html = "<div class='uc-param-wrapper uc-param-parent uc-hover "+classFilter+"' "+style+" data-name='"+name+"' "+addHtml+">";
				html += "		<a data-name='"+name+"' data-text='"+text+"' href='javascript:void(0)' class='uc-link-paramkey "+classFilter+"' >"+text+"</a>";
				html += "		<div class='uc-icons-wrapper uc-icons-parent'>";
				html += "			<a class='uc-icon-show-children uc-tip' title='"+tooltip+"'></a>";
				html += "		</div>";
				html += "</div>";
			break;
			case "child":
								
				if(tooltip)
					htmlTip = " title='"+tooltip+"'";
				
				htmlClass += " ucparent-"+parentName+" uc-child-key uc-child-hidden";
			default:
				
				if(paramVisual == null)
					paramVisual = text;
				
				var html = "<a data-name='"+name+"' data-text='"+text+"' href='javascript:void(0)' class='"+htmlClass+"' "+style+htmlTip+addHtml+">"+paramVisual+"</a>";
			break;
		}
		
		
		g_objWrapper.append(html);
	}
	
	function ___________VARIABLES_CONSTANTS___________(){}
	
	
	/**
	 * add param to panel
	 */
	function addVariable(index, objVar, filter){
		
		if(typeof objVar != "object")
			throw new Error("The variable should be object");
		
		var name = objVar.name;
		var prefix = getPrefix(filter);
		var text = "{{"+prefix+name+"}}";
		
		//set class
		var classFilter = getFilterClass(filter);
		var htmlClass = "uc-link-paramkey uc-type-variable "+classFilter;

		var style = "";
		if(g_activeFilter && filter && g_activeFilter !== filter)
			style = "style='display:none'";
		
		var html = "<div class='uc-param-wrapper uc-variable-wrapper' data-name='"+name+"' data-index='"+index+"'>";
		html += "<a data-name='"+name+"' data-text='"+text+"' href='javascript:void(0)' class='"+htmlClass+"' "+style+">"+text+"</a>";
		html += "<div class='uc-icons-wrapper'>";
		html += "<div class='uc-icon-edit'></div>";
		html += "<div class='uc-icon-delete'></div>";
		html += "</div>";
		html += "</div>";
		
		g_objWrapper.append(html);
	}
	
	/**
	 * add constant params as prefix
	 */
	function addConstants(argFilter){
		
		if(!g_arrConstants)
			return(false);
		
		if(typeof g_arrConstants != "object")
			return(false);
		
		if(g_arrConstants.length == 0)
			return(false);
		
		jQuery.each(g_arrConstants, function(filter, name){
			
			if(argFilter && filter != argFilter)
				return(true);
				
			var arrConstants = g_arrConstants[filter];
			
			jQuery.map(arrConstants,function(name){
				addParam(name, "uc_constant", filter);
			});
			
		});
		
	}
	
	
	function ___________EVENTS___________(){}
	
	/**
	 * on param click
	 */
	function onParamClick(){
		var objParam = jQuery(this);
		
		var text = objParam.data("text");
		var rawText = objParam.data("rawtext");
		if(rawText)
			text = rawText;
		
		//check if open children on click
		var objParent = objParam.parents(".uc-param-parent");
		if(objParent.length != 0){
			var openOnClick = objParent.data("openonclick");
			if(openOnClick === true){
				var objIcon = objParent.find(".uc-icon-show-children");
				objIcon.trigger("click");
				return(false);
			}
			
		}
		
		g_temp.funcOnClick(text, rawText);
	}
	
	
	
	/**
	 * trigger event
	 */
	function triggerEvent(eventName, params){
		if(!params)
			var params = null;
		
		g_objWrapper.trigger(eventName, params);
	}
	
	
	/**
	 * on event name
	 */
	function onEvent(eventName, func){
		g_objWrapper.on(eventName,func);
	}
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		g_objWrapper.delegate("a.uc-link-paramkey", "click", onParamClick);

		g_objWrapper.delegate("a.uc-link-paramkey", "focus", function(){
			this.blur();
		});
		
		//show, hide icons panel
		
		g_objWrapper.delegate(".uc-variable-wrapper", "mouseenter", function(){
			jQuery(this).addClass("uc-hover");
		});
		
		g_objWrapper.delegate(".uc-variable-wrapper", "mouseleave", function(){
			jQuery(this).removeClass("uc-hover");
		});
		
		
		g_objWrapper.delegate(".uc-variable-wrapper .uc-icon-edit","click",function(){
			
			var objLink = jQuery(this);
			var objVarWrapper = objLink.parents(".uc-variable-wrapper");
			
			var varIndex = objVarWrapper.data("index");
			
			triggerEvent(events.EDIT_VARIABLE, varIndex);
		
		});
		
		g_objWrapper.delegate(".uc-param-parent .uc-icon-show-children","click",function(){
			
			var objLink = jQuery(this);
			
			var objMenu = objLink.parents(".uc-icons-wrapper");
			var objParamWrapper = objLink.parents(".uc-param-wrapper");
			var paramName = objParamWrapper.data("name");
			var classChildren = ".ucparent-"+paramName;
			
			var objChildren = g_objWrapper.find(classChildren);
						
			objMenu.hide();
			objChildren.removeClass("uc-child-hidden");
			
		});
		
		
		g_objWrapper.delegate(".uc-variable-wrapper .uc-icon-delete","click",function(){
			
			var objLink = jQuery(this);
			var objVarWrapper = objLink.parents(".uc-variable-wrapper");
			var varIndex = objVarWrapper.data("index");
			
			triggerEvent(events.DELETE_VARIABLE, varIndex);
			
		});
		
	}
	
	
	/**
	 * remove all params
	 */
	this.removeAllParams = function(){
		g_objWrapper.html("");
	}
	
	
	
	function ___________FILTERS___________(){}
	
	
	/**
	 * get fitler class
	 */
	function getFilterClass(filter, addDot){
		
		if(!filter)
			return("");
		
		var prefix = "";
		if(addDot === true)
			prefix = ".";
		
		filter = filter.replace(".","_");
		filter = filter.replace("|e","");
		
		classFilter = prefix+"uc-filter-"+filter;
		
		return(classFilter);
	}
	
	/**
	 * activate all filter tabs
	 */
	function onFilterTabClick(){
		var activeClass = "uc-filter-active";
		
		var objFilter = jQuery(this);
		if(objFilter.hasClass(activeClass))
			return(false);
		
		var otherFitlers = g_objFiltersWrapper.find("a").not(objFilter);
		otherFitlers.removeClass(activeClass);
		
		objFilter.addClass(activeClass);
		
		g_activeFilter = objFilter.data("filter");
		
		//hide, show filters
		var classFilter = getFilterClass(g_activeFilter, true);
		
		var objFilterKeys = g_objWrapper.find(classFilter);
		var objOtherKeys = g_objWrapper.find("a.uc-link-paramkey").add(g_objWrapper.find(".uc-param-wrapper")).not(objFilterKeys);
		
		objOtherKeys.hide();
		objFilterKeys.show().css({"display":"block"});
		
	}
	
	
	/**
	 * init filter tabs
	 */
	function initFilterTabs(){
		
		var objFilterWrapper = g_objWrapper.siblings(".uc-params-panel-filters");
		
		if(objFilterWrapper.length == 0)
			return(false);
		
		g_objFiltersWrapper = objFilterWrapper;
		
		
		//set active filter
		
		var objActiveFilter = g_objFiltersWrapper.find("a.uc-filter-active");
		if(objActiveFilter.length == 0)
			throw new Error("Must have at least one active filter!!!");
		
		g_activeFilter = objActiveFilter.data("filter");
		
		//set events
		g_objFiltersWrapper.delegate("a", "click", onFilterTabClick);
	}
	
	
	/**
	 * replace all params
	 */
	this.setParams = function(arrParams, arrVariables, filter){
		
		if(!filter)
			t.removeAllParams();
		
		//add constants
		addConstants(filter);
		
		//add params
		jQuery.each(arrParams, function(index, param){
			addParam(param, null, filter);
		});
		
		//add variables
		if(arrVariables && typeof arrVariables == "object"){
			
			jQuery.each(arrVariables, function(index, objVar){
				addVariable(index, objVar, filter);
			});
			
		}
			
	}
	
	
	/**
	 * on param click
	 */
	this.onParamClick = function(func){
		g_temp.funcOnClick = func;
	};
	
	
	/**
	 * on edit variable
	 */
	this.onEditVariable = function(func){
		onEvent(events.EDIT_VARIABLE, func);
	}
	
	
	/**
	 * on delete variable function
	 */
	this.onDeleteVariable = function(func){
		onEvent(events.DELETE_VARIABLE, func);
	};
	
	
	/**
	 * init global setting
	 */
	function initGlobalSetting(name, data){
		
		if(!data || data.length == 0)
			return(false);
		
		g_ucAdmin.storeGlobalData(name, data);
		
	}
	
	/**
	 * set thumb sizes
	 */
	this.initGlobalSetting_ThumbSizes = function(objThumbSizes){
		
		initGlobalSetting("param_panel_thumb_sizes", objThumbSizes);
	};
	
	
	/**
	 * set thumb sizes
	 */
	this.initGlobalSetting_ChildKeys = function(objChildKeys, objAddKeys){
		
		initGlobalSetting("param_panel_child_keys", objChildKeys);
		initGlobalSetting("param_panel_add_keys", objAddKeys);
		
	};
	
	/**
	 * init template code
	 */
	this.initGlobalSetting_TemplateCode = function(objTemplateCode){
		
		initGlobalSetting("param_panel_template_code", objTemplateCode);
	
	};
	
	
	/**
	 * init the panel
	 */
	this.init = function(objWrapper, type, prefix, arrConstants){
		g_objWrapper = objWrapper;
		
		g_type = type;
		
		
		if(prefix)
			g_prefix = prefix;
		
		initFilterTabs();
		
		if(arrConstants && typeof arrConstants == "object")
			t.initConstants(arrConstants, "all");
		
		//get the sizes
		g_objThumbSizes = g_ucAdmin.getGlobalData("param_panel_thumb_sizes");
		if(!g_objThumbSizes)
			g_objThumbSizes = null;
		
		//get the child keys
		g_objChildKeys = g_ucAdmin.getGlobalData("param_panel_child_keys");
		if(!g_objChildKeys)
			g_objChildKeys = null;
		
		g_objAddKeys = g_ucAdmin.getGlobalData("param_panel_add_keys");
		if(!g_objAddKeys)
			g_objAddKeys = null;
		
		g_objTemplateCode = g_ucAdmin.getGlobalData("param_panel_template_code");
		if(!g_objTemplateCode)
			g_objTemplateCode = null;
		
		
		initEvents();
	};
	
	
	/**
	 * init consants
	 */
	this.initConstants = function(arrConstants, filter){
		
		if(!arrConstants || typeof arrConstants != "object")
			return(false);
		
		if(!g_arrConstants)
			g_arrConstants = {};
		
		if(!filter)
			filter = "all";
		
		g_arrConstants[filter] = arrConstants;
		
	}
	
}