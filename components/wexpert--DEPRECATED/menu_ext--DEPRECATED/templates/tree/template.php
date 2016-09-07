<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach($arResult as $k=>$v){
	if(!empty($v['PARAMS'])){
		$arResult[$k] = array_merge($arResult[$k], $v['PARAMS']);
		unset($arResult[$k]['PARAMS']);
	}
}
$curDir = $APPLICATION->GetCurDir();
function menuFormatter($ar,$curDir) {
	echo '<ul>';

	for($k = 0; $k < sizeof($ar); $k++){
		$cl='';$class='';$scl='';$sub_class='';
		if($ar[$k]['SELECTED']){
			$scl .= ' sel';
		}
		if($ar[$k]['IS_PARENT']){
			$cl .= ' parent';
			if($ar[$k]['SELECTED']){
				$cl .= ' min';
			}
		}
		if(strlen($cl)>0){
			$class='class="'.trim($cl).'"';
		}
		if(strlen($scl)>0){
			$sub_class='class="'.trim($scl).'"';
		}
		if($ar[$k]['LINK']==$curDir){
			$pre='<span '.$sub_class.'>';$post='</span>';
		} else{
			$pre='<a '.$sub_class.' href="'.$ar[$k]['LINK'].'">';$post='</a>';
		}
		echo '<li '.$class.'>';
		if($ar[$k]['IS_PARENT']){
			echo '<div></div>';
		}
		if(isset($ar[$k + 1]) && $ar[$k]['DEPTH_LEVEL'] < $ar[$k + 1]['DEPTH_LEVEL']){
			echo $pre.$ar[$k]['TEXT'].$post;

			$stoper = $ar[$k]['DEPTH_LEVEL'];
			$ares = array();
			$k++;
			while($ar[$k]['DEPTH_LEVEL'] > $stoper && $k < sizeof($ar)){
				$ares[] = $ar[$k];
				$k++;
			}$k--;

			echo menuFormatter($ares,$curDir);

		} else{
			echo $pre.$ar[$k]['TEXT'].$post;
		}
		echo '</li>';
	}

	echo '</ul>';
}
?>

<div class="menu-right">
<? menuFormatter($arResult,$curDir)?>
</div>
