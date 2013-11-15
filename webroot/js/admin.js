Assets = {}

Assets.popup = function(e) {
	var url = e.currentTarget.attributes['href'].value;
	var options = 'menubar=no,resizable=yes,height=600,width=800,chrome=yes,centerScreen=yes,scrollbars=yes';
	this.win = window.open(url, 'Asset Browser', options).focus();
	e.preventDefault();
	return false;
}

$(function() {
	$('body').on('click', 'a[rel=browse]', Assets.popup);
});
