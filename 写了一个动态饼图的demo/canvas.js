function pieChart(opt) {
    if (this instanceof pieChart) {
        if (!opt || !opt.id) return;
        this.init(opt);
    } else {
        new pieChart();
    }
}
pieChart.prototype = {
    conf: {
        width: 362, // 画布宽度
        height: 288, // 画布高度
        bgcolor: '#f9fafe', // 画布颜色
        animate: true,
        title: {
            text: '',// 主标题
            subtext: '',// 次标题
            x: 20, // 标题X坐标
            y: 30, // 标题Y坐标
            fontSize: 24, //标题字体大小
            fontFamliy : 'Arial',
            fontColor: '#000000', // 主标题颜色
            subtextColor: '#cccccc' // 次标题颜色
        },
        colors: ['#ff1a47', '#00e29f', '#00c964', '#ff792e', '#7bc0e0'],
        unit: {
            names: [],// 数值单位
            scales: [] // 数值单位刻度
        },
        // 画布外心圆形
        outercircle:{
            radius: 0,
            color: '#cccccc'
        },
        // 画布内心圆
        innercircle: {
            radius: 0,
            opacity: 0.35,
            name: '',
            fontSize: 11,
            color: '#ffffff',
            fontFamliy: 'Arial'
        },
        // 数据
        series: {
            radius: 60,//饼图半径
            decimal: 2,
            angle: 0,
            data: []
        }
    },
    getNumber: function (number, decimal) {
        return (Math.floor((number + 5 / Math.pow(10, decimal + 1))
                * Math.pow(10, decimal)) / Math.pow(10, decimal)).toFixed(decimal)
    },
    init: function (opt) {
        if(opt.id instanceof $) {
           this.canvas = opt.id[0];
        } else {
           this.canvas = document.getElementById(opt.id);
        }
        this.ctx = this.canvas.getContext("2d");
        this.getConfig(opt);
        this.retinaScale();
        this.coord = {
            x: this.width / 2,
            y: this.height / 2
        };
        this.calculate();
        this.paint();
    },
    // 合并配置
    getConfig: function(opt) {
        for (var i in this.conf) {
            if (i && opt.hasOwnProperty(i) && this.conf.hasOwnProperty(i)
                && $.isPlainObject(opt[i])) {
                this.conf[i] = $.extend({}, this.conf[i], opt[i]);
            } else if(opt[i] !== undefined){
                this.conf[i] = opt[i];
            }
        }
    },
    // 重新设置缩放比例
    retinaScale : function() {
        var scaleRatio = Math.min(window.devicePixelRatio, 2);
        this.scaleRatio = scaleRatio = isNaN(scaleRatio) ? 1 : scaleRatio;
        if (scaleRatio === 1) {
            this.width = this.canvas.width = this.conf.width;
            this.height = this.canvas.height = this.conf.height;
        } else {
            this.width = this.canvas.width = this.conf.width * scaleRatio;
            this.height = this.canvas.height = this.conf.height * scaleRatio;
            this.ctx.scale(scaleRatio, scaleRatio);
        }
    },
    // 开始回执
    paint: function () {
        var t = this;
        // 绘制背景色
        t.fillRect(0, 0, t.width, t.height);
        var conf = t.conf;
        if (conf.title) {
            t.paintTitle();
            if(conf.title.subtext) {
                t.paintSubTitle();
            }
        }

        var stepRadius = 0.5 * conf.series.radius;
        t.maxRadius = Math.max(conf.outercircle.radius, conf.series.radius) * 1.1;
        var clearX = t.coord.x - t.maxRadius;
        var clearY = t.coord.y - t.maxRadius;
        var step = 1.5; // 步长
        if (t.conf.animate) {
            function pieBigAnimate() {
                var timer = setTimeout(function () {
                    if (stepRadius > t.maxRadius) {
                        clearTimeout(timer);
                        pieSmallAnimate();
                    } else {
                        stepRadius += step;
                        t.paintPie(stepRadius, clearX, clearY);
                        pieBigAnimate();
                    }
                }, 1000 / 60);
            }
            function pieSmallAnimate() {
                var timer = setTimeout(function () {
                    if (stepRadius <= t.conf.series.radius ) {
                        clearTimeout(timer);
                        t.paintPie(t.conf.series.radius, clearX, clearY);
                    } else {
                        stepRadius -= step;
                        t.paintPie(stepRadius, clearX, clearY);
                        pieSmallAnimate();
                    }
                }, 20);
            }
            pieBigAnimate();

            t.stepAngle = 1;
            // 算运动次数
            t.totalStep = parseInt((t.maxRadius - stepRadius) / step + (t.maxRadius - t.conf.series.radius) / step);
            function angleAnimate() {
                var timer = setTimeout(function () {
                    if (t.stepAngle > t.totalStep) {
                        clearTimeout(t.stepAngle);
                        t.stepAngle = t.totalStep;
                    } else {
                        t.stepAngle++;
                        angleAnimate();
                    }
                }, 1000 / 60);
            }
            angleAnimate();
        } else {
            t.paintPie(conf.series.radius, clearX, clearY);
        }
    },
    paintPie: function(radius, clearX, clearY) {
        var conf = this.conf;
        this.fillRect(clearX - 1, clearY - 1, 2 * this.maxRadius + 2, 2 * this.maxRadius + 2);
        if (conf.outercircle.radius) {
            this.paintOuterCicle(radius * conf.outercircle.radius / conf.series.radius);
        }
        if (conf.series.data.length) {
            this.paintCircle(radius);
        }
        if (conf.innercircle && conf.innercircle.name) {
            this.paintInnerCircle(radius * conf.innercircle.radius / conf.series.radius);
        }
        // 防止画内心圆的时候将ctx的透明更改
        this.ctx.globalAlpha = 1;
    },
    // 画背景
    fillRect: function (x, y, width, height) {
        this.ctx.fillStyle = this.conf.bgcolor;
        this.ctx.fillRect(x, y, width, height);
    },
    paintCircle: function (radius) {
        var ctx = this.ctx;
        var series = this.conf.series;
        radius = radius > this.maxRadius ? this.maxRadius : radius;

        var startPos = series.angle;
        var angle = 0;
        var drawData = this.conf.drawData;
        var colorsLen = this.conf.colors.length;
        for (var i = 0, _len = drawData.length; i < _len; i++) {
            ctx.beginPath();
            ctx.moveTo(this.coord.x, this.coord.y);
            angle = drawData[i].angle * (this.stepAngle/this.totalStep);
            ctx.arc(this.coord.x, this.coord.y, radius, startPos, startPos + angle);
            ctx.fillStyle = (drawData[i].color ? drawData[i].color : this.conf.colors[i % colorsLen]);
            startPos += angle;
            ctx.fill();
        }
    },
    calculate: function () {
        var series = this.conf.series;
        var data = series.data;
        var effectiveData = [];
        var total = 0;
        var calculateData = [];
        var tmpVal;
        var maxFlag = 0;
        var minFlag = 0;
        var maxValue = 0;
        var minValue = 0;
        var totalPercent = 0;
        for (var i = 0, _len = data.length; i< _len; i++) {
            tmpVal = parseInt(data[i].value);
            if (!isNaN(tmpVal)) {
                total += tmpVal;
                effectiveData.push(data[i]);
            }
        }
        for (var j = 0, _len = effectiveData.length; j < _len; j++) {
            if (j === 0) {
                maxValue = minValue = effectiveData[0].value;
            } else {
                if (effectiveData[j].value > maxValue) {
                    maxValue = effectiveData[j].value;
                    maxFlag = j;
                }
                if (minValue > effectiveData[j].value) {
                    minValue = effectiveData[j].value;
                    minFlag = j;
                }
            }
            var num = +this.getNumber(effectiveData[j].value / total * 100, series.decimal);
            totalPercent = totalPercent + num;
            effectiveData[j]['percentage'] = num;
            effectiveData[j]['angle'] = num * Math.PI * 2 / 100;
        }
        // 数据矫正
        if (totalPercent > 100) {
            effectiveData[maxFlag]['percentage'] = effectiveData[maxFlag]['percentage'] - totalPercent + 100;
            effectiveData[maxFlag]['angle'] = effectiveData[maxFlag]['percentage'] * Math.PI * 2 / 100;
        } else if (totalPercent < 100) {
            effectiveData[minFlag]['percentage'] = effectiveData[minFlag]['percentage'] - totalPercent + 100;
            effectiveData[minFlag]['angle'] = effectiveData[minFlag]['percentage'] * Math.PI * 2 / 100;
        }
        this.conf.drawData = effectiveData;
    },
    // 画外心圆
    paintOuterCicle: function(radius) {
        var conf = this.conf.outercircle;
        var ctx = this.ctx;
        radius = radius > this.maxRadius ? this.maxRadius : radius;
        ctx.beginPath();
        ctx.arc(this.coord.x, this.coord.y, radius, 0, Math.PI * 2);
        ctx.strokeStyle = conf.color;
        ctx.stroke();
    },
    // 画内心圆
    paintInnerCircle: function (radius) {
        var conf = this.conf.innercircle;
        var ctx = this.ctx;
        radius = radius > this.maxRadius ? this.maxRadius : radius;
        var names = [];
        var index = 0;
        if (conf.name.length > 3) {
            index = parseInt(conf.name.length / 2);
            names.push(conf.name.substr(0, index));
            names.push(conf.name.substr(index));
        } else {
            names.push(conf.name);
        }
        var params = {
            font: conf.fontSize + 'px ' + conf.fontFamliy,
            fillStyle: conf.color,
            x: this.coord.x,
            textAlign: 'center',
            textBaseline: 'middle'
        };
        if (names.length == 1) {
            params.text = names[0];
            params.y = this,coord.y;
            this.paintText(params);
        } else {
            params.text = names[0];
            params.y = this.coord.y - 2 - conf.fontSize / 2;
            this.paintText(params);

            params.text = names[1];
            params.y = this.coord.y + 2 + conf.fontSize / 2;
            this.paintText(params);
        }
        
        ctx.beginPath();
        ctx.arc(this.coord.x, this.coord.y, radius, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.globalAlpha = conf.opacity;
        ctx.fill();
    },
    paintText: function (params) {
        var ctx = this.ctx;
        ctx.font = params.font;
        ctx.fillStyle = params.fillStyle;
        ctx.textAlign = params.textAlign || 'left';
        ctx.textBaseline = params.textBaseline || 'bottom';
        ctx.fillText(params.text, params.x, params.y);
        ctx.restore();
    },
    paintTitle: function () {
        var title = this.conf.title;
        var params = {
            text: title.text,
            font: title.fontSize + 'px ' + title.fontFamliy,
            fillStyle: title.fontColor,
            x: title.x,
            y: title.y
        };
        this.paintText(params);
    },
    paintSubTitle: function () {
        var title = this.conf.title;
        var x = title.x + title.text.length * title.fontSize + 10;
        var params = {
            text: title.subtext,
            font: title.fontSize + 'px ' + title.fontFamliy,
            fillStyle: title.subtextColor,
            x: x,
            y: title.y
        };
        this.paintText(params);
    }
}