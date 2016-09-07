<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/* @var $this CBitrixComponentTemplate */


$this->SetViewTarget('RIGHT_BLOCK_ALSO_LINK');
?>

<div class="b-inf">
	<p><span>Информация по теме</span></p>
	Статьи, тонкости, рекомендации
</div><!--b-inf-->

<?
$this->EndViewTarget();
?>


<div id="evaluation" class="pop2 upform">
	<div class="cont-pop">
		<div class="close"></div>
		<h1>Информация по теме</h1>
		<p>Здесь вы можете ознакомиться полезной информацией.</p>

		<div class="info-list">
		<ul>


			<li><ul>
						<?
						$half = ceil(count($arResult["SEARCH"]) / 2);
						$isN = true;

						foreach ($arResult["SEARCH"] as $k => $it) {?>

							<?if (++$k % $half == 0 && $isN && $half > 2) {?>
								</ul></li>
								<li><ul>
								<?
								$isN = false;
							}?>

							<li><a href="<?=$it['URL']?>"><?=$it['TITLE']?></a></li>
						<?}?>
			</ul></li>


		</ul>
		</div><!--info-list-->

	</div><!--cont-pop-->
</div><!--pop2-->