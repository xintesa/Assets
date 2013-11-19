# Installation via git

	git clone http://github.com/rchavik/Assets Plugin/Assets
	( cd Assets && git submodule update --init )
	Console/cake schema create -p Assets
	Console/cake ext activate plugin Assets

# Dependencies

- [Gaufrette](https://github.com/Knplabs/Gaufrette)
- [AdminExtras](http://github.com/rchavik/AdminExtras) theme
