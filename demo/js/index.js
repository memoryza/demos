define(function(require) {
	var d = require('Dialog');

	$(function() {


		$(document)
			.on('click', '._clickNormal', function() {
				new d().alert({type: 'warning', content: '这是一个简单的模拟alert的提示'})
			})
			.on('click', '._clickPrompt', function() {
				
			})
			.on('click', '._clickConfirm', function() {
				new d().confirm({type: 'danger', content: '你同意嘛？', title: '网站获取信息', confirmText:'同意', cancelText: '不同意',hasCloseBtn:false,
					confirm4Click: function() {
						alert('您点击了同意')
					},
					cancel4Click: function() {
						alert('您点击了不同意');
					}
				});
			})
			.on('click', '._clickInline', function() {
				new d().inlineAlert({content:'这是一个内嵌在页面的提示信息', type:'danger'})
			})
			.on('click', '._clickMask', function() {
				 new d().alert({type: 'success', content: '这是一个简单的模拟alert有mask的提示', hasMask: true})
			})
	});
	
})