<?
/**
 * Very tiny simple autoloader with support of wexpert classes
 *
 * @author weXpert, 2014
 * @version 1.0
 *
 * HELP:
 * <classes root> is:
 * - /local/php_interface/include/lib/classes/
 * - /bitrix/php_interface/include/lib/classes/
 *
 * use classes with namespaces:
 * \Wexpert\Db\AbstractBaseAdapter	-->  <classes root>/wexpert/db/abstractbaseadapter.php
 * \CImg 							-->  <classes root>/cimg.php
 * ...
 */


// add custom __autoload in stack after \Bitrix\Main\Loader::autoLoad()
// see: var_dump( spl_autoload_functions() );
spl_autoload_register(function ($className) {
	//echo $className; die();

	$libDirs = array(
		$_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/lib/classes',
		$_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/include/lib/classes',
		// ...
	);

	foreach ($libDirs as $libDir) {
		// to work with other frameworks, case sensitive UNIX folders
		$checkPaths = array(
			$libDir . '/' . str_replace('\\', '/', $className) . '.php',
			$libDir . '/' . str_replace('\\', '/', strtolower($className)) . '.php',
			// ...
		);
		foreach ($checkPaths as $classFile) {
			if (file_exists($classFile) && is_readable($classFile)) {
				require $classFile;
				return;
			}
		}
	}
	if (! class_exists($className)) {
		// IGNORE, to be fatal
		//throw new Exception('no class in wexpert simple autoloader: ' . $className);
	}

});

//var_dump( spl_autoload_functions() );


?>