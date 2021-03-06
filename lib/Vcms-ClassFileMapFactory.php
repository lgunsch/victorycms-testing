<?php
/**
 * VictoryCMS - ClassFileMapFactory
 *
 * @filesource
 * @category  VictoryCMS
 * @package   Testing
 * @author    A.J. Brown
 * @see       http://ajbrown.org/blog/2008/12/02/an-auto-loader-using-php-tokenizer.html
 */

namespace Vcms;

/**
 * Class to handle generating the class file map, and loading of classes
 *
 * @package Testing
 */
abstract class ClassFileMapFactory
{
	/**
	 * Generates a class file map instance for the specified class path
	 *
	 * @param string $sClassPath the path to analyze
	 * @param string $sName      the name for this class file map
	 *
	 * @return ClassFileMap
	 */
	public static function generate($sClassPath, $sName = null)
	{
		$aClassMap = static::_getClassFileMapArray($sClassPath, true);
		$oClassfileMap = new ClassFileMap($sName);
		$oClassfileMap->setClassPath($aClassMap);

		return $oClassfileMap;
	}

	/**
	 * Generates a class file map for the specified directory
	 *
	 * @param string  $sDirectory directry path.
	 * @param boolean $bRecursive true if directory should be recursively searched.
	 *
	 * @return array
	 */
	private static function _getClassFileMapArray($sDirectory, $bRecursive = true)
	{
		if (! is_dir($sDirectory)) {
			throw new AppException('The specified location is not a directory');
		}

		if (! is_readable($sDirectory)) {
			throw new AppException('The path `'.$sDirectory.'` is not readable');
		}

		$sPath = realpath($sDirectory);

		$oFiles = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($sPath),
			$bRecursive ? \RecursiveIteratorIterator::SELF_FIRST : null
		);

		$sHiddenFiles = '/\/\.\w+/';

		$aDeclarations = array();

		/*
		 * Load the list of files in the directory
		 */
		foreach ($oFiles as $sName => $aFile) {
			$extension = strtolower(substr($sName, strlen($sName) - 3));
			if (! preg_match($sHiddenFiles, $sName) && !$aFile->isDir()
				&& strcmp($extension, 'php') == 0
			) {
				$oFile = $aFile->openFile();

				$sContents = null;
				while (! $oFile->eof()) {
					$sContents .= $oFile->fgets();
				}

				/*
				 * Tokenize the source and grab the classes
				 * and interfaces
				 */
				$fileNamespace = '';
				$aTokens = token_get_all($sContents);
				$iNumtokens = count($aTokens);
				for ($i=0; $i < $iNumtokens; $i++) {
					switch ($aTokens[$i][0]) {
						case T_NAMESPACE:
							/*
							 * Namespaces are required to be the first line of
							 * code if they are used, so this will set the current
							 * files $fileNamespace before any T_CLASS or
							 * T_INTERFACE is declared. This will assemble the
							 * declared namespace to be concatenated with the key
							 * for looking up the class in the FileMap.
							 */
							$i += 2;
							$fileNamespace = $aTokens[$i][1];
							$i++;
							while (($aTokens[$i][0] === T_NS_SEPARATOR
									|| $aTokens[$i][0] === T_STRING)
									&& $i < $iNumtokens) {
								 $fileNamespace .= $aTokens[$i][1];
								 $i++;
							}
							// add trailing \ if not present
							if (strcmp($fileNamespace[strlen($fileNamespace) - 1], '\\') !== 0) {
								$fileNamespace .= '\\';
							}
							break;
						case T_CLASS:
						case T_INTERFACE:
							$i += 2; //skip the whitespace token
							if (isset($aTokens[$i][1])) {
								$aDeclarations["{$fileNamespace}{$aTokens[$i][1]}"] = $sName;
							}
							break;
						default:
							/* We do nothing by default */
							break;
					}
				}
			}
		}
		return $aDeclarations;
	}
}