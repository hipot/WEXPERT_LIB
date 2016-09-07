<? require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');?>

<?

if (!function_exists('_getCollectionParentsNames')) {
	function _getCollectionParentsNames($parentId, $arColIndexed)
	{
		$name = '';
		if (! isset($arColIndexed[ $parentId ])) {
			return $name;
		}
		$name = $arColIndexed[ $parentId ]['NAME'] . ' / ';
		if ($arColIndexed[ $parentId ]['PARENT_ID'] > 0) {
			$name = _getCollectionParentsNames($arColIndexed[ $parentId ]['PARENT_ID'], $arColIndexed) . $name;
		}
		return $name;
	}
}
//if(!function_exists('_scandir')){
	function _scandir($path,$colId){
		$path = $_SERVER['DOCUMENT_ROOT'].$path;
		if(file_exists($path) && is_dir($path)){
			$hndl = opendir($path);
			while(false !== ($file = readdir($hndl))){
				// исключаем папки с назварием '.' и '..'
				if($file != '.' && $file != '..'){
					$tmp = $path . '/' . $file;
					chmod($tmp, 0777);
					if(is_dir($tmp)){ // если папка
						if($_POST['col_by_folder']=='y'){ // чекер - Создавать подколекции
							if(strlen($tmp) == strrpos($tmp,'/')){
								$tmp2 = substr($tmp,0,strlen($tmp)-1);
							} else{
								$tmp2 = $tmp;
							}
							$arN = explode('/',$tmp2);
							$name = array_pop($arN);
							// создаю новую подколлекцию
							$new_coll = CMedialibCollection::Edit(array(
							                               'arFields'=>array(
								                               'OWNER_ID' => $GLOBALS['USER']->GetId(),
								                               'ACTIVE' => 'Y',
								                               'NAME' => $name,
								                               'PARENT_ID' => $colId
							                               )
							                          ));
							_scandir(str_replace($_SERVER['DOCUMENT_ROOT'],'',$tmp),$new_coll);
						} else{
							_scandir(str_replace($_SERVER['DOCUMENT_ROOT'],'',$tmp),$colId);
						}

					}
					else{
						if(file_exists($tmp) && getimagesize($tmp)){
							// добавляем элемент $tmp в коллекцию $_POST['medialib_cat']
							$path1 = str_replace($_SERVER['DOCUMENT_ROOT'],'',$tmp);
							$arN = explode('/',$path1);
							$name = array_pop($arN);
							$name = substr($name,0,strpos($name,'.'));
							$ar = CMedialibItem::Edit(array(
							                         'path'=>$path1,
							                         'arFields'=>array(
								                        'NAME' => $name
							                         ),
							                         'arCollections' => array($colId)
							                     ));
						}
					}
				}
			}
			closedir($hndl);
			// удаляем текущую папку
			if(file_exists($path)){
				rmdir($path);
			}
		}
		else{
			return false;
		}
	}
//}

CModule::IncludeModule("fileman");
CMedialib::Init();

$rsCol = CMedialibCollection::GetList(array(
	'arOrder'	=> array('ML_TYPE' => 'ASC')
));
foreach ($rsCol as $ar) {
	$arColIndexed[ $ar['ID'] ] = $ar;
}
foreach ($rsCol as $ar) {
	$arTypesEx[ $ar['ID'] ] = (_getCollectionParentsNames($ar['PARENT_ID'], $arColIndexed)) . $ar['NAME'] . ' [' . $ar['ID'] . '] ';
}
uasort($arTypesEx, create_function('$a, $b', '
	if ($a == $b) {
		return 0;
	}
	return ($a < $b) ? 1 : -1;'));
unset($rsCol, $arColIndexed);



if(trim($_POST['path'])!='' && $_POST['medialib_cat']){
	$PATH = $_SERVER['DOCUMENT_ROOT'].$POST['path'];
	if(is_dir($PATH) && is_readable($PATH)){
		// читаю все внутренности и добавляю в $_POST['medialib_cat'] картинки
		_scandir($_POST['path'],$_POST['medialib_cat']);
		$READY=true;
		$READY_COL_PATH = '/bitrix/admin/fileman_medialib_admin.php';
	} else{
		$ERRORS[] = 'Неверно указан путь к папке, или такой папки не существует';
	}
}

?>




<?
$APPLICATION->SetTitle('Перемещение файлов из папок в медиабиблиотеку');
if(!$_POST['medialib_cat']){
	$_POST['medialib_cat'] = 310;
}
?>
<br clear="all" />
<? if(!empty($ERRORS)){
	echo '<ul style="color:red;"><li>'.inplode('</li><li>',$ERRROS).'</li></ul>';
} elseif($READY){
	echo '<span style="color:green; font-size:18px;">Перемещение завершено! Можете <a href="'.$READY_COL_PATH.'">проверить</a>.</span><br clear="all" />';
}?>
<br clear="all" />
<form method="POST" style="float:left;">
	<label for="path">
		Папка с картинками <i style="color:#ccc;">(относительно корня сайта)</i><br>
		<input style="width:98%;" type="text" name="path" id="path" value="<?=$_POST['path']?>">
	</label>
	<br clear="all" />
	<br clear="all" />
	<label for="medialib_cat">
		Категория медиабиблиотеки<br>
		<select name="medialib_cat" id="medialib_cat">
			<?
			$parentDepth = array();
			foreach($arTypesEx as $id=>$nm):
				$parentDepth[ $coll['PARENT_ID'] ]=1;
				?>
				<option <?=($_POST['medialib_cat']==$id)?'selected':'';?> value="<?=$id?>"><?=$nm?></option>
			<?endforeach;?>
		</select>
	</label>
	<br clear="all" />
	<br clear="all" />
	<label for="col_by_folder">
		<input style="margin-right:12px;" type="checkbox" name="col_by_folder" id="col_by_folder" <?=($_POST['col_by_folder']=='y')?'checked':'';?> value="y"> Создавать подколекции если в указанной папке встретятся подпапки
	</label>
	<br clear="all" />
	<br clear="all" />
	<input type="submit" value="ПЕРЕМЕСТИТЬ" style="padding:2px 5px;">
</form>
<br clear="all" />
<br clear="all" />




<?



require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
