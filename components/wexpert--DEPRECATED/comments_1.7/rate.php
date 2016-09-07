<?
if($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest'):
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once('classComments.php');
$ip = $GLOBALS['REMOTE_ADDR'];
if(isset($_REQUEST['comment_id']) || $_REQUEST['comment_id']!=''){
	if($_REQUEST['clear']){
		if(WeCommentsRate::Del($_REQUEST['comment_id'])){
			$echo['cleared'] = 'Y';
		} else{
			$echo['error'] = 'Не удалось очистить рейтинги';
		}
	} else{
		$rsIP = WeCommentsRate::GetList(array('DATE'=>'DESC'), array('IP_ADRESS'=>$ip, 'COMMENT_ID'=>$_REQUEST['comment_id']));
		if($rsIP->SelectedRowsCount() > 0){
			$echo['warny'] = 'Y';
		} else{
			$added = WeCommentsRate::Add(array(
				'DATE'			=> '',
				'COMMENT_ID'	=> $_REQUEST['comment_id'],
				'RATE'			=> $_REQUEST['rate'],
				'IP_ADRESS'		=> $ip
			));
			if($added){
				$rsCom = WeCommentsRate::GetList(array('DATE'=>'DESC'), array('COMMENT_ID'=>$_REQUEST['comment_id'], 'RATE' => $_REQUEST['rate'],));
				$echo['num'] = $rsCom->SelectedRowsCount();
			} else{
				$echo['error'] = 'Коментарий не был добавлен';
			}
		}
	}
} else{
	$echo['error'] = 'Не установлен идентификатор комментария';
}

echo json_encode($echo);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
endif;
?>
