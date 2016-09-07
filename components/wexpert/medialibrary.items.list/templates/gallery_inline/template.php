<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>

<?if (count($arResult['ITEMS']) > 0) {?>
<div class="wr-gal">
	<div class="gal-new">
		<div id="slides2">
			<div class="slides_container">
				<?foreach ($arResult['ITEMS'] as $item) {
					if ($item['TYPE'] != 'image') {
						continue;
					}
					?>
					<div class="item">
						<img src="<?=CImg::Resize($item['PATH'], 451, 301, Cimg::M_CROP, array(false, false))?>" alt="<?=htmlspecialcharsEx($item['NAME'])?>" />
					</div>
				<?}?>
			</div>
		</div>
	</div>
	<!--gal-new-->
</div>
<!--wr-gal-->
<?}?>