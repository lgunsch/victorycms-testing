<?php
namespace Vcms;

/**
 * Autoloads classes using class file maps
 *
 * @author A.J. Brown
 * @package com.hypermuttlabs
 * @subpackage packaging
 *
 */
class ClassFileMapAutoloader
{
	private $_aClassFileMaps = array();

	/**
	 * Adds a class file map for use by this autoloader.  ClassFileMaps are grouped
	 * by their name if the second parameter is true, resulting in a second
	 * class file with the same name overwriting the first.
	 *
	 * @param ClassFileMap $oClassFileMap
	 * @param bool $bUseName use the value of {@link ClassFileMap::getName()}
	 *  as the key
	 *
	 * @return void
	 */
	public function addClassFileMap(ClassFileMap $oClassFileMap, $bUseName = true)
	{
		if ($bUseName) {
			$this->_aClassFileMaps[$oClassFileMap->getName()] = $oClassFileMap;
		} else {
			$this->_aClassFileMaps[] = $oClassFileMap;
		}
	}

	/**
	 * Registers this class with the spl autloader stack.
	 *
	 * @return bool
	 */
	public function registerAutoload()
	{
		return spl_autoload_register(array(&$this, 'autoload')); 
	}

	/**
	 * Autloads classes, if they can be found in the class file maps associated
	 * with this autoloader.
	 *
	 * @param string $sClass
	 * @return string the class name if found, otherwise false
	 */
	public function autoload($sClass)
	{
		if (class_exists($sClass, false ) || interface_exists($sClass))	{
			return false;
		}
		
		$sPath = $this->lookup($sClass);
		if ($sPath !== null) {
			require_once $sPath;
		}

		return true;
	}

	/**
	 * Loop through class files maps untill a match is found
	 *
	 * @param string $sClassName
	 * @return string the path of the class, or null if not found
	 */
	public function lookup($sClassName)
	{
		foreach ($this->_aClassFileMaps as $oClassFileMap) {
			$sPath = $oClassFileMap->lookup($sClassName);
			if (! is_null($sPath)) {
				return $sPath;
			}
		}

		return null;
	}
	
	/**
	 * Looks through all the class paths loaded into ClassFileMaps used by this
	 * autoloader and returns the class names contained in that file in an
	 * array.
	 * 
	 * @param array $path of the class names contained in the file path.
	 * 
	 * @return array of Classes contained in the file $path.
	 */
	public function reverseLookup($path)
	{
		// sanity check
		if (! is_file($path)) {
			throw new AppException('The specified location is not a file');
		}
		if (! is_readable($path)) {
			throw new AppException('The path `'.$path.'` is not readable');
		}

		/*
		 * Search through each class file map array and get all the keys that match
		 * the path.
		 */
		$path = realpath($path);
		$classes = array();
		foreach ($this->_aClassFileMaps as $classFileMap) {
			$classFilemapArray = $classFileMap->getClassMap();
			if ($classFilemapArray != null) {
				$matched = array_keys($classFilemapArray, $path);
				foreach ($matched as $class) {
					array_push($classes, $class);
				}
			}
		}

		return $classes;
	}
}