<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/* @var $this CBitrixComponent */
?>


<table class="pickpoint" style="border-collapse:collapse;" align="center">
	<tbody>
		<tr>
			<td style="border-image: initial;">
				<p>
					<b>№№</b>
				</p>
			</td>
			<td style="border-image: initial;">
				<p>
					<b>Город</b>
				</p>
			</td>
			<td style="border-image: initial;">
				<p>
					<b>Область</b>
				</p>
			</td>
			<td style="border-image: initial;">
				<p align="center">
					<b>Срок доставки</b>
				</p>
			</td>
			<td style="border-image: initial;">
				<p align="center">
					<b>Цена доставки</b>
				</p>
			</td>
		</tr>

<?foreach ($arResult['ITEMS'] as $k => $item) {?>
		<tr>
			<td style="border-image: initial;">
				<p align="center"><?=++$k?></p>
			</td>
			<td style="border-image: initial;">
				<p><?=$item['CITY_NAME_RU']?></p>
			</td>
			<td style="border-image: initial;">
				<p><?=$item['REGION_RU']?></p>
			</td>
			<td align="center" valign="middle" style="border-image: initial;">
				<p align="center"><?=$item['CNT_DAYS']?></p>
			</td>
			<td style="border-image: initial;">
				<p align="center"><?=FormatCurrency($item['PRICE'], 'RUB')?></p>
			</td>
		</tr>
<?}?>

</tbody>
</table>