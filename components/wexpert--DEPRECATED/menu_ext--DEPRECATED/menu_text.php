для начала читаем по порядку
http://dev.1c-bitrix.ru/api_help/main/general/menu.php
http://dev.1c-bitrix.ru/api_help/main/general/menu_5x.php
http://dev.1c-bitrix.ru/user_help/settings/settings/components_2/navigation/menu.php

еще в компоненте есть всякие проверки
если $arParams['MAX_LEVEL'] не входит в период (1-4) включительно то $arParams['MAX_LEVEL'] = 1
$arParams['ROOT_MENU_TYPE'] и $arParams['CHILD_MENU_TYPE'] по умолчанию 'left'
дальше...

массив возвращаемый файлом .--.menu_ext.php или .--.menu.php
лучше всего в таком файле вызывать компоненту типа self:menu_ext с PHP кешированем, и возвращать в компоненте массив вид которого показан ниже То есть компонента используется как функция.
Array
(
    [0] => пункт меню 1
        Array
            (
                [0] => заголовок пункта меню (в шаблоне $arResult[$i]['TEXT'])
                [1] => ссылка на пункте меню (в шаблоне $arResult[$i]['LINK'])
                [2] => массив дополнительных ссылок для подсветки пункта меню: (в шаблоне $arResult[$i]['ADDITIONAL_LINKS'])
                    Array (
                        (
                            [0] => ссылка 1
                            [1] => ссылка 2
                            ...
                         )
                [3] => массив дополнительных переменных передаваемых в шаблон меню:
                    Array  (в шаблоне $arResult[$i]['PARAMS'] = array())
                        (
                            [имя переменной 1] => значение переменной 1
                            [имя переменной 2] => значение переменной 2
                            ...
                            ['DEPTH_LEVEL'] => {int} переопределяет параметр выделения в шаблоне ($arResult[$i]['DEPTH_LEVEL'])
                            ['IS_PARENT'] => {false|true} переопределяет параметр выделения в шаблоне ($arResult[$i]['IS_PARENT'])
							при определении последних двух строк необходимо здесь же передать параметр (['FROM_IBLOCK']='Y' - вообщето проверка идет на isset(), так что это может быть даже IBLOCK_ID, если он вам понадобится в шаблоне)
							так же тут можно передавать необходимые параметры для использования их в шаблоне (через $arResult[$i]['PARAMS'][параметр])
                         )
                [4] => условие, при котором пункт меню появляется
                       это PHP выражение, которое должно вернуть "true"
            )
    [1] => пункт меню 2
    [2] => пункт меню 3
    ...
)

в файле .--.menu_ext.php возвращаемый массив нужно слить с основным массивом меню и вернуть результирующий массив
$aMenuLinks = array_merge($aMenuLinks, [возвращаемый массив]);

!!!вызов компоненты и сама копонента типа MENU_EXT лежат в текущей папке


в общем-то в компоненте bitrix:menu лишнего кода нет, есть несколько замедляющих моментов, а так же бOльшая часть кода(~70%) выполняется при условии если пользователь авторизован, и нужна для вывода всяких приколов в панели

в шаблоне меню доступен массив результатов $arResult  = Array
(
    [0] => пункт меню 1
        Array
            (
                [TEXT] => заголовок пункта меню
	            [LINK] => ссылка на пункте меню
	            [SELECTED] =>  выделени или нет
	            [PERMISSION] => право доступа на страницу указанную в LINK для текущего пользователя, возможны следующие значения:
								D - доступ запрещён
								R - чтение (право просмотра содержимого файла)
								U - документооборот (право на редактирование файла в режиме документооборота)
								W - запись (право на прямое редактирование)
								X - полный доступ (право на прямое редактирование файла и право на изменение прав доступа на данный файл)
	            [ADDITIONAL_LINKS] => дополнительные ссылки для подстветки меню
	            [ITEM_TYPE] => флаг указывающий на тип ссылки указанной в LINK, возможны следующие значения:
								D - каталог (LINK заканчивается на "/")
								P - страница
								U - страница с параметрами
	            [ITEM_INDEX] => порядковый номер пункта меню
	            [PARAMS] => Array ассоциативный массив параметров пунктов меню
	                (
	                    [param1] => val1
	                )

	            [DEPTH_LEVEL] => 1
	            [IS_PARENT] => 1
            )
    [1] => пункт меню 2
    [2] => пункт меню 3
    ...
)

меню удобно использовать для построения нестандартной карты сайта(имеющиеся типы или отдельный тип).

несколько коротких шаблонов меню, горизонтальное или вертикальное в обоих случаях настраивается стилями.

самый простой:
//----------------------------------------------------------------------------
<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$curDir = $APPLICATION->GetCurPage(false);?>
<ul>
<?foreach ($arResult as $arItem):?>
    <li>
         <?if ($curDir == $arItem["LINK"]):?>
                  <?=$arItem["TEXT"];?>
         <?elseif ($arItem["SELECTED"]):?>
                  <a href="<?=$arItem["LINK"];?>"><b><?=$arItem["TEXT"];?></b></a>
         <?else:?>
                  <a href="<?=$arItem["LINK"];?>"><?=$arItem["TEXT"];?></a>
         <?endif?>
     </li>
<?endforeach;?>
</ul>
//----------------------------------------------------------------------------


многоуровневый, с параметром MAX_LEVEL_OPEN - до какой глубины показывать невыделенные итемы.
//----------------------------------------------------------------------------
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$par = array();
$curDir = $APPLICATION->GetCurDir();
?>
<div class="menu">
<ul>
	<?foreach($arResult as $k => $arItem):


		if ($arItem["PARAMS"]["SELECTED"] && $arItem["LINK"] == $curDir){
			$itm = '<span>'.$arItem["TEXT"].'</span>';
		} else{
			$cl = ($arItem["PARAMS"]["SELECTED"])?'class="selected"':'';
			$itm =  '<a '.$cl.' href="'.$arItem["LINK"].'">'.$arItem["TEXT"].'</a>';
		}


		if ($arItem["DEPTH_LEVEL"] < 2){
			echo '<li>'.$itm;
			if($arItem["IS_PARENT"] || $arItem["PARAMS"]["SELECTED"]){
				$par[] = $k;
				echo "<ul>";
			}
		} elseif (($arItem["IS_PARENT"] && $arItem["PARAMS"]["SELECTED"])
					|| (!empty($par) && $arItem["DEPTH_LEVEL"]-1 == $arResult[$par[count($par)-1]]["DEPTH_LEVEL"])){
			echo '<li>'.$itm;
			if ($arItem["IS_PARENT"] && $arItem["PARAMS"]["SELECTED"]){
				$par[] = $k;
				echo "<ul>";
			}
		}

		if (isset($arResult[$k+1]) && $arItem["DEPTH_LEVEL"]-1 == $arResult[$par[count($par)-1]]["DEPTH_LEVEL"]
		&& $arItem["DEPTH_LEVEL"] > $arResult[$k+1]["DEPTH_LEVEL"]){
			echo str_repeat("</ul></li>", ($arItem["DEPTH_LEVEL"] - $arResult[$k+1]["DEPTH_LEVEL"]));
			if ($arItem["DEPTH_LEVEL"]-1 == $arResult[$par[count($par)-1]]["DEPTH_LEVEL"]){
				array_pop($par);
			}
		}
		else echo "</li>";


	endforeach?>
</ul>
</div>
//----------------------------------------------------------------------------


и в довесок использование нового метода для получения постранички
в компоненте после выборки
$rsItems - обьект класса CDBResult();
<?
if ($arParams["USE_PAGER"] == "Y")
{
	$arResult['NAV_STRING'] = $rsItems->GetPageNavStringEx($navComponentObject, ""/* путь к шаблону */, false /*SHOW_ALWAYS кажется*/);

	//количество ссылок на странице, после выборки:
	if(!isset($arParams["PAGES_ON_WINDOW"]))
		$arParams["PAGES_ON_WINDOW"] = 10;

	$rsItems->nPageWindow = $arParams["PAGES_ON_WINDOW"]; /* выставить количество ссылок(страниц) в постраничке */
}
?>