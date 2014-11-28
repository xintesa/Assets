var Assets = {};

Assets.reloadAssetsTab = function(e) {
	e && e.preventDefault();
	var $tab = $('a[data-toggle="tab"][href$="-assets"]');
	var url = $('.asset-list').data('url');
	$tab.tab('show');
	$($tab.attr('href')).load(url);
	return false;
};

Assets.popup = function(e) {
	e && e.preventDefault();
	var width = window.screen.width > 1024 ? 1024 : 800;
	var height = 600;
	var screenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	var screenTop = window.screenTop != undefined ? window.screenTop : screen.top;
	var left = (screen.width / 2) - (width / 2) + screenLeft;
	var top = (screen.height / 4) - (height / 4) + screenTop;
	var url = e.currentTarget.attributes['href'].value;
	var options = 'menubar=no,resizable=yes,chrome=yes,centerScreen=yes,scrollbars=yes' +
		',top=' + top + ',left=' + left +
		',width=' + width + ',height=' + height;
	var $tab = $('a[data-toggle="tab"][href$="-assets"]').tab('show');
	window.open(url, 'Asset Browser', options).focus();
	return false;
};

Assets.changeUsageType = function(e) {
	var $target = $(e.currentTarget);
	var $editable = $target.parents('tr').find('.usage-type.editable');
	var type = 'FeaturedImage';
	e && e.preventDefault();
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
};

Assets.unregisterAssetUsage = function(e) {
	e && e.preventDefault();
	var $target = $(e.currentTarget);
	var postData = {
		id: $target.data('id')
	};
	$.post($target.attr('href'), postData, function(data, textStatus) {
		if (data == true) {
			$target.parents('tr').hide('medium', function() {
				$(this).remove();
			});
		}
	});
	return false;
};

Assets.resizeAsset = function(e) {
	e && e.preventDefault();

	var width = parseInt(prompt('Resize to width: '));
	if (isNaN(width)) {
		return alert('Invalid number');
	}

	var $target = $(e.currentTarget);
	var postData = {
		width: width
	};
	$.post($target.attr('href'), postData, function(data, textStatus) {
		if (textStatus === 'success') {
			if (typeof data === 'string') {
				return alert(data);
			}
			return prompt("Asset id: "+ data.AssetsAsset.id + " created", data.AssetsAsset.path);
		}
	});
	return false;
}

$(function() {
	$('body').on('click', 'a[data-toggle=browse]', Assets.popup);
	$('body').on('click', 'a[data-toggle=refresh]', Assets.reloadAssetsTab);
	$('body').on('click', 'a[data-toggle=resize-asset]', Assets.resizeAsset);
	$('body').on('click', 'a.change-usage-type', Assets.changeUsageType);
	$('body').on('click', 'a.unregister-usage', Assets.unregisterAssetUsage);
});
