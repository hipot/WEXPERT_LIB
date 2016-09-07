<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/* @var $this CBitrixComponent */

/*
array(	// параметры
"POST_NAME"=>"commercial_order",	// имя формы и массива параметров в посте
"REQ_FIELDS"=>array("name", "mail"),	// обязательные поля(предусмотрена вложенность (задавать как структуру массива пост))
"EVENT_TYPE"=>"ORDER_FORM_COMMERCIAL",	// тип почтового события
"EVENT_ID"=>21,	// идентиыикатор почтового события
"TEMPLATE" => "mail.tmp" // если параметр установлен, то в теле письма в админ. части будет доступна переменная #HTML# с готовым заполненым шаблоном(который указан), иначе в в теле письма будут доступны переменные из массива пост #переменная#
"ADD_ELEMENT"=>array(	// добавить элемент в и.б., если элемент добавлен, то в шаблоне доступна переменная $arResult['ADDED_ID'], иначе переменная с ошибкой $arResult['error']['add']
	"FIELDS"=>array(	// поля и.б. (код поля => значение)
		"IBLOCK_ID"=>17,	// идентификатор и.б.
		"NAME"=>$escPOST['name'],
		"ACTIVE_FROM"=>ConvertTimeStamp(getmicrotime()),
	),
	"PROPS"=>array()), // свойства и.б. (код свойства => значение),
"DUBLICATE_MAIL" => "Y" // дублировать письмо
"REDIRECT_URL"=>false // страницаб на которую редиректится компонента после отработки (по умолчанию без редиректа)



'FIELDS' => array(	// готовые поля в шаблоне сайта выводятся с помощью ф-ции <?=GetFieldHTML('код поля')?>
		'code' // код поля
		 => array('TYPE'=>'text', // тип поля
		  'NAME'=>'name', // имя поля, если имя пустое, то берется имя из языкового файла
		   компоненты GetMessage('REQ_'.$code), где $code - это код поля
		  'VALUE'=>'f', // значение по умолчанию
		  'ATTR'=>'class="red"' // атрибуты
		  ),
		'code2' => array('TYPE'=>'text', 'NAME'=>'[step1][name]'),
		'select' => array('TYPE'=>'select', 'NAME'=>'sel', 'VALUE'=>'perenes', 'OPTIONS'=>array( // теги <option>
			'vet'=>'вет', // аттрибут value тега option
			'perenes'=>'перенес', // значение тега option

			)
		),
		'texta' => array('TYPE'=>'textarea', 'NAME'=>'tt', 'VALUE'=>'f', 'ATTR'=>'class="ret"')
	),
*/


/**
 * @todo
 * Подрубаем языковой файл шаблона компонента, чтобы в нем определять текстовки ошибок (т.е.
 * сообщения вида "RTN_" . $fld и "RTEN_" . $fld) для каждой формы отдельно (шаблон - это одна форма),
 * а не один раз для всего компонента
 */
global $MESS;
@include_once ($_SERVER['DOCUMENT_ROOT'] . $this->GetPath() . '/templates/' . $this->__templateName . '/lang/ru/template.php');
@include_once ('functions.php');
//$arParams['_POST'] = escapeArray($_POST);
$arResult = array();
if (! empty($arParams['_POST'][$arParams["POST_NAME"]]) && check_bitrix_sessid())
{


	CModule::IncludeModule("iblock");

	// проверка обязательных полей
	if(!empty($arParams["REQ_FIELDS"])){
		foreach ($arParams["REQ_FIELDS"] as $k => $f)
		{
			if(is_array($f) && !empty($f))
			{
				foreach($f as $fld)
				{
					if (trim($arParams['_POST'][ $arParams["POST_NAME"] ][$k][$fld]) == "")
						$arResult["error"][] = GetMessage("RTN_".$fld);
					elseif($fld == 'mail' && !check_email($arParams['_POST'][$arParams["POST_NAME"]][$k][$fld]))
						$arResult["error"][] = GetMessage("RTEN_".$fld);
				}
			} else if (trim($arParams['_POST'][ $arParams["POST_NAME"] ][$f]) == "") {
				$arResult["error"][] = GetMessage("RTN_" . $f);
			} elseif ($f == 'mail' && !check_email($arParams['_POST'][ $arParams["POST_NAME"] ][$f])) {
				$arResult["error"][] = GetMessage("RTEN_" . $f);
			}
		}
	}

	$MAIL_VARS = array();
	if(empty($arResult["error"]))
	{

		foreach($arParams['_POST'][$arParams["POST_NAME"]] as $key=>$p)
		{
			$MAIL_VARS[$key] = $p;
		}

		// добавление элемента, если надо
		if(is_array($arParams['ADD_ELEMENT']) && !empty($arParams['ADD_ELEMENT']['FIELDS'])){
			$el = new CIBlockElement();

			foreach ($arParams['ADD_ELEMENT']['FIELDS'] as $k=>$v){
				$arAddFields[$k] = $v;
			}

			if(!empty($arParams['ADD_ELEMENT']['PROPS'])){
				foreach ($arParams['ADD_ELEMENT']['PROPS'] as $pk=>$pv){
					$arProps[$pk] = $pv;
				}
				if(!empty($arProps)) $arAddFields['PROPERTY_VALUES'] = $arProps;
			}

			if($PRODUCT_ID = $el->Add($arAddFields)) {
				$arResult['ADDED_ID']  = $PRODUCT_ID;
				$MAIL_VARS['ADDED_ID'] = $PRODUCT_ID;
			}
			else
				$arResult['error']['add'] = $el->LAST_ERROR;
		}

		/**
		 * редирект делаем на успешный урл если нет ошибок и отправляем письмо
		 */
		if (empty($arResult['error'])) {

			if($arParams['TEMPLATE'] != ''){
				$arResult = $MAIL_VARS;
				$MAIL_VARS = array();
				$orig_template = $this->__templateName;
				$this->__templateName = $arParams['TEMPLATE'];
				ob_start();
				$this->IncludeComponentTemplate();
				$MAIL_VARS['HTML'] = ob_get_contents();
				ob_end_clean();
				$this->__templateName = $orig_template;
			}

			// делаем готовые поля
			if(!empty($arParams['FIELDS'])){
				foreach ($arParams['FIELDS'] as $code=>$vals){
					$vals['NAME'] = ($vals['NAME']!='')?$vals['NAME']:GetMessage('REQ_'.$code);

					if(strpos($vals['NAME'], '[')===false){
						$name = '['.$vals['NAME'].']';
						$val =  ($arParams['_POST'][ $arParams['POST_NAME'] ][ $code ])?$arParams['_POST'][ $arParams['POST_NAME'] ][ $code ]:$vals['VALUE'];
					} else{
						$name = $vals['NAME'];
						preg_match_all('/[a-zA-Z0-9_-]+/', $name, $arN);
						if(sizeof($arN[0])==1){
							$val = $arParams['_POST'][ $arParams['POST_NAME'] ][ $arN[0][0] ];
						} elseif(sizeof($arN[0])==2){
							$val = $arParams['_POST'][ $arParams['POST_NAME'] ][ $arN[0][0] ][ $arN[0][1] ];
						} elseif(sizeof($arN[0])==3){
							$val = $arParams['_POST'][ $arParams['POST_NAME'] ][ $arN[0][0] ][ $arN[0][1] ][ $arN[0][2] ];
						}
						if(trim($val)==''){
							$val = $vals['VALUE'];
						}
					}

					switch ($vals['TYPE']) {
						case 'select':
						$str = '<select name="'.$arParams['POST_NAME'].$name.'" '.$vals['ATTR'].'>';
						foreach ($vals['OPTIONS'] as $c=>$v){
							$ch = ($val == $c)?'selected':'';
							$s = '<option '.$ch.' value="'.$c.'">'.$v.'</option>';
							$str .= $s;
						}
						$str .= '</select>';
						break;

						case 'textarea':
						$str = '<textarea name="'.$arParams['POST_NAME'].$name.'" '.$vals['ATTR'].'>'.$val.'</textarea>';
						break;

						default:
						if($vals['TYPE'] == 'checkbox' || $vals['TYPE'] == 'radio'){
							$ch = ($val != '')?'checked':'';
						}
						$str = '<input '.$ch.' type="'.$vals['TYPE'].'" name="'.$arParams['POST_NAME'].$name.'" value="'.$val.'" '.$vals['ATTR'].' />';
						break;
					}
					$arParams['HTML_FIELDS'][ $code ] = $str;
				}
				global $HTML_FIELDS;
				$HTML_FIELDS = $arParams['HTML_FIELDS'];

				function GetFieldHTML($code){
					global $HTML_FIELDS;
					return $HTML_FIELDS[ $code ];
				}
			}

			/*$MAIL_VARS['FILES'] =  array(
				array(
					'SRC'   => $_SERVER['DOCUMENT_ROOT'] . '/upload/iblock/016/0_4ed7_7e61ad7b_l.rar',
					'NAME'  => 'Хороший архив.rar' // отображаемое имя прикрепленного файла в письме, может быть пустым
				)
			);*/

			CEvent::Send($arParams["EVENT_TYPE"], SITE_ID, $MAIL_VARS, $arParams["DUBLICATE_MAIL"] == "Y" ? "Y" : "N", $arParams["EVENT_ID"] > 0 ? $arParams["EVENT_ID"] : "");

			$_SESSION[ "READY" . $arParams["POST_NAME"] ] = 'Y';

			if (isset($arParams['REDIRECT_URL']))
				LocalRedirect($arParams['REDIRECT_URL']);

		}
	}
}

$this->IncludeComponentTemplate();
unset($HTML_FIELDS);
?>
