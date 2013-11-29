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

Assets.changeUsageType = function(e) {
	var $target = $(e.currentTarget);
	var $editable = $target.parents('tr').find('.usage-type.editable');
	var type = 'FeaturedImage';
	e.preventDefault();
	var curValue = $editable.editable('getValue');
	if (curValue.type !== '') {
		return alert('Type already set');
	}
	var postData = {
		pk: $target.data('pk'),
		value: $target.data('value')
	};
	$.post($target.attr('href'), postData, function(data, textStatus) {
		$target.parents('tr').find('.usage-type.editable').editable('setValue', type, type);
	});
	return false;
}

$(function() {
	$('body').on('click', 'a[data-toggle=browse]', Assets.popup);
	$('body').on('click', 'a.change-usage-type', Assets.changeUsageType);
});
