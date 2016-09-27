# Jinfinity Joomla Extensions

## What is Jinfinity?

Jinfinity has operated for 4 years now and throughout this time we have released professional Joomla extensions that had previously only been available to our own commercial clients. We are so grateful to each and every one of our customers who have helped support and enable the continual refinement of these extensions. It has truly been a joy to see our extensions put to good use by the broader Joomla community. Through this support we have been able to fulfil our commitment to develop world class Joomla extensions — a mission that we started out with so many years ago.

Over the years both myself and Norm have had other interests competing for our time and this has resulted in a decreased level of support. This is something we're not proud of and a situation we never expected find ourselves placed in. Our other ambitions, both personal and professional, have taken priority and as a result we cannot allow Jinfinity to continue commercially.

Jinfinity is shutdown as of 27 September 2016. Our extensions are published on GitHub and made freely available in the hope that crowd sourced development and support may continue. This decision was not an easy one to make and we thank you for taking the journey with us in extending Joomla as a platform for open source web development.

This journey wasn't always easy for us as there were plenty of challenges along the way especially during the early development. Looking back now we choose to relish in the good times and hope to see the legacy of the Jinfinity community and extensions continue on without our official support.

## What is this?

This is the source code used to develop Joomla extensions by Jinfinity. It has everything you need to continue with development including the extension server and extension manager used for extension deployment.

## How to use it?

These instructions give the most direct path to a working Jinfinity
development environment. Options for doing things differently are
not discussed below.

### System Requirements

macOS, Windows, Ubuntu Linux LTS, and the latest Ubuntu Linux release are the current
supported host development operating systems.

### Source

The source code is split across 3 seperate Joomla core codebases and must be built to create a package ready for installation. Developing code adjacent to the Joomla core means testing can begin immediately and bugs are found sooner with each change made.

### Build from source
**Via PHP server**

1. Clone this repo:

	````
	git clone https://github.com/antonwintergerst/jinfinity.git
	````

2. Download MAMP https://www.mamp.info/en/ (or equivalent PHP server).
3. Set server root to repo root.
4. Go to [http://localhost:8080/makefile.php](http://localhost:8080/makefile.php)
5. Use web interface to build.

### Extension packages

Built packages will be placed into an Archives directory and at this point are ready to be installed using the Joomla installer.

## How to contribute?

See CONTRIBUTING.md

## License

GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html