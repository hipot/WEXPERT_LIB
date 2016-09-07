<?
/* @var $this CBitrixComponent */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
CpageOption::SetOptionString("main", "nav_page_in_session", "N");
// параметры кеша --------------
$arParams['PAGEN_1'] = $_REQUEST['PAGEN_1'];

$CACHE_ID = 'we_comments_page' . md5(serialize($arParams));
$CAHCE_DIR = '/cache/php/we_comments/';
$obMenuCache = new CPHPCache;
//------------------------------

// установка ====================================================================
$tbls = $DB->Query('SHOW TABLES LIKE "we_comments"');

// добавим кнопку установки в панель
if($tbls->AffectedRowsCount()<1){
	$this->AddIncludeAreaIcon(array(
		'TITLE'=>'Установить',
		'SRC' => $this->GetPath().'/images/icon.png',
		'URL' => $APPLICATION->GetCurPageParam('install_me_please=Y'),
	));
}
if($_GET['install_me_please']=='Y'){
	if($tbls->AffectedRowsCount()<1){
		$arrErrors = $DB->RunSqlBatch($_SERVER["DOCUMENT_ROOT"].$this->GetPath()."/we_comments.sql");
		ShowError($arrErrors);
		$et = new CEventType;
		$etid = $et->Add(array(
			"LID" => LANGUAGE_ID,
			"EVENT_NAME" => 'WE_COMMENTS_ADDED',
			"NAME" => 'Добавлен комментарий',
			"DESCRIPTION" => '#HREF# - ссылка на комментарий'
		));
		if($et->LAST_ERROR!='')
			ShowError($arrErrors);
		$arNewM = array(
			'ACTIVE'=>'Y',
			'EVENT_NAME'=>'WE_COMMENTS_ADDED',
			'LID'=>SITE_ID,
			'EMAIL_FROM'=>'#DEFAULT_EMAIL_FROM#',
			'EMAIL_TO'=>'#EMAIL_TO#',
			'SUBJECT'=>'Добавлен комментарий на сайте #SITE_NAME#',
			'BODY_TYPE'=>'html',
			'MESSAGE'=>'Добавлен комментарий. <br/>
		Для просмотра перейдите по <a href="#HREF#">ссылке</a>',
		);

		$emess = new CEventMessage;
		$ader = $emess->Add($arNewM);
		if($et->LAST_ERROR!='')
			ShowError($arrErrors);
		if($et->LAST_ERROR=='' && $et->LAST_ERROR=='' && $arrErrors=='')
			LocalRedirect($APPLICATION->GetCurPageParam('', array('install_me_please')));
	} else{
		ShowError('Установлено уже!!!');
		$this->IncludeComponentTemplate();
	}
}
if($tbls->AffectedRowsCount()<1){
	ShowError('Необходимо установить базу данных! нажмите на кнопку "Установить" на панели компонента в режиме редактирования.');
	$this->IncludeComponentTemplate();
} else{
// конец установки ==============================================================

	if(!isset($arParams["CACHE_TIME"])){
		$arParams["CACHE_TIME"] = 3600;
		$arParams["CACHE_TIME"] = 0;
	}
	if(!isset($arParams['MAX_DEPTH'])){
		$arParams['MAX_DEPTH'] = 5;
	}
	if(!isset($arParams['TAB_SIZE'])){
		$arParams['TAB_SIZE'] = 20;
	}
	if(!isset($arParams['WRITE_ALL'])){
		$arParams['WRITE_ALL'] = 'Y';
	}
	if ($arParams["USE_CAPTCHA"] == 'Y'){
		if($USER->IsAuthorized()){
			$arResult["USE_CAPTCHA"] = 'N';
		} else{
			$arResult["USE_CAPTCHA"] = 'Y';
		}
	}
	if ($arResult["USE_CAPTCHA"] == "Y") {
	    $arResult["CAPTCHA_CODE"] = htmlspecialchars($APPLICATION->CaptchaGetCode());
	}
	if($arParams['WRITE_COM_COM']){
		unset($arParams['nPageSize']);
	}

	// определяем имя и мыло пользователя --------
	$arResult['arUser']['NAME'] = '';
	$arResult['arUser']['EMAIL'] = '';
	if($USER->IsAuthorized()){
		$arResult['arUser']['NAME'] = ($arPrarms['SHOW_USER_LOGIN']=='Y')?$USER->GetLogin():$USER->GetFullName();
		if($arResult['arUser']['NAME']==''){
			$arResult['arUser']['NAME'] = $USER->GetLogin();
		}
		$arResult['arUser']['EMAIL'] = $USER->GetEmail();
	}
	// -------------------------------------------

	// определяем права пользователя -------------------------
	$arResult['canComments'] = true;
	$arResult['canModerate'] = false;
	if($USER->IsAuthorized()){
		$arResult['canComments'] = true;
	} else{
		if($arParams['WRITE_ALL']!='Y'){
			$arResult['canComments'] = false;
		} else{
			$arResult['canComments'] = true;
			if($arParams['WRITE_COM_COM']){
				$arResult['canCommentComment'] = true;
			} else{
				$arResult['canCommentComment'] = false;
			}
		}
	}
	$arUGroups = $USER->GetUserGroupArray();
	$arIntersect = array_intersect($arParams['MODER_GR'], $arUGroups);
	if(count($arIntersect)>0){
		$arResult['canModerate'] = true;
		$arResult['canComments'] = true;
		if($arParams['WRITE_COM_COM']){
			$arResult['canCommentComment'] = true;
		}
	}
	// --------------------------------------------------------

	require_once('classComments.php');

	// добавляем или изменяем комментарий
	if(!empty($_POST['arCommentFields']) && $_POST['arCommentFields']['DEL']!='Y'){
		unset($_POST['arCommentFields']['DEL']); // убираем поле DEL если попалось

		if($_POST['arCommentFields']['SET_STATUS']!='Y'){
			// валидируем
			if ($arResult["USE_CAPTCHA"] == "Y") {
		        if (! $APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
		            $arResult["errors"][] = "Неверно введено слово с картинки";
		        }
		    }
			if($_POST['arCommentFields']['IBLOCK_ELEMENT_ID']==''){
				$arResult['errors'][] = 'Комментарий не привязан к элементу';
			}
			if($_POST['arCommentFields']['AUTHOR_NAME']==''){
				$arResult['errors'][] = 'Не заполнено поле "Имя"';
			}
			if($_POST['arCommentFields']['AUTHOR_EMAIL']==''){
				$arResult['errors'][] = 'Не заполнено поле "E-Mail"';
			}
			if($_POST['arCommentFields']['TEXT']==''){
				$arResult['errors'][] = 'Не заполнено поле "Комментарий"';
			}
		}


		if($arParams['PRE_MODER']=='Y' && ($_POST['arCommentFields']['STATUS']=='' || !isset($_POST['arCommentFields']['STATUS']))){
			$_POST['arCommentFields']['STATUS'] = 'N';
		} elseif($_POST['arCommentFields']['STATUS']==''){
			$_POST['arCommentFields']['STATUS'] = 'P';
		}

		if(empty($arResult['errors'])){
			$_POST['arCommentFields']['DATE'] = ($_POST['arCommentFields']['DATE']!='')?$_POST['arCommentFields']['DATE']:ConvertTimeStamp(getmicrotime(), 'FULL');
			unset($_POST['arCommentFields']['SET_STATUS']);
			$added = WeComments::Add($_POST['arCommentFields']);
			if(!$added){
				$op = ($_POST['arCommentFields']['ID']>0)?'изменении':'добавлении';
				$arResult['err'][] = 'При '.$op.' комментария возникла ошибка';
			} else{
				// отправим письмо
				if($arParams['SEND_MAIL']=='Y'){
					$flds['HREF'] = $_SERVER['HTTP_HOST'].$APPLICATION->GetCurPage().'#com'.$added;
					CEvent::Send('WE_COMMENTS_ADDED', SITE_ID, $flds, "N");
				}

				$obMenuCache->CleanDir(); // сбрасываем кеш
				LocalRedirect($APPLICATION->GetCurPage());
			}
		}
	}

	// удаляем комментарий
	if($_POST['arCommentFields']['DEL']=='Y'){
		if(!WeComments::Del($_POST['arCommentFields']['ID'])){
			$arResult['err'][] = 'При удалении комментария возникла ошибка';
		} else{
			$gl = WeComments::GetList(array('DATE'=>'DESC'), array('PARENT_ID'=>$_POST['arCommentFields']['ID']));
			while($arGl = $gl->GetNext()){
				$dar[] = $arGl['ID'];
			}
			if(!empty($dar)){
				foreach ($dar as $id){
					if(!WeComments::Del($id)){
						$arResult['err'][] = 'При удалении подкомментария возникла ошибка';
					}
				}
			}
			$obMenuCache->CleanDir(); // сбрасываем кеш
			if(empty($arResult['err'])){
				LocalRedirect($APPLICATION->GetCurPage());
			}
		}
	}

	if(!empty($arResult['errors'])){
		$obMenuCache->CleanDir(); // сбрасываем кеш
	}

	// ----- выборка идет в PHP кеш -------
	// ------------------------------------
	if ($obMenuCache->StartDataCache($arParams["CACHE_TIME"], $CACHE_ID, $CACHE_DIR)) {
		// достаем комментарии ---------------------------------------------
		$arNavStartParams = false;
		if($arParams['nPageSize']>0)
			$arNavStartParams['nPageSize'] = $arParams['nPageSize'];

		$res = WeComments::GetList(array('DATE'=>'ASC'), array('IBLOCK_ELEMENT_ID'=>$arParams['ELEMENT_ID']), $arNavStartParams);
		while ($arItm = $res->Fetch()) {
			if($arItm['STATUS']=='')
				$arItm['STATUS'] = 'P';
			$arItm['DATE_PARSE'] = ((int)$arItm['DATE']>0)?ParseDateTime($arItm['DATE'], 'YYYY-MM-DD HH:MI:SS'):'';
			$arItm['DATE_FORMAT'] = ConvertTimeStamp(mktime($arItm['DATE_PARSE']['HH'], $arItm['DATE_PARSE']['MI'], $arItm['DATE_PARSE']['SS'], $arItm['DATE_PARSE']['MM'], $arItm['DATE_PARSE']['DD'], $arItm['DATE_PARSE']['YYYY']), 'FULL');
			$arResult['COMMENTS'][ $arItm['ID'] ] = $arItm;
		}

			// постраничим ---
		if($arParams['nPageSize']>0){
			if($arPrarms['MY_TEMPLATE_PAGER']!='Y'){
				$arResult['NAV_STRING'] = $res->GetPageNavString(false, $arParams['TEMPLATE_PAGER'], false);
			} else{
				$this->InitComponentTemplate();
				$template = & $this->GetTemplate();
				$templatePath = $template->GetFolder();
				$arResult['NAV_STRING'] = $res->NavPrint('', false, '',  $templatePath.'/pager.php', 'Y');
			}
		}
			// ---------------

		// расставляем комментарии комментариев
		//	$arResult['COMMENTS'] = array();
		$arResult['COMMENTS'] = array_reverse($arResult['COMMENTS'], true);
		for($i=0;$i<$arParams['MAX_DEPTH'];$i++){
			foreach($arResult['COMMENTS'] as $id => $co){
				if($co['PARENT_ID']>0){
					$arResult['COMMENTS'][ $co['PARENT_ID'] ]['COMMENTS'][ $co['ID'] ] = $co;
					if($i+1==$arParams['MAX_DEPTH'])
						unset($arResult['COMMENTS'][ $id ]);
				}
			}
		}
		$obMenuCache->EndDataCache(Array("arResult" => $arResult)); // помним в кеш

	} else {
		$arVars = $obMenuCache->GetVars(); // берем кеш
		$arResult = array_merge($arVars["arResult"], $arResult); // в удобную переменную
	}

	$this->IncludeComponentTemplate();
}
?>