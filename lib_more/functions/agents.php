<?
/**
 * Функции-агенты для битрикса
 */

/**
 * Удаляем заснувшие запросы и запросы SELECT, длительность которых больше 200 секунд
 *
 * Константа WE_NOT_REMOVE_LONG_SELECT позволяет отменить выполнение
 *
 * @return string
 */
function AgentRemoveLongSelect()
{
	if (constant('WE_NOT_REMOVE_LONG_SELECT') === true) {
		return __FUNCTION__ . '();';
	}

	global $DB;

	$timeout_s = 200;

	$r = $DB->Query('SHOW PROCESSLIST');

	while ($p = $r->Fetch()) {
		$sql		= trim($p['Info']);
		$procId		= intval($p['Id']);

		if (intval($p['Time']) >= $timeout_s &&
			(substr($sql, 0, 6) == 'SELECT' || $p['Command'] == 'Sleep')
		) {
			$DB->Query('KILL ' . $procId);
		}
	}

	return __FUNCTION__ . '();';
}
