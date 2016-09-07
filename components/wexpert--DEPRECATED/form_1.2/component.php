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
		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].$componentPath.'/templates/'.$tn.'/mail.php');
		$MAIL_VARS['HTML'] = ob_get_contents();
		ob_end_clean();

		CEvent::Send('FEEDBACK', SITE_ID, $MAIL_VARS, "N");

		$FORM->setReady();						// ставим флаг готовности, т.е. форма обработана
	}
}

$this->IncludeComponentTemplate();?>
