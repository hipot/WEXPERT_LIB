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
 */
/**
 * Класс для работы с комментариями
 * @author Матяш Сергей WebExpert
 */
class WeComments {

	/**
	 * Выводит список комментариев
	 * @param {array=array('DATE'=>'DESC')} $arOrder Сортировка ('поле'=>'направление')
	 * @param {array=flase} $arFilter Фильтр ('поле'=>'значение(может быть и массивом)')
	 * @param {array=false} $arNavStartParams ('nTopCount'-ограничить количество, 'nPageSize'-элементов на страницу)
	 */
	function GetList($arOrder=array('DATE'=>'DESC'), $arFilter=false, $arNavStartParams=false) {
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
					$strWhere .= $k .' = '. $v;
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
				$strSql = 'SELECT * FROM we_comments '.$strWhere.$strOrderBy.' LIMIT '.$nTopCount;
				$res = $DB->Query($strSql);
			} else{
				$strSQL = 'SELECT * FROM we_comments '.$strWhere.$strOrderBy;
				$res = $DB->Query($strSQL);
				// инициализация постранички
				if($nPageSize>0){
					$res->NavStart($nPageSize);
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
	function Add($arFields){
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
				$strSql = 'UPDATE we_comments SET '.$strSet.' WHERE ID='.$arFields['ID'].' LIMIT 1';
				$res = $DB->Query($strSql);
				return $arFields['ID'];
			} else{
				// добавление
				$strSql = 'INSERT INTO we_comments ('.$arInsert[0].') VALUES ('.$arInsert[1].')';
				$res = $DB->Query($strSql);
				return intval($DB->LastID());
			}
		}
	}

	/**
	 * Удаляет комментарий
	 * @param {int} $ID Идентификатор комментария
	 */
	function Del($ID){
		if($ID>0){
			/* @var $DB CDatabase */
			global $DB;
			$strSql = 'DELETE FROM we_comments WHERE ID='.$DB->ForSql($ID);
			if($DB->Query($strSql)){
				return true;
			} else{
				return false;
			}
		}
	}
}
?>