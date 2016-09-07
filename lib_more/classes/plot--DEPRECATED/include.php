<?
/**
 * Оригинальная версия плота, все пользуются
 *
 * @author WebExpert, (c)hipot
 * @version 2.0.1, 12-07-2011
 *
 *
 *
 * Если необходимо изменить размер картинок не меняя тип - убиваем папку с типом,
 * либо создаем новый.
 *
 * Примеры вызовов:
 * 1. файл в структуре инфоблоков
 *    $img_a = NewImage("/iblock/21d/yqnxmj4.jpg","width20", 20, "", true);
 *    копирует файл в
 *    /upload/iblock_s1_width20/21d/yqnxmj4.jpg
 *
 * 2. файл вне структуры инфоблока
 *    $img_a = NewImage("/bitrix/templates/citizens_main/images12/krug_blank.png","width20", 20);
 *    копирует файл в
 *    /upload/pictures_width20/images12/krug_blank.png
 *
 * Версия 2.0.1
 * - исправлено: не работала возможность трансформировать прозрачные gif (прозрачные места заливались черным)
 *
 * Версия 2.0 добавлено
 * - начал переводить на объектную структуру
 * - добавил проверку, если картинка меньше переданных параметров, то ее не обрабатывать
 *
 * Версия 1.2 добавлено
 * - параметр для применения фильтра $imgFilterCallback, должна быть пользовательская функция, Напр.:
 * class PlotFilters
 * {
 * 		function grayscaleImage(&$desc_r)
 * 		{
 * 			// см. http://www.php.net/manual/en/function.imagefilter.php
 *	 		imagefilter($desc_r, IMG_FILTER_GRAYSCALE);
 *		}
 * }
 * $arItem["LOGO_BW"] = NewImage(CFile::GetPath($arItem["DETAIL_PICTURE"]), 'list_scroll_vendr_bw', $cust_w, $cust_h,
 * 								 false, array('PlotFilters', 'grayscaleImage'));
 *
 *
 * Версия 1.1, добавлено
 * - если передана и ширина и высота, то происходит обрезание куска фотографии, чтобы тот вместился в размеры
 * - оптимизирована проверка на наличие пути к изображению
 *
 * Версия 1.0.1, добавлено
 * - полупрозрачность при масштабировании PNG24
 * - тестовая возможность вызова для файлов не из папки /upload/iblock/, тогда они складываются в /upload/pictures_type/
 * (пока нет проверки на одинаковые имена файлов)
 *
 *
 */


require_once dirname(__FILE__) . '/filters.php';
require_once dirname(__FILE__) . '/base.php';


/**
 * Функция по уменьшению изображений и сохранению их в папке upload
 *
 * @param string $file путь к файлу изображения от DOCUMENT_ROOT
 * @param string $type тип, для добавленя к имени папки iblock. например little, middle, width125
 * @param int $width ширина нужного изображения
 * @param int $height = false высота нужного [optional]
 * @param bool $set_site_id = false устанавливать ли в имя директорий имя сайта, т.е. iblock_{SITE_ID}_{TYPE} [optional]
 * @param bool|callback $imgFilterCallback = false применить ли фильтр к уменьшенному изображению, должна быть пользовательская функция, куда передается
 * ссылка на изображение-ресурс непосредственно перед сохранением уменьшенной фотографии на диск [optional]
 * @return bool|array массив с данными уменьшенного фото, или false в случае ошибки
 *
 * @uses CheckDirPath(), /bitrix/modules/main/include/mainpage.php, CMainPage::GetSiteByHost() *
 */
function NewImage($file, $type, $width, $height = 0, $set_site_id = false, $imgFilterCallback = false)
{
	return PlotBase::Transform($file, $type, $width, $height, $set_site_id, $imgFilterCallback);
}
?>
