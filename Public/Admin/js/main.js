$(document).ready(function(){
	
})
function LeftMenuClick2(d_id){
	$('.left_box_2 dd').hide();
	$('#Left_'+d_id+'_box dd').show();
}
function Submit_Form(form,res_dom,Data_From_href,Data_From_action,Data_From_Tiele,confirmMsg) {
	if(!(confirmMsg == '' || confirmMsg == null )){
		$.messager.confirm('确认操作',confirmMsg,function(r){  
			if (!r){
				return false;
			}else{
				Submit_Form_fun(form,res_dom,Data_From_href,Data_From_action,Data_From_Tiele);
			}
		}) 
    }else{
		Submit_Form_fun(form,res_dom,Data_From_href,Data_From_action,Data_From_Tiele)
	}
}
function Submit_Form_fun(form,res_dom,Data_From_href,Data_From_action,Data_From_Tiele){
	var $form = $('#'+form);
	Data_From_Tiele=(Data_From_Tiele == '' || Data_From_Tiele == null )?'数据处理':Data_From_Tiele;
	if(Data_From_action == '' || Data_From_action == null){
		Data_From_action=Data_From_href;
	}
	$form.dialog({
		title:Data_From_Tiele,
		href:Data_From_href,
		modal:true,
		cache: false,
		buttons : [{
				text : '提交',
				iconCls : 'iconfont icon-edit',
				handler : function () {
					$.post(Data_From_action, $form.serialize(), function(res){
						if(!res.status){
							$.messager.show({title:'错误提示',msg:res.info,timeout:2000,showType:'slide'});
						}else{
							$form.dialog('close');
							$.messager.show({title:'成功提示',msg:res.info,timeout:1000,showType:'slide'});
							$('#'+res_dom).datagrid('reload');
							$('#'+res_dom).treegrid('reload');
						}
					})
				},
		}],
	})
}
/*刷行区域*/
function G_Ajax(this_Dom,Dom_paren_type,ToDom,url) {
	$('#'+ToDom).load(url);
	$(this_Dom).parents(Dom_paren_type).children().removeClass("selected");
	$(this_Dom).addClass("selected");
}



function UpdateTabs(model_name, url, tit, icon) {
	if ($('#'+model_name+'_Form').length>0) {
		$('#'+model_name+'_Form').closest('.window').next(".window-shadow").remove()
		$('#'+model_name+'_Form').closest('.window').next(".window-mask").remove()
		$('#'+model_name+'_Form').closest('.window').remove()
	}
	if ($('#'+model_name+'_Search_From').length>0) {
		$('#'+model_name+'_Search_From').closest('.window').next(".window-shadow").remove()
		$('#'+model_name+'_Search_From').closest('.window').next(".window-mask").remove()
		$('#'+model_name+'_Search_From').closest('.window').remove()
	}
	if ($('#tabs_'+model_name).length>0) {
		index = $('#MainTabs').tabs('getTabIndex',$('#tabs_'+model_name));
		$('#MainTabs').tabs('select',index)
		Selected_tabs=$('#MainTabs').tabs('getSelected')
		options_s={}
		options_s.href=url
		options_s.bodyCls="tabs_box"
		if(tit!=''){
			options_s.title=tit
		}
		if(icon!=''){
			options_s.iconCls=icon
		}
		$('#MainTabs').tabs('update', {
			tab:Selected_tabs,
			options: options_s
		});
		Selected_tabs.panel('refresh');
	} else {
		options_s={}
		options_s.id='tabs_'+model_name
		options_s.title=tit
		options_s.href=url
		options_s.closable=true
		options_s.bodyCls="tabs_box"
		if(icon!=null){
			options_s.iconCls=icon
		}else{
			options_s.iconCls='iconfont icon-viewlist'
		}
		$('#MainTabs').tabs('add', options_s);
	}
}

/*刷行区域*/
function LeftMenu(id) {
	$("#Main_Layout_West dl").removeClass("selected");
	$("#Left_"+id+"_box").addClass("selected");
	$("#Main_Layout_West .left_box li").removeClass("selected");
	$("#Left_"+id+"_nav li").addClass("selected");
}

function LeftMenuClick(name,tit,url,icon) {
	icon='iconfont '+icon
	var strs= new Array();
	strs=name.split("/"); //字符分割 
	var cname = strs[1];
	UpdateTabs(cname, url, tit, icon);
}

function LeftMenuClick_IF(name,tit,url,icon) {
	icon='iconfont '+icon
	var strs= new Array();
	strs=name.split("/"); //字符分割 
	var model_name = strs[1];
	$('#'+model_name+'_Search_From').closest('.window').next(".window-shadow").remove()
	$('#'+model_name+'_Search_From').closest('.window').next(".window-mask").remove()
	$('#'+model_name+'_Search_From').closest('.window').remove()
	
	if ($('#tabs_'+model_name).length>0) {
		index = $('#MainTabs').tabs('getTabIndex',$('#tabs_'+model_name));
		$('#MainTabs').tabs('select',index)
		Selected_tabs=$('#MainTabs').tabs('getSelected')
		options_s={}
		options_s.content='<iframe scrolling="yes" frameborder="0" src="'+url+'" style="width:100%;height:100%;"></iframe>';
		options_s.bodyCls="tabs_box"
		if(tit!=''){
			options_s.title=tit
		}
		if(icon!=''){
			options_s.iconCls=icon
		}
		$('#MainTabs').tabs('update', {
			tab:Selected_tabs,
			options: options_s
		});
		Selected_tabs.panel('refresh');
	} else {
		options_s={}
		options_s.id='tabs_'+model_name
		options_s.title=tit
		options_s.content='<iframe scrolling="yes" frameborder="0" src="'+url+'" style="width:100%;height:100%;"></iframe>';
		options_s.closable=true
		options_s.bodyCls="tabs_box"
		if(icon!=null){
			options_s.iconCls=icon
		}else{
			options_s.iconCls='iconfont icon-viewlist'
		}
		$('#MainTabs').tabs('add', options_s);
	}
}


//搜索
function Data_Search(Search_From,Datagrid_data){
	var Search_From_w=$('#'+Search_From).width();
	var Search_From_h=$('#'+Search_From).height();
	if(Search_From_w<1){Search_From_w=700}
	if(Search_From_h<1){Search_From_h=450}
	$('#'+Search_From).dialog({
		width:Search_From_w,   
		height:Search_From_h,  
		title : '搜索',
		modal:true,
		cache: false,
		buttons : [{
				text : '搜索',
				iconCls : 'iconfont icon-search',
				handler : function () {
					var queryParams = $('#'+Datagrid_data).datagrid('options').queryParams;
					$.each($('#'+Search_From).serializeArray(), function() {
						queryParams[this['name']] = this['value'];
						
					});
					$('#'+Datagrid_data).datagrid('reload');
				},
			}],
	})
}

/* 提交表单 */
function From_Submit(Model_name){
	$.post($('#'+Model_name+'_Submit_From').attr("action"), $('#'+Model_name+'_Submit_From').serialize(), function(res){
		if(!res.status){
			$.messager.show({title:'错误提示',msg:res.info,timeout:2000,showType:'slide'});
		}else{
			$.messager.show({title:'成功提示',msg:res.info,timeout:1000,showType:'slide'});
			UpdateTabs(Model_name, res.url+'&cachedata='+new Date().getTime(), '', 'iconfont icon-viewlist');
		}
	})
}

function Data_Remove(Data_from_url,Datagrid_data){
	$.messager.confirm('确定操作', '您正在要删除所选的记录吗？', function (flag) {
		if (flag){
			$.post(Data_from_url,{},function(res){
				if(!res.status){
					$.messager.show({title:'错误提示',msg:res.info,timeout:2000,showType:'slide'});
				}else{
					$.messager.show({title:'成功提示',msg:res.info,timeout:1000,showType:'slide'});
					$('#'+Datagrid_data).datagrid('reload');
					$('#'+Datagrid_data).treegrid('reload');
				}
			})
		}
	})
}
/* 刷新页面 */
function Data_Reload(Data_Box){
	$('#'+Data_Box).datagrid('reload');
	$('#'+Data_Box).treegrid('reload');
}



KindEditor.ready(function(K) {});


/* 上传附件 */

function updata_fields(file_box){
	KindEditor.ready(function(K) {
		updata_fields_editor = K.editor({
			allowFileManager : true,
			pasteType:ke_pasteType,
			fileManagerJson: ke_fileManagerJson,
			uploadJson: ke_uploadJson,
			extraFileUploadParams: {
				uid: ke_Uid
			}
		});
		updata_fields_editor.loadPlugin('insertfile', function() {
			updata_fields_editor.plugin.fileDialog({
				fileUrl : $('#'+file_box).textbox('getValue'),
				clickFn : function(url, title) {
					$('#'+file_box).textbox('setValue',url);
					updata_fields_editor.hideDialog();
				}
			});			
		});
	});
}

/* 上传图片 */

function updata_image(image_box){
	KindEditor.ready(function(K) {
		var updata_image_editor = K.editor({
			allowFileManager : true,
			pasteType:ke_pasteType,
			fileManagerJson: ke_fileManagerJson,
			uploadJson: ke_uploadJson,
			extraFileUploadParams: {
				uid: ke_Uid
			}
		});
		updata_image_editor.loadPlugin("image", function() {
			updata_image_editor.plugin.imageDialog({
				imageUrl : $('#'+image_box).textbox('getValue'),
				clickFn : function(url, title, width, height, border, align) {
					$('#'+image_box).textbox('setValue',url);
					updata_image_editor.hideDialog();
				}
			});
		});
	});
}

(function($, K) {
	if (!K) throw "KindEditor未定义!";
	
	function create(target) {
		var opts = $.data(target, 'kindeditor').options;
		var editor = K.create(target, opts);
		$.data(target, 'kindeditor').options.editor = editor;
	}

	$.fn.kindeditor = function(options, param) {
		if (typeof options == 'string') {
			var method = $.fn.kindeditor.methods[options];
			if (method) {
				return method(this, param);
			}
		}
		options = options || {};
		return this.each(function() {
			
			if($(this).attr('config_date')==0){
				config_date=['fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link'];
			}else if($(this).attr('config_date')==1){
				config_date=[
		'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
		'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
		'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
		'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
		'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
		'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
		'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
		'anchor', 'link', 'unlink', '|', 'about'
	];
			}else{
				config_date=['fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link'];
			}
			
			var state = $.data(this, 'kindeditor');
			if (state) {
				$.extend(state.options, options);
			} else {
				state = $.data(this, 'kindeditor', {
					options: $.extend(
					{},
					{
						resizeType: 1,
						allowPreviewEmoticons: false,
						allowImageUpload: false,
						items: config_date,
						allowFileManager : true,
						pasteType:ke_pasteType,
						fileManagerJson: ke_fileManagerJson,
						uploadJson: ke_uploadJson,
						extraFileUploadParams: {
							uid: ke_Uid
						},
						afterChange: function() {
							this.sync();
						},
						afterBlur: function() {
							this.sync();
						}
					},
					$.fn.kindeditor.parseOptions(this), options)
				});
			}
			create(this);
		});
	}

	$.fn.kindeditor.parseOptions = function(target) {
		return $.extend({},
		$.parser.parseOptions(target, []));
	};

	$.fn.kindeditor.methods = {
		editor: function(jq) {
			return $.data(jq[0], 'kindeditor').options.editor;
		}
	};
	$.parser.plugins.push("kindeditor");
})(jQuery, KindEditor);
