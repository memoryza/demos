/**
* 全站弹窗提示，依赖于jquery，bootstrap
* author:memoryza(jincai.wang@foxmail.com)
**/
define([ 'jquery','Widget'], function(require, exports, module){
	var w = require('Widget');

	//弹框样式
	var styleList = {success: 1, warning: 1, danger: 1, primary: 1};

	function Dialog() {};
	Dialog.prototype = $.extend({}, new w.Widget(), {
		//初始化参数
		init: function(cfg) {
			var config  = {
				title: '提示',
				width: null,
				height: null,
				hasMask: false,//是否有遮罩
				hasCloseBtn: true,//是否有右上角的叉
				confirmText: '确定',
				confirm4Click: null,
				cancelText: '取消',
				cancel4Click: null,
				type: 'default',
				content: '提示内容为空',
				container: null //嵌入某个dom
			};
			this.cfg = $.extend({}, config, (typeof cfg == 'string' ? {content: cfg} : cfg));
			this.cfg.type = styleList[this.cfg.type] ? this.cfg.type : 'default';
		},
		//普通弹框
		alert: function(config) {
			this.init(config);
			this.boundingBox = $('<div class="panel panel-' + this.cfg.type + ' dialog-default">\
								<div class="panel-heading">\
									<h3 class="panel-title">' + this.cfg.title + (this.cfg.hasCloseBtn ? '<i class="dialog-close _closeLayer">X</i>' : '') + '</h3>\
								</div>\
								<div class="panel-body text-center">' + this.cfg.content + '</div>\
								<div class="panel-body text-right">\
									 <button type="button" class="btn btn-sm btn-' + this.cfg.type + ' _confrimBtn">' + this.cfg.confirmText + '</button>\
								<div>\
							</div>');
			if(this.cfg.hasMask) {
				$('<div class="dialog-mask"></div>').appendTo($('body'));
			}
			this.render(this.cfg.container);
		},
		prompt: function() {

		},
		confirm: function(config) {
			this.init(config);
			this.boundingBox = $('<div class="panel panel-' + this.cfg.type + ' dialog-default">\
								<div class="panel-heading">\
									<h3 class="panel-title">' + this.cfg.title + (this.cfg.hasCloseBtn ? '<i class="dialog-close _closeLayer">X</i>' : '') + '</h3>\
								</div>\
								<div class="panel-body text-center">' + this.cfg.content + '</div>\
								<div class="panel-body text-right">\
									 <button type="button" class="btn btn-sm btn-' + this.cfg.type + ' _confrimBtn">' + this.cfg.confirmText + '</button>\
									 <button type="button" class="btn btn-sm btn-default _cancelBtn">' + this.cfg.cancelText + '</button>\
								<div>\
							</div>');
			if(this.cfg.hasMask) {
				$('<div class="dialog-mask"></div>').appendTo($('body'));
			}
			this.render(this.cfg.container);
		},
		//内敛弹框
		inlineAlert: function(config) {
			this.init(config);
			this.boundingBox = $('<div class="alert alert-'+ this.cfg.type + '" role="alert">' + this.cfg.content + '</div>');
			this.render(this.cfg.container);
		},
		destructor: function() {
			if(this.cfg.hasMask) {
				$('.dialog-mask').remove();
			}
		},
		renderUI: function() {},
		bindUI: function() {
			var oThis = this;
			//this.on('close', this.destroy.bind(oThis));
			//关闭按钮
			var closeBtn = this.boundingBox.find('._closeLayer');
			closeBtn.on('click', function() {
				oThis.destroy();
			});
			
			//确定按钮
			var confirmBtn = this.boundingBox.find('._confrimBtn');
			confirmBtn.on('click', function() {
				if(typeof oThis.cfg.confirm4Click == 'function') {
					oThis.cfg.confirm4Click();
				}
				oThis.destroy();
			});

			//取消按钮
			var cancelBtn = this.boundingBox.find('._cancelBtn');
			cancelBtn.on('click', function() {
				if(typeof oThis.cfg.cancel4Click == 'function') {
					oThis.cfg.cancel4Click();
				}
				oThis.destroy();
				//oThis.fire('close');
			});
		},
		syncUI: function() {
			if(this.cfg.width) this.boundingBox.css({'width': this.cfg.width});
			if(this.cfg.height) this.boundingBox.css({'height': this.cfg.height + 'px'});
		}
	});
	module.exports = Dialog;
});