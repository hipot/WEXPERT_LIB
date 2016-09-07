<?
/**
 * backup server sites
 * use crontab at night
 * php -f /home/bitrix/backup/scripts/backuper.php
 *
 * @version 2.0
 */

$execBash = function ($cmd) {
	echo $cmd . PHP_EOL;
	exec($cmd);
};

function randString($pass_len = 10, $pass_chars = false)
{
	static $allchars = "abcdefghijklnmopqrstuvwxyzABCDEFGHIJKLNMOPQRSTUVWXYZ0123456789";
	$string = "";
	if (is_array($pass_chars)) {
		while (strlen($string) < $pass_len) {
			if (function_exists('shuffle'))
				shuffle($pass_chars);
			foreach ($pass_chars as $chars) {
				$n = strlen($chars) - 1;
				$string .= $chars[mt_rand(0, $n)];
			}
		}
		if (strlen($string) > count($pass_chars))
			$string = substr($string, 0, $pass_len);
	} else {
		if ($pass_chars !== false) {
			$chars = $pass_chars;
			$n = strlen($pass_chars) - 1;
		} else {
			$chars = $allchars;
			$n = 61; // strlen($allchars)-1;
		}
		for ($i = 0; $i < $pass_len; $i ++)
			$string .= $chars[mt_rand(0, $n)];
	}
	return $string;
}

set_time_limit(0);

$arSites = [];

foreach (new DirectoryIterator('/home/bitrix/ext_www') as $fileInfo) {
	if ($fileInfo->isDot() || !$fileInfo->isDir()) {
		continue;
	}
	$arSites[ $fileInfo->getFilename() ] = $fileInfo->getPath() . '/' . $fileInfo->getFilename();
}
$arSites['www'] = '/home/bitrix/www';

$date			= date('d.m.Y');
$randFName		= randString();
$backupDir		= '/mnt/remote_backups';

// delete old arch (5 days)
$execBash('find ' . $backupDir . ' -not -mtime -5 ' . '-delete');

foreach ($arSites as $siteName => $siteDir) {
	
	// base to site folder
	include $siteDir . '/bitrix/php_interface/dbconn.php';
	
	$characterSet = 'cp1251';
	if (preg_match('#BX_UTF[\'", ]+true#is', file_get_contents($siteDir . '/bitrix/php_interface/dbconn.php'))) {
		$characterSet = 'utf8';
	}
	
	if ($DBPassword == '') {
		$execBash('mysqldump --user='.$DBLogin.' --host='.$DBHost.' --default-character-set='.$characterSet.' '.$DBName.' | gzip > '.$siteDir.'/mysql_dump_'.$date.'_'.$randFName.'.sql.gz');
	} else {
		$execBash('mysqldump --user='.$DBLogin.' --host='.$DBHost.' -p'. $DBPassword .' --default-character-set='.$characterSet.' '.$DBName.' | gzip > '.$siteDir.'/mysql_dump_'.$date.'_'.$randFName.'.sql.gz');
	}
	
	// pack site
	$execBash('tar -zcf /mnt/remote_backups/'. $siteName .'_backup_' . $date . '_' . $randFName . '.tar.gz --exclude-from=' . __DIR__ . '/ex.txt ' . $siteDir);
	
	// delete base backup
	$execBash('rm -f ' . $siteDir . '/mysql_dump_'.$date.'_'.$randFName.'.sql.gz');
}

// save server config
$execBash('tar -zcf '. $backupDir . '/etc_settings_' . $date . '_' . $randFName . '.tar.gz /etc /home/bitrix/.msmtprc');


?>