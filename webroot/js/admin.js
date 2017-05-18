var Assets = {};

Assets.reloadAssetsTab = function(e) {
	e && e.preventDefault();
	var $tab = $('a[data-toggle="tab"][href$="-assets"]');
	var url = $('.asset-list').data('url');
	var loadingMsg = '<span><i class="fa fa-spin fa-spinner"></i> Loading. Please wait...</span>';
	$tab.tab('show');
	$($tab.attr('href'))
		.html(loadingMsg)
		.load(url);
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
	var type = 'FeaturedImage';

	e && e.preventDefault();

	var curValue = $target.val();
	var postData = {
		pk: $target.data('pk'),
		value: curValue,
	};
	$.post($target.data('url'), postData, function(data, textStatus) {
		$target.select2('destroy');
		if (curValue) {
			$target.html('<option value="' + curValue + '">' + curValue + '</option>')
		} else {
			$target.html('');
		}
		$target.select2({placeholder: {id: '', text: ''}})
	});
	return false;
};

Assets.setFeaturedImage = function(e) {
	var $target = $(e.currentTarget)
	var pk = $target.data('pk');
	var curValue = 'FeaturedImage';
	var $select = $('.change-usage-type[data-pk=' + pk + ']');
	$select
		.select2('destroy')
		.html('<option value="' + curValue + '">' + curValue + '</option>')
		.val(curValue)
		.change()
		.select2({placeholder: {id: '', text: ''}})

	e && e.preventDefault();
	return false;
}

Assets.unregisterAssetUsage = function(e) {
	e && e.preventDefault();
	var $target = $(e.currentTarget);
	var postData = {
		id: $target.data('id')
	};
	$('.tooltip').tooltip('hide');
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
	var $body = $('body');
	$body.on('click', 'a[data-toggle=browse]', Assets.popup);
	$body.on('click', 'a[data-toggle=refresh]', Assets.reloadAssetsTab);
	$body.on('click', 'a[data-toggle=resize-asset]', Assets.resizeAsset);
	$body.on('change', '.change-usage-type', Assets.changeUsageType);
	$body.on('click', 'a.unregister-usage', Assets.unregisterAssetUsage);
	$body.on('click', 'a.set-featured-image', Assets.setFeaturedImage);
});
