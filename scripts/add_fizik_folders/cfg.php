<?
$folders = array(
		array(
				'NAME'     	=> 'Наш сервис',				// попадает в .section.php
				'TITLE'    	=> 'Наш сервис',	// попадает в #PAGE_TITLE# шаблона page_template.php, елси не задано, то должно быть равно 'NAME'
				'F_FOLDER' 	=> '/',		// путь к папке, в которой текущая будет создана, от корня сайта (т.е. от DOCUMENT_ROOT)
				'F_NAME'  	=> '#TRANSLATE_EN#',         // имя папки на диске, если тут значение #TRANSLIT# - то должно автоматически
														//транслитироваться из имени 'NAME', #TRANSLATE_EN#, #TRANSLATE_DE# для перевода на английский и немецкий языки
				'MENU'      => array(           		 // произвольный массив пунктов меню, если нужно создать в данной папке файл с меню
						//тип файла меню ---> пукты меню
						'top' => array(
								array('NAME' => 'Главная', 'LINK' => '/'),
								array('NAME' => 'Спецпредложения', 'LINK' => '/deals/'),
								array('NAME' => 'Наш сервис', 'LINK' => '/#TRANSLATE_EN#/'),
								array('NAME' => 'Информация для пассажиров', 'LINK' => '/#TRANSLATE_EN#/'),
								array('NAME' => 'Royal Orchid Plus', 'LINK' => '/royal-orchid-plus/'),
								array('NAME' => 'Контакты', 'LINK' => '/#TRANSLATE_EN#/'),
								),
						'bottom' => array(
								array('NAME' => 'О THAI', 'LINK' => '/#TRANSLATE_EN#/'),
								array('NAME' => 'Новости', 'LINK' => '/news/'),
								array('NAME' => 'FAQ', 'LINK' => '/#TRANSLATE_EN#/'),
								)
				)
		),
		array(
				'NAME'     	=> 'Информация для пассажиров',				// попадает в .section.php
				'TITLE'    	=> 'Информация для пассажиров',	// попадает в #PAGE_TITLE# шаблона page_template.php, елси не задано, то должно быть равно 'NAME'
				'F_FOLDER' 	=> '/',		// путь к папке, в которой текущая будет создана, от корня сайта (т.е. от DOCUMENT_ROOT)
				'F_NAME'  	=> '#TRANSLATE_EN#',         // имя папки на диске, если тут значение #TRANSLIT# - то должно автоматически
				//транслитироваться из имени 'NAME', #TRANSLATE_EN#, #TRANSLATE_DE# для перевода на английский и немецкий языки
		),
		array(
				'NAME'     	=> 'Контакты',				// попадает в .section.php
				'TITLE'    	=> 'Контакты',	// попадает в #PAGE_TITLE# шаблона page_template.php, елси не задано, то должно быть равно 'NAME'
				'F_FOLDER' 	=> '/',		// путь к папке, в которой текущая будет создана, от корня сайта (т.е. от DOCUMENT_ROOT)
				'F_NAME'  	=> '#TRANSLATE_EN#',         // имя папки на диске, если тут значение #TRANSLIT# - то должно автоматически
				//транслитироваться из имени 'NAME', #TRANSLATE_EN#, #TRANSLATE_DE# для перевода на английский и немецкий языки
		),
		array(
				'NAME'     	=> 'О THAI',				// попадает в .section.php
				'TITLE'    	=> 'О THAI',	// попадает в #PAGE_TITLE# шаблона page_template.php, елси не задано, то должно быть равно 'NAME'
				'F_FOLDER' 	=> '/',		// путь к папке, в которой текущая будет создана, от корня сайта (т.е. от DOCUMENT_ROOT)
				'F_NAME'  	=> '#TRANSLATE_EN#',         // имя папки на диске, если тут значение #TRANSLIT# - то должно автоматически
				//транслитироваться из имени 'NAME', #TRANSLATE_EN#, #TRANSLATE_DE# для перевода на английский и немецкий языки
		),
		array(
				'NAME'     	=> 'FAQ',			// попадает в .section.php
				'TITLE'    	=> 'FAQ',	// попадает в #PAGE_TITLE# шаблона page_template.php, елси не задано, то должно быть равно 'NAME'
				'F_FOLDER' 	=> '/',		// путь к папке, в которой текущая будет создана, от корня сайта (т.е. от DOCUMENT_ROOT)
				'F_NAME'  	=> '#TRANSLATE_EN#',         // имя папки на диске, если тут значение #TRANSLIT# - то должно автоматически
				//транслитироваться из имени 'NAME', #TRANSLATE_EN#, #TRANSLATE_DE# для перевода на английский и немецкий языки
		),
);

?>