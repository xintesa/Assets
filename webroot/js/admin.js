Assets = {}

Assets.popup = function(e) {
	var width = 800;
	var height = 600;
	var screenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	var screenTop = window.screenTop != undefined ? window.screenTop : screen.top;
	var left = (screen.width / 2) - (width / 2) + screenLeft;
	var top = (screen.height / 4) - (height / 4) + screenTop;
	var url = e.currentTarget.attributes['href'].value;
	var options = 'menubar=no,resizable=yes,chrome=yes,centerScreen=yes,scrollbars=yes' +
		',top=' + top + ',left=' + left +
		',width=' + width + ',height=' + height;
	window.open(url, 'Asset Browser', options).focus();
	e.preventDefault();
	return false;
}

$(function() {
	$('body').on('click', 'a[rel=browse]', Assets.popup);
});
