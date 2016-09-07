<?
/**
 * Библиотека трансформации изображений с кэшем результатов (plot)
 * @copyright 2011, WebExpert
 * @version 2.0
 */


/**
 * Фильтры для постобработки изображений, после трансформации из плотом
 */
class PlotFilters
{
	/**
	 * Делает изображение ч-б и уменьшает у него прозрачность
	 *
	 * @param res $rDesc изображение-ресурс, результат возврата imagecreatetruecolor()
	 * @see http://www.php.net/manual/en/function.imagefilter.php
	 */
	function grayscaleImage(&$rDesc)
	{
		imagefilter($rDesc, IMG_FILTER_GRAYSCALE);
		imagefilter($rDesc, IMG_FILTER_COLORIZE, 255, 255, 255, 90);
	}
}
?>