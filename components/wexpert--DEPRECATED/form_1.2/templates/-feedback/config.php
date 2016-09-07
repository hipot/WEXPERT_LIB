<?
$cfg['settings'] = array(				// основные настройки формы
	'js_validators' => true,			// флаг, валидировать ли форму с помощью js
	'js_error_list' => true,			// флаг, выводить ошибки js-валидации в списке вверху формы
);
// для всех опций связанных со скриптами нужен jQuery

$cfg['method'] = 'POST';					// метод отпарвки формы
$cfg['enctype'] = 'multipart/form-data';	// способ кодирования данных формы при их отправке на сервер
//$cfg['action'] = '';						// обработчик, к которому обращаются данные формы при их отправке на сервер
$cfg['name'] = 'feedback';					// имя формы


$cfg['f']['name'] = array(
	'label'=>'Имя',
);

$cfg['f']['email'] = array(
	'label'=>'E-Мail',
	'validate'=>array(CFValidators::filled, CFValidators::mail),
);

$cfg['f']['msg'] = array(
	'type' => 'textarea',
	'label'=>'Сообщение',
	'validate'=>CFValidators::filled,
);

$cfg['f']['submit'] = array(
	'type' => 'submit',
	'value'=>'Отправить',
	'class'=>'button',
);

/*
$cfg['f']['name'] = array(				// поле [name="name"]
	'label'=>'Имя',						// имя(label) поля
	'value'=>'значение',				// значение по умолчанию
	'id'=>'ID',							// id
	'attr'=>'attr="val"',				// дополнительные аттрибуты
	'validate'=>array(CFValidators::filled, CFValidators::number),	// валидаторы, можно задавать или строкой или массивом
);
$cfg['f']['send'] = array(				// поле [name="send"]
	'type'=>'submit',					// тип инпута, по умолчанию "text"
	'value'=>'отправить',				// значение
	'id'=>'ID',
	'class'=>'CLASS',					// class
	'attr'=>'attr="val"',
);
$cfg['f']['country'] = array(			// поле [name="country"]
	'type'=>'select',					// тип select
	'label'=>'Страна',					// имя(label) поля
	'id'=>'cn',
	'class'=>'CLASS',
	'multiple'=>'multiple',				// множественное поле, именно для select - это возможность множественного выбора
	'attr'=>'',
	'groups'=>array(					// группы опций <optgroup>
		'fst'=>'Первая',				// соответственно "код группы" => "название"
		'scnd'=>'Вторая'
	),
	'options'=>array(					// опции <option>
		array(
			'rf',						// значение
			'Россия',					// название
			'',							// аттрибуты (например selected)
			'fst'						// код группы
		),
		array(							// а можно и так указать
			'value'=>'md',				// значение
			'name'=>'Молдова',			// название
			'attr'=>'',					// аттрибуты (например selected)
			'group'=>'fst'				// код группы
		),
		array(
			'kz',
			'Казахстан',
			'',
			'scnd'
		)
	),
	'value'=>'md',						// значение по умолчанию
	'validate'=>CFValidators::filled,	// валидатор
);
$cfg['f']['img'] = array(				// поле [name="img"]
	'type'=>'file',						// тип file
	'label'=>'Картинка',
	'id'=>'imgi',
	'class'=>'CLASSiKo',
	'multiple'=>'multiple',				// множественное, те можно выводить поле несколько раз подрят, и принимать массив картинок
	'attr'=>'',
	'validate'=>array(CFValidators::filesize => array('1Mb', '100Mb')),		// валидатор (минимальный размер 1Mb, максимальный 100Mb), если указать 1 параметр - он будет максимальным размером
);
$cfg['f']['mail'] = array(				// поле [name="mail"]
	'label'=>'E-mail',
	'id'=>'ID',
	'class'=>'CLASS',
	'attr'=>'attr="val"',
	'validate'=>array(CFValidators::filled, CFValidators::mail),	// валидаторы (непустота и корректность почтового адреса)
);*/

// а здесь описаны языковые строки
global $MESS;
$MESS['CFB_file'] = 'Файл';
$MESS['CFB_no_cfg'] = 'Не указан массив конфигурации';
$MESS['CFB_no_data'] = 'Нет данных формы';
$MESS['CFB_error_js_validators'] = 'Ошибка: Не правильно заданы JS валидаторы.';
$MESS['CFB_upper_size'] = 'превысил максимально допустимый размер';
$MESS['CFB_upper_max_f_size'] = 'превысил значение MAX_FILE_SIZE';
$MESS['CFB_was_giving_partly'] = 'был получен только частично';
$MESS['CFB_was_not_dnld'] = 'не был загружен';
$MESS['CFB_fld_empty'] = 'Поле "#label#" не заполнено';
$MESS['CFB_fld_mail'] = 'Поле "#label#" заполнено неправильно';
$MESS['CFB_fld_phone'] = 'Поле "#label#" не является телефоном';
$MESS['CFB_fld_number'] = 'Поле "#label#" не является числом';
$MESS['CFB_fld_file_max_sz'] = 'Файл "#label#" превышает максимально допустимый размер';
$MESS['CFB_fld_file_min_sz'] = 'Файл "#label#" меньше минимально допустимого размера';
?>
