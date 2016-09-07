<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$tn = ($this->__templateName)?$this->__templateName:'.default';
require_once($_SERVER['DOCUMENT_ROOT'].$componentPath.'/cform.php');			// подключаем класс CFormBuilder
require_once($_SERVER['DOCUMENT_ROOT'].$componentPath.'/templates/'.$tn.'/config.php');	// подключаем файл конфигурации из папки шаблона, так как обычно для каждого шаблона этот файл свой
global $FORM;									// оглабаливаем $FORM
$FORM = new CFormBuilder($cfg, '_REQUEST');		// берем объект

if(!empty($FORM->errors)){						// если есть внутренние ошибки
	$FORM->myErrors();
//	die();
}

if($FORM->Submitted){							// если форма была отправлена
	if($FORM->Validate()){						// проверяем
		/*
		если форма без ошибок, производим нужные действия
		например добавить жлемент и.б. или отправить почтовое сообщение
		все поля доступны из $FORM->cfg['f']['код_поля']
		значение из $FORM->cfg['f']['код_поля']['value']
		*/
		$MAIL_VARS = $FORM->getMailVars(); // кидаем все поял в MAIL_VARS
		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].$componentPath.'/templates/'.$tn.'/mail.php');
		$MAIL_VARS['HTML'] = ob_get_contents();
		ob_end_clean();

		CEvent::Send(
			$FORM->cfg["EVENT_TYPE"],
			SITE_ID,
			$MAIL_VARS,
			($FORM->cfg["DUBLICATE_MAIL"] == "Y") ? "Y" : "N",
			($FORM->cfg["EVENT_ID"] > 0) ? $FORM->cfg["EVENT_ID"] : ""
		);

		$FORM->setReady();						// ставим флаг готовности, т.е. форма обработана
	}
}

$this->IncludeComponentTemplate();?>
