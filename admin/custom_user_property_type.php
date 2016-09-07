<?
// РЕГИСТРАТОР типа свойства, используется только один раз для новой базы данных.
// курим мануал: http://dev.1c-bitrix.ru/api_help/iblock/classes/user_properties/index.php
// RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', 'main', 'We_CIBlockPropertyCityLink', 'GetUserTypeDescription', 100, '/php_interface/tools/prop_type.php');
if(!class_exists('We_CIBlockPropertyCityLink')){
	/**
	 * Пользовательский тип свойства привязка к городу модуля Геолокация.
	 * @package default
	 * @author Матяш Сергей Юрьевич <matiaspub@gmail.com>
	 * @version 1 , 16.07.2012
	 */
	class We_CIBlockPropertyCityLink
	{
		function GetUserTypeDescription() {
			return array(
				'PROPERTY_TYPE'       => 'C',
				'USER_TYPE'           => 'We_CIBlockPropertyCityLink',
				'DESCRIPTION'         => 'Привязка к городу (модуль GEO_IP)',
				'GetPropertyFieldHtml'=> array('We_CIBlockPropertyCityLink', 'GetPropertyFieldHtml'),
				'ConvertToDB'         => array('We_CIBlockPropertyCityLink', 'ConvertToDB'),
				'ConvertFromDB'       => array('We_CIBlockPropertyCityLink', 'ConvertFromDB')
			);
		}

		function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName) {
			global $USER;
			global $APPLICATION;
			$module_name_ = 'banki_geoip';
			$cityTags = array();
			if(!CModule::IncludeModule($module_name_)) {
			    ShowError("Невозможно подключить модуль ${module_name_}");
			} else{
				$GLOBALS['APPLICATION']->AddHeadScript('/_lib/jquery/jquery.min.js',true);
				$GLOBALS['APPLICATION']->AddHeadString('<link href="/_lib/jquery/plugins/autocomplete/css/jquery-autocomplete.custom.css"  type="text/css" rel="stylesheet" />',true);
				$GLOBALS['APPLICATION']->AddHeadScript('/_lib/jquery/plugins/autocomplete/jquery-ui-autoconplite.custom.min.js',true);
				$regionList = CBankiGeoip::getRegionList();
				$regionName='';
				if((int)$value > 0){
					$region = CBankiGeoip::getRegionByID((int)$value);
					$regionName = $region['region_name'];
				}
				foreach($regionList as $reg){
					if($reg['is_city']){
						$cityTags[] = array('label'=>$reg['region_name'],'value'=>$reg['region_name'],'dop'=>$reg['id']);
					}
				}
			}

			ob_start();
			?>
			<script type="text/javascript">
			$(function(){
			  $("#geo_city_name").autocomplete({
				  source: <?=CUtil::PhpToJSObject($cityTags, false);?>,
				  select: function(event,ui){
					  $('#geo_city_id').val(ui.item.dop);
				  }
			  });
			});
			</script>
			<input id="geo_city_name" name="<?=$strHTMLControlName?>_name" value="<?=$regionName?>" type="text" />
			<input type="hidden" id="geo_city_id" name="<?=$strHTMLControlName?>" value="<?=$value?>" type="text" />
			<?
			$HTML = ob_get_contents();
			ob_end_clean();
			return $HTML;
		}

		function ConvertToDB($arProperty, $value) {
			$return = array();
			if(intVal($value['VALUE']) > 0) {
				$return['VALUE'] = intVal($value['VALUE']);
			}
			else {
				$return['VALUE'] = '';
			}

			return $return;
		}

		function ConvertFromDB($arProperty, $value) {
			$return = array();
			if(intVal($value['VALUE']) > 0) {
				$return['VALUE'] = intVal($value['VALUE']);
			}
			else {
				$return['VALUE'] = '';
			}

			return $return;
		}

	}
}

?>
