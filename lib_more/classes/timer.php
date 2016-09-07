<?php
/**
 * Класс точного подсчета прошедшего времени в микросекундах
 * (напр. для нахождения узких моментов в коде)
 * (10^?6 с = 1 микросекунда мкс)
 *
 *
 * @copyright (c) Webexpert, hipot
 * @version 1.0
 * @link
 */
class Timer
{
	/**
	 * Начало отсчета времени
	 *
	 * @var float
	 */
	var $_start;
	/**
	 * Конец отсчета времени
	 *
	 * @var float
	 */
	var $_stop;
	
	/**
	 * Начать отсчет времени
	 *
	 */
	function setStart()
	{
		$t = gettimeofday();
		$this->_start = $t['sec']*1000000.0 + $t['usec'];
	}
	
	/**
	 * Закончить отсчет времени
	 *
	 */
	function setStop()
	{
		$t = gettimeofday();
		$this->_stop = $t['sec']*1000000.0 + $t['usec'];
	}
	
	/**
	 * Вернуть прошедшее время (в секундах)
	 *
	 * @return float
	 */
	function elapsed()
	{
		$elapsed = ($this->_stop - $this->_start) / 1000000.0;
		return $elapsed;
	}
}
?>