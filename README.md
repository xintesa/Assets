# Installation via git

	cd $APP

	git clone http://github.com/rchavik/AdminExtras View/Themed/AdminExtras

	git clone http://github.com/CakeDC/Imagine Plugin/Imagine
	( cd Plugin/Imagine && git submodule update --init --recursive )

	git clone http://github.com/xintesa/Assets Plugin/Assets
	( cd Plugin/Assets && git submodule update --init --recursive )

	Console/cake settings.settings write Site.admin_theme AdminExtras
	Console/cake ext activate plugin Assets

## Usage

Once activated successfully, three new buttons (Reload, Browse, and Upload)
and a new "Assets" tab will be added to the `edit` action of the following
pages: Nodes, Blocks, and Types.

Clicking on the "Upload" button will bring up a popup window, where you can
select and upload the asset file.

### Featured Image

When ticking the "Featured Image" checkbox, the asset will be automatically
grouped under the "Featured Image" record.

A simple helper method (AssetsImageHelper::featured) is included for basic
markup generation.

### Adapter

Two adapters are provided in the plugin:

	* Local Attachment

	  Default storage adapter.  When using this adapter, assets will be
	  stored under `APP/webroot/assets` directory.

	* Local Attachment (Legacy)

	  This adapters mimics the original Croogo behavior, where images are
	  stored under `APP/webroot/uploads` directory.

# Dependencies

- [Imagine](https://github.com/CakeDC/Imagine) plugin which is a CakePHP
  friendly wrapper for [Imagine image processing library](http://imagine.readthedocs.org/).
  Tested with 1.0.1-3-g500a559
- [Gaufrette](https://github.com/Knplabs/Gaufrette) library
- [AdminExtras](http://github.com/rchavik/AdminExtras) theme
