# CakePHP JESS Component

A CakePHP JESS component to (automatically) compile jess-files by using jessc.php (https://github.com/BradCrumb/jessphp).

## Requirements

The master branch has the following requirements:

* CakePHP 2.2.0 or greater.
* PHP 5.3.0 or greater.

## Installation

* Clone/Copy the files in this directory into `app/Plugin/Jess`
* Ensure the plugin is loaded in `app/Config/bootstrap.php` by calling `CakePlugin::load('Jess');`
* Include the toolbar component in your `AppController.php`:
   * `public $components = array('Jess.Jess');`

## Documentation

The component will check for jess-files to (re)compile automatically when:
 * Debug level is > 0
 * Cache-time expires

All jess-files should be placed in the `app/jess` directory (to generate js-files in the default `webroot/js` directory).
Jess-files for the plugin and themes should be stored in `app/Plugin/{pluginname}/jess` or `app/View/Themed/{themename/jess`.

## License
GNU General Public License, version 3 (GPL-3.0)
http://opensource.org/licenses/GPL-3.0