<?php
App::import('Vendor', 'Jess.jessc/jessc');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('Component', 'Controller');

class JessComponent extends Component {

/**
 * LESS folders to compile
 */
	private $__jessFolders = array();

/**
 * CSS folders where compiled LESS files will be placed
 */
	private $__jsFolders = array();

/**
 * Initialisation logic. Sets the options
 *
 * @author Stef van den Ham
 *
 * @param {Controller} $controller
 *
 * @return void
 * */
	public function initialize(Controller $controller) {
		$rootJessDir = ROOT . DS . APP_DIR . DS . 'jess';

		if (!is_dir($rootJessDir)) {
			mkdir($rootJessDir);
		}

		$this->__jessFolders['default'] = new Folder($rootJessDir);
		$this->__jsFolders['default'] = new Folder(ROOT . DS . APP_DIR . DS . 'webroot' . DS . 'js');

		$Folder = new Folder(ROOT . DS . APP_DIR . DS . 'View' . DS . 'Themed');
		list($themes, $files) = $Folder->read();

		foreach ($themes as $theme) {
			$jessDir = ROOT . DS . APP_DIR . DS . 'View' . DS . 'Themed' . DS . $theme . DS . 'jess';
			$jsDir = ROOT . DS . APP_DIR . DS . 'View' . DS . 'Themed' . DS . $theme . DS . 'webroot' . DS . 'js';
			if ($theme != '.svn' && is_dir($jessDir) && is_dir($jsDir)) {
				$this->__jessFolders[$theme] = new Folder($jessDir);
				$this->__jsFolders[$theme] = new Folder($jsDir);
			}
		}

		$Folder = new Folder(ROOT . DS . APP_DIR . DS . 'Plugin');
		list($plugins, $files) = $Folder->read();

		foreach ($plugins as $plugin) {
			$jessDir = ROOT . DS . APP_DIR . DS . 'Plugin' . DS . $plugin . DS . 'jess';
			$jsDir = ROOT . DS . APP_DIR . DS . 'Plugin' . DS . $plugin . DS . 'webroot' . DS . 'js';
			if ($plugin != '.svn' && is_dir($jessDir) && is_dir($jsDir)) {

				$this->__jessFolders[$plugin] = new Folder($jessDir);
				$this->__jsFolders[$plugin] = new Folder($jsDir);
			}
		}
	}

/**
 * Main conversion
 *
 * @author Stef van den Ham
 *
 * @param {Controller} $controller
 *
 * @return void
 */
	public function beforeRender(Controller $controller) {
		$this->generateJs();
	}

/**
 * Auto compile less
 *
 * This method auto compiles less files according to compile state and updating time
 *
 * @author Marc-Jan Barnhoorn
 *
 * @param {String} $inputFile LESS file path
 * @param {String} $outputFile CSS output file path
 *
 * @return void
 */
	private function __autoCompileJess($inputFile, $outputFile) {
		$cacheFile = $inputFile . ".cache";

		if (file_exists($cacheFile)) {
			$cache = unserialize(file_get_contents($cacheFile));
		} else {
			$cache = $inputFile;
		}

		$jess = new JessCompiler();
		$newCache = $jess->cachedCompile($cache);

		if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
			file_put_contents($cacheFile, serialize($newCache));
			file_put_contents($outputFile, $newCache['compiled']);

			return true;
		}
	}

	private function __stripComments($string) {
		return preg_replace('#\s*//.*#', "", preg_replace('#/\*[^*]*\*+([^/][^*]*\*+)*/#', '', $string));
	}

/**
 * Clean the generated CSS files
 *
 * @author Marc-Jan Barnhoorn
 *
 * @return String[] Array of paths we have removed
 */
	public function cleanGeneratedCss() {
		//Cleaned files that we will return
		$cleanedFiles = array();
		foreach ($this->__jessFolders as $key => $jessFolder) {
			foreach ($jessFolder->find() as $file) {
				$file = new File($file);

				if ($file->ext() == 'jess' && substr($file->name, 0, 2) !== '._') {
					$jessFile = $jessFolder->path . DS . $file->name;
					$jsFile = $this->__jsFolders[$key]->path . DS . str_replace('.jess', '.js', $file->name);
					if (file_exists($jsFile)) {
						unlink($jsFile);
						$cleanedFiles[] = $jsFile;
					}

					if (file_exists($jessFile . '.cache')) {
						unlink($jessFile . '.cache');
						$cleanedFiles[] = $jessFile . '.cache';
					}
				}
			}
		}

		return $cleanedFiles;
	}

/**
 * Generate the CSS from all the LESS files we can find
 *
 * @return String[] Generated CSS files
 */
	public function generateJs() {
		$generatedFiles = array();

		if (Configure::read('debug') > 0) {
			foreach ($this->__jessFolders as $key => $jessFolder) {
				foreach ($jessFolder->find() as $file) {
					$file = new File($file);
					if ($file->ext() == 'jess' && substr($file->name, 0, 2) !== '._') {
						$jessFile = $jessFolder->path . DS . $file->name;
						$jsFile = $this->__jsFolders[$key]->path . DS . str_replace('.jess', '.js', $file->name);

						if ($this->__autoCompileJess($jessFile, $jsFile)) {
							$generatedFiles[] = $jsFile;
						}
					}
				}
			}
		}
		return $generatedFiles;
	}
}