/**
* widget 类，跟UI组件相关代码
* memoryza(jincai.wang@foxmail.com)
**/
define(function() {
	function Widget() {
		this.handlers = {};
		this.boundingBox = null;//容器
	}
	Widget.prototype = {
		on: function(type, handler) {
			if(typeof this.handlers[type] == "undefined") {
				this.handlers[type] = [];
			}
			this.handlers[type].push(handler);
			return this;
		},
		fire: function(type, data) {
			if(this.handlers[type] instanceof Array) {
				var handlers = this.handlers[type];
				for(var i = 0, len = handlers.length; i < len; i++) {
					handlers[i](data);
				}
			}
		},
		unfire: function(type, handler) {
			if(this.handlers[type] instanceof Array) {
				for(var i = 0, len = this.handlers[type].length; i < len; i++) {
					if(this.handlers[type][i] == handler) {
						this.handlers.splice(i, 1);
					}
				}
			}
			return this;
		},
		render: function(container) {//绘制
			this.renderUI();
			this.handlers = {};
			this.bindUI();
			this.syncUI();
			$(container || document.body).append(this.boundingBox); 
		},
		destroy: function() { //销毁组件
			this.destructor();
			this.boundingBox.off();
			this.boundingBox.remove();
		},
		//接口类型
		renderUI: function() {},//绘制dom
		bindUI: function() {},//监听事件
		syncUI: function() {},//初始化组件属性
		destructor: function(){}//组件销毁处理
	}
	return {Widget: Widget};
});