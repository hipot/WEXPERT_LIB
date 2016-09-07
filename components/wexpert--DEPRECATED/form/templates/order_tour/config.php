<?
$cfg['settings'] = array(                   // основные настройки формы
	'js_validators'     => true,            // флаг, валидировать ли форму с помощью js
	'js_error_tooltips' => true,            // флаг, выводить ошибки js-валидации в виде тултипов
//	'js_error_list'     => true,            // флаг, выводить ошибки js-валидации в списке вверху формы
);
// для всех опций связанных со скриптами нужен jQuery

$cfg['EVENT_TYPE'] = 'RESERVATION';            // тип почтового события
//$cfg['EVENT_ID'] = 23;                    // ID почтового сообщения
//$cfg['DUBLICATE_MAIL'] = 'Y';             // Дублировать ли сообщение админу
$cfg['method'] = 'POST';                    // метод отпарвки формы
$cfg['enctype'] = 'multipart/form-data';    // способ кодирования данных формы при их отправке на сервер
//$cfg['action'] = '';					    // обработчик, к которому обращаются данные формы при их отправке на сервер
$cfg['name'] = 'order_tour';                // имя формы

$cfg['f']['name'] = array(
	'label'   => 'Ваше имя',
	'validate'=> array(CFValidators::filled),
);
$cfg['f']['mail'] = array(
	'label'   => 'Электронная почта',
	'validate'=> array(CFValidators::filled),
);
$cfg['f']['phone_num'] = array(
	'label'   => 'Телефон с кодом города (оператора)',
	'validate'=> array(CFValidators::filled, CFValidators::phone),
);
$cfg['f']['phone_code'] = array(
	'label' => 'код города',
	'validate'=> array(CFValidators::filled, CFValidators::phone, CFValidators::stringsize=>array(3)),
);
// скрытые
$cfg['f']['hotelname'] = array(
	'label'   => 'Проживание',
	'type'    => 'hidden',
);
$cfg['f']['duration'] = array(
	'label'   => 'Продолжительность',
	'type'    => 'hidden',
);
$cfg['f']['tourists'] = array(
	'label'   => 'Туристы',
	'type'    => 'hidden',
);
$cfg['f']['room'] = array(
	'label'   => 'Номер',
	'type'    => 'hidden',
);
$cfg['f']['meal'] = array(
	'label'   => 'Питание',
	'type'    => 'hidden',
);
$cfg['f']['price'] = array(
	'label'   => 'Стоимость тура',
	'type'    => 'hidden',
);
?>
