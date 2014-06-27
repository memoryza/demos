/**
* 淡入淡出效果插件
* author:memoryza(jincai.wang@foxmail.com)
**/
	
	function fadePlugin(s) {
		var dSetting = {
			container: null,
			timer: 3000, 
			callback: null,
			innerWidth: $(window).width()
		}
		s = $.extend({}, dSetting,s);
		this.container = s.container;
		this.timer = s.timer;
		this.callback = s.callback;
		this.innerWidth = s.innerWidth;
		this.indexId = 0;
		this.scrollLen = this.container.find('li').length;
		this.browserKit = ['-moz-', '', '-webkit-'];
		this.init();		
		this.bindEvent();
	}
	fadePlugin.prototype.init = function() {
		var self = this;
		$(this.container).find('li').each(function(index) {
			for(var i = 0, _len = self.browserKit.length; i < _len; i++) {
				$(this).css(self.browserKit[i] + 'transform', 'translate3d(' + index * self.innerWidth + 'px, 0 ,0)');
			}
		});
		typeof self.callback == 'function' ? self.callback(self.indexId) : '';		
		//定时器
		self.timerTicket(); 
	}
	fadePlugin.prototype.reInit = function(s) {
		this.innerWidth = s.innerWidth;
		var self = this;
		$(this.container).find('li').each(function(index) {
			for(var i = 0, _len = self.browserKit.length; i < _len; i++) {
				$(this).css(self.browserKit[i] + 'transform', 'translate3d(' + (index - self.indexId) * self.innerWidth + 'px, 0 ,0)');
			}
		});
	}
	fadePlugin.prototype.timerTicket = function() {
		var self = this;
		self.timerTick = setTimeout(function() {
			self.go(1);
			self.timerTicket();
		}, self.timer);
	}
	fadePlugin.prototype.bindEvent = function() {
		var self = this;

		self.startTime = 0;
		var startHandler =  function(ev) {
			clearTimeout(self.timerTick);
			self.startX = ev.touches[0].pageX;
			self.startTime = new Date() * 1;
			self.moveOffsetx = 0;
			self.startY = ev.touches[0].pageY; 
			self.moveOffsetx = 0; 
		}
		var moveHandler = function(ev) {
			var moveX = ev.touches[0].pageX;
			var moveY = ev.touches[0].pageY;
			var changeS = self.indexId - 1;
			var changeE = self.indexId + 2;
			self.moveOffsetx = moveX - self.startX;
			if(Math.abs(moveY - self.startY) > Math.abs(self.moveOffsetx)) {
				return;
			}
			for(;changeS < changeE;  changeS++) {
				for(var i = 0, _len = self.browserKit.length; i < _len; i++) {
					$(self.container).find('li').get(changeS) && $($(self.container).find('li').get(changeS)).css(self.browserKit[i] + 'transform', 'translate3d(' + ((changeS - self.indexId)* self.innerWidth + self.moveOffsetx) + 'px, 0 ,0)');
				}
			}					
			ev.preventDefault();
		}
		var endHandler = function(ev) {

			var boundary = self.innerWidth / 6;
			var endTime = new Date() * 1;
			var endY = ev.changedTouches[0].pageY;
			var endX = ev.changedTouches[0].pageX;
			if(Math.abs(endY - self.startY) > Math.abs(endX - self.startX)) {
				return;
			}			
			if(endTime - self.startTime > 800) {
				if(self.moveOffsetx >= boundary) {
					//上一张
					self.go(-1);
				} else if(self.moveOffsetx <= -boundary) {
					//下一张
					self.go(1);
				} else if(self.moveOffsetx >= -20 &&  self.moveOffsetx <= 20) {	
					var href = $($(self.container).find('li').get(self.indexId)).attr('href');
					if(href) {
						location.href = href;
					}
					self.go(0);
				} else {					
					self.go(0);
				}
			} else {
				if(self.moveOffsetx >= 50) {
					//上一张
					self.go(-1);
				} else if(self.moveOffsetx <= -50) {
					//下一张
					self.go(1);
				} else if(self.moveOffsetx >= -20 &&  self.moveOffsetx <= 20 && (endTime - self.startTime > 100)) {
					var href = $($(self.container).find('li').get(self.indexId)).attr('href');
					if(href) {
						location.href = href;
					}
					self.go(0);
				} else {
					//不动					
					self.go(0);
				}
			}
			if(self.timerTick) {
				clearTimeout(self.timerTick);
				delete self.timerTick;
				//定时器
				self.timerTicket();
			}		
			ev.preventDefault();	
		}
		
		$(document)
			.on('touchstart', '#fadeContainer', startHandler )
			.on('touchmove', '#fadeContainer', moveHandler)
			.on('touchend', '#fadeContainer', endHandler)
	}
	fadePlugin.prototype.getIndexId = function() {
		this.indexId =  this.indexId % this.scrollLen;
	}

	fadePlugin.prototype.stop =  function(callback) {
		// this.indexId = 0;
		// $('#fadeContainer').off('touchstart').off('touchmove').off('touchend');
		// $($('#shakePoint li').removeClass('active').get(0)).addClass('active');
		// clearTimeout(this.timerTick);
		typeof callback == 'function' ? callback() : '';
	}
	fadePlugin.prototype.go = function(flag) {
		var self = this;
		self.indexId = self.indexId + flag;
		self.getIndexId();
		var changeS = self.indexId - 1;
		var changeE = self.indexId + 2;
		for(;changeS < changeE;  changeS++) {
			for(var i = 0, _len = self.browserKit.length; i < _len; i++) {
				$(self.container).find('li').get(changeS) && $($(self.container).find('li').get(changeS)).css(self.browserKit[i] + 'transform', 'translate3d(' + (changeS - self.indexId)* self.innerWidth + 'px, 0 ,0)');
			}
		}
		typeof self.callback == 'function' ? self.callback(self.indexId) : '';
	}
