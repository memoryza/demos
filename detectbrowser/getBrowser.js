/**
* 正则摘自jq源码
*/
function getUA(ua) {
	ua = ua.toLowerCase();

	var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
		/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
		/(msie) ([\w.]+)/.exec( ua ) ||
		ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
		[];
	if(ua.match(/lumia/)) {
		match[1] = 'lumia ' + match[1]
	}
	if(ua.match(/android/)) {
		match[1] = 'android ' + match[1]
	}
	if(ua.match(/iphone/)) {
		match[1] = 'iphone ' + match[1]
	}
	if(ua.match(/ipad/)) {
		match[1] = 'iphone ' + match[1]
	}
	return {
		browser: match[ 1 ] || "",
		version: match[ 2 ] || "0"
	};
}