<?
/* Поля таблицы we_comments
 * ID – автоинкремент
 * DATE – дата и время добавления комментария
 * IBLOCK_ELEMENT_ID – ID элемента инфоблоков, который комментируется
 * PARENT_ID – ID комментария, который является родительским для текущего комментария.
 * TEXT – текст комментария
 * AUTHOR_NAME – имя автора
 * AUTHOR_EMAIL – емайл автора
 * USER_ID – ID пользователя в системе битрикс
 * STATUS – P (Published - опубликован), H (Hidden - скрыт от посетителей), N (New - Новый, не проверен)
 */
/**
 * Класс для работы с комментариями
 * @author Матяш Сергей WebExpert
 */
class WeComments {


	/**
	 * Возвращает количество комментариев для элемента $ELEMENT_ID
	 * @param $ELEMENT_ID
	 * @return bool|int
	 */
	public static function GetCntByEl($ELEMENT_ID, $STATUS='P'){

		$arFilter = array('IBLOCK_ELEMENT_ID'=>$ELEMENT_ID, 'STATUS' => $STATUS);

		$arDB = self::GetList(array('DATE'=>'DESC'), $arFilter);
		if(!$arDB){
			return false;
		} else{
			return $arDB->SelectedRowsCount();
		}
	}

	/**
	 * Возвращает массив комментариев элемента $ELEMENT_ID
	 * @param $ELEMENT_ID Идентификатор элемента
	 * @return array|bool
	 */
	public static function GetByEl($ELEMENT_ID){
		$return = false;
		$arDB = self::GetList(array('DATE'=>'DESC'), array('IBLOCK_ELEMENT_ID'=>$ELEMENT_ID));
		if(!$arDB){
			return false;
		} else{
			while($db = $arDB->GetNext()){
				$return[] = $db;
			};
		}
		return $return;
	}

	/**
	 * Выводит список комментариев
	 * @param {array=array('DATE'=>'DESC')} $arOrder Сортировка ('поле'=>'направление')
	 * @param {array=flase} $arFilter Фильтр ('поле'=>'значение(может быть и массивом)')
	 * @param {array=false} $arNavStartParams ('nTopCount'-ограничить количество, 'nPageSize'-элементов на страницу)
	 */
	public static function GetList($arOrder=array('DATE'=>'DESC'), $arFilter=false, $arNavStartParams=false) {
		/* @var $DB CDatabase */
		global $DB;
		$strWhere = '';
		if(is_array($arFilter) && !empty($arFilter)){
			$strWhere = 'WHERE ';
			$cc=0;
			foreach ($arFilter as $k=>$v){

				if ($cc>0)
					$strWhere .= ' AND ';

				$strWhere .= ' ';
				$k = $DB->ForSql($k);
				if(is_array($v)){
					$ck=0;
					foreach ($v as $vl){
						$vl = $DB->ForSql($vl);
						if($ck>0)
							$strWhere .= ' OR ';
						$strWhere .= $k .' = '.$vl;
						$ck++;
					}
				} else {
					$v = $DB->ForSql($v);
					$strWhere .= $k .' = "'. $v.'"';
				}
				$cc++;
			}
			$strWhere .= ' ';
		}

		$strOrderBy = '';
		if(is_array($arOrder) && !empty($arOrder)){
			$strOrderBy = 'ORDER BY ';
			$c=0;
			foreach ($arOrder as $k=>$v){
				if($c>=3) break;
				if($c>0)
					$strOrderBy .= ', ';

				$k = (trim($k)!='')?$DB->ForSql($k):'ID';
				$v = (trim($v)!='' && (strtoupper($v)=='ASC' || strtoupper($v)=='DESC' ))?$DB->ForSql($v):'ASC';
				$strOrderBy .= strtoupper($k) .' '. strtoupper($v);

				$c++;
			}
		}

		if(is_array($arNavStartParams)){
			$nTopCount = $arNavStartParams['nTopCount'];
			$nPageSize = $arNavStartParams['nPageSize'];
			$iNumPage = $arNavStartParams['iNumPage'];

			if($nTopCount > 0){
				$strSql = 'SELECT * FROM we_comments '.$strWhere.$strOrderBy.' LIMIT '.$nTopCount;
				$res = $DB->Query($strSql);
			} else{
				$strSQL = 'SELECT * FROM we_comments '.$strWhere.$strOrderBy;
				$res = $DB->Query($strSQL);

				// инициализация постранички
				if($nPageSize>0){
					$res->NavStart($nPageSize, false, $iNumPage);
				}
			}
		} else{
			$strSQL = 'SELECT * FROM we_comments '.$strWhere.$strOrderBy;
			$res = $DB->Query($strSQL);
		}

		return $res;
	}

	/**
	 * Добавляет комментарий
	 * @param {array} $arFields Массив с полями, если указано поле ID, то вместо добавления производится обновление
	 */
	public static function Add($arFields){
		if(is_array($arFields) && !empty($arFields)){
			/* @var $DB CDatabase */
			global $DB;
			if($arFields['IBLOCK_ELEMENT_ID']<=0){
				echo ShowError('Ошибка! Не указан элемент для привязки комментария');
			}
			$DB->PrepareFields("we_comments");

			$arInsert = $DB->PrepareInsert('we_comments', $arFields);

			if($arFields['ID']>0){
				// обновление
				$strSet = '';
				$c=0;
				foreach($arFields as $k => $v){
					if(trim($v)=='' || trim($k)==''){
						unset($arFields[ $k ]);
					} else{

						if($c>0)
							$strSet .= ', ';
						if($k=='DATE'){
							if (trim($v)==''){
								$v = ConvertTimeStamp(getmicrotime(), 'FULL');
							}
							$strSet .= $DB->ForSql($k) .'="'. ConvertDateTime($v, 'YYYY-MM-DD HH:MI:SS') .'"';
						} else{
							$strSet .= $DB->ForSql($k) .'="'. $DB->ForSql($v) .'"';
						}
						$c++;
					}
				}
				$strSql = 'UPDATE we_comments SET '.$strSet.' WHERE ID='.$arFields['ID'].' LIMIT 1';
				$res = $DB->Query($strSql);
				if($res){
					return $arFields['ID'];
				} else{
					return false;
				}
			} else{
				// добавление
				$strSql = 'INSERT INTO we_comments ('.$arInsert[0].') VALUES ('.$arInsert[1].')';
				$res = $DB->Query($strSql);
				if($res){
					return intval($DB->LastID());
				} else{
					return false;
				}
			}
		} else{
			return false;
		}
	}

	/**
	 * Удаляет комментарий
	 * @param {int} $ID Идентификатор комментария
	 */
	public static function Del($ID){
		if($ID>0){
			/* @var $DB CDatabase */
			global $DB;
			$strSql = 'DELETE FROM we_comments WHERE ID='.$DB->ForSql($ID);
			if($DB->Query($strSql)){
				return true;
			} else{
				return false;
			}
		} else{
			return false;
		}
	}
}


/* Поля таблицы we_comments_rate
 * ID – автоинкремент
 * DATE – дата и время добавления комментария
 * COMMENT_ID – ID комментария который рэйтится
 * RATE – уровень рейтинга (1 - хорошо, 0 - плохо, промежуточные значения - на будущее)
 * IP_ADRESS – IP адресс автора рейтинга
 */
/**
 * Класс для работы с рейтингами комментариев
 * @author Матяш Сергей WebExpert
 */
class WeCommentsRate {

	/**
	 * Выбирает рейтинги для комментария с идентификатором $COMMENT_ID
	 * @param $COMMENT_ID
	 * @return array('BAD'=>array(), 'GOOD'=>array());
	 */
	public static function GetByComment($COMMENT_ID){
		$return = false;
		$arDB = self::GetList(array('DATE'=>'DESC'), array('COMMENT_ID' => $COMMENT_ID));
		if(!$arDB){
			return false;
		} else{
			while($rDB = $arDB->GetNext()){
				if($rDB['RATE'] == 1){
					$return['GOOD'][] = $rDB;
				} else{
					$return['BAD'][] = $rDB;
				}
			}
		}
		return $return;
	}

	/**
	 * Выводит список рейтингов
	 * @param {array=array('DATE'=>'DESC')} $arOrder Сортировка ('поле'=>'направление')
	 * @param {array=flase} $arFilter Фильтр ('поле'=>'значение(может быть и массивом)')
	 * @param {array=false} $arNavStartParams ('nTopCount'-ограничить количество, 'nPageSize'-элементов на страницу)
	 */
	public static function GetList($arOrder=array('DATE'=>'DESC'), $arFilter=false, $arNavStartParams=false) {
		/* @var $DB CDatabase */
		global $DB;
		$strWhere = '';
		if(is_array($arFilter) && !empty($arFilter)){
			$strWhere = 'WHERE ';
			$cc=0;
			foreach ($arFilter as $k=>$v){

				if ($cc>0)
					$strWhere .= ' AND ';

				$strWhere .= ' ';
				$k = $DB->ForSql($k);
				if(is_array($v)){
					$ck=0;
					foreach ($v as $vl){
						$vl = $DB->ForSql($vl);
						if($ck>0)
							$strWhere .= ' OR ';
						$strWhere .= $k .' = '.$vl;
						$ck++;
					}
				} else {
					$v = $DB->ForSql($v);
					$strWhere .= $k .' = "'. $v.'"';
				}
				$cc++;
			}
			$strWhere .= ' ';
		}

		$strOrderBy = '';
		if(is_array($arOrder) && !empty($arOrder)){
			$strOrderBy = 'ORDER BY ';
			$c=0;
			foreach ($arOrder as $k=>$v){
				if($c>=3) break;
				if($c>0)
					$strOrderBy .= ', ';

				$k = (trim($k)!='')?$DB->ForSql($k):'ID';
				$v = (trim($v)!='' && (strtoupper($v)=='ASC' || strtoupper($v)=='DESC' ))?$DB->ForSql($v):'ASC';
				$strOrderBy .= strtoupper($k) .' '. strtoupper($v);

				$c++;
			}
		}

		if(is_array($arNavStartParams)){
			$nTopCount = $arNavStartParams['nTopCount'];
			$nPageSize = $arNavStartParams['nPageSize'];

			if($nTopCount > 0){
				$strSql = 'SELECT * FROM we_comments_rate '.$strWhere.$strOrderBy.' LIMIT '.$nTopCount;
				$res = $DB->Query($strSql);
			} else{
				$strSQL = 'SELECT * FROM we_comments_rate '.$strWhere.$strOrderBy;
				$res = $DB->Query($strSQL);
				// инициализация постранички
				if($nPageSize>0){
					$res->NavStart($nPageSize);
				}
			}
		} else{
			$strSQL = 'SELECT * FROM we_comments_rate '.$strWhere.$strOrderBy;
			$res = $DB->Query($strSQL);
		}

		return $res;
	}

	public static function Add($arFields){
		if(is_array($arFields) && !empty($arFields)){
			/* @var $DB CDatabase */
			global $DB;
			if($arFields['COMMENT_ID']<=0){
				echo ShowError('Ошибка! Не указан комментарий для привязки рейтинга');
			}
			if($arFields['IP_ADRESS']==''){
				echo ShowError('Ошибка! Не указан отправитель.');
			}
			if(!isset($arFields['RATE'])){
				echo ShowError('Ошибка! Не указан рейтинг.');
			}
			$DB->PrepareFields("we_comments_rate");

			$arInsert = $DB->PrepareInsert('we_comments_rate', $arFields);

			if($arFields['ID']>0){
				// обновление
				$strSet = '';
				$c=0;
				foreach($arFields as $k => $v){
					if(trim($v)=='' || trim($k)==''){
						unset($arFields[ $k ]);
					} else{

						if($c>0)
							$strSet .= ', ';
						if($k=='DATE'){
							if (trim($v)==''){
								$v = ConvertTimeStamp(getmicrotime(), 'FULL');
							}
							$strSet .= $DB->ForSql($k) .'="'. ConvertDateTime($v, 'YYYY-MM-DD HH:MI:SS') .'"';
						} else{
							$strSet .= $DB->ForSql($k) .'="'. $DB->ForSql($v) .'"';
						}
						$c++;
					}
				}
				$strSql = 'UPDATE we_comments_rate SET '.$strSet.' WHERE ID='.$arFields['ID'].' LIMIT 1';
				$res = $DB->Query($strSql);
				if($res){
					return $arFields['ID'];
				} else{
					return false;
				}
			} else{
				// добавление
				$strSql = 'INSERT INTO we_comments_rate ('.$arInsert[0].') VALUES ('.$arInsert[1].')';
				$res = $DB->Query($strSql);
				if($res){
					return intval($DB->LastID());
				} else{
					return false;
				}
			}
		} else{
			return false;
		}
	}

	/**
	 * Удаляет все рейтинги комментария с идентификатором $COMMENT_ID
	 * @param $COMMENT_ID Идентификатор комментария
	 * @return bool
	 */
	public static function Del($COMMENT_ID){
		if($COMMENT_ID>0){
			/* @var $DB CDatabase */
			global $DB;
			$strSql = 'DELETE FROM we_comments_rate WHERE COMMENT_ID='.$DB->ForSql($COMMENT_ID);
			if($DB->Query($strSql)){
				return true;
			} else{
				return false;
			}
		} else{
			return false;
		}
	}
}
?>
