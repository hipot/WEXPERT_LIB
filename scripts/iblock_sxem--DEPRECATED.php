<?
/**
 * iblock structure first look
 * @version 1.0
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('iblock');

$iblocks = array();
$rs = CIBlock::GetList(array('sort' => 'asc', 'name' => 'asc'), array());
while ($ib = $rs->Fetch()) {
	$iblocks[ $ib['ID'] ] = array(
		'ID'				=> $ib['ID'],
		'NAME'				=> $ib['NAME'],
		'IBLOCK_TYPE_ID'	=> $ib['NAME'],
	);
}

$relayProps			= array();
$iblockRelayProps	= array();
$rs = CIBlockProperty::GetList(array('IBLOCK_ID' => 'ASC'), array('PROPERTY_TYPE' => 'E'));
while ($pr = $rs->Fetch()) {
	$relayProps[ $pr['ID'] ] = array(
		'ID'	=> $pr['ID'],
		'NAME'	=> $pr['NAME'],
		'CODE'	=> $pr['CODE'],
	);

	if (intval($pr['LINK_IBLOCK_ID']) == 0) {
		$pr['LINK_IBLOCK_ID'] = 'WTF';
	}

	$iblockRelayProps[ $pr['IBLOCK_ID'] ][] = array(
		'TO'		=> $pr['LINK_IBLOCK_ID'],
		'LINK_ID'	=> $pr['ID']
	);
}

foreach ($iblocks as $ibid => $v) {
	if (! isset($iblockRelayProps[$ibid])) {
		$iblockRelayProps[$ibid] = array();
	}
}
?>

<!DOCTYPE PUBLIC>
<html>
<head>
	<title>Связи инфоблоков (первый взгляд)</title>
	<style type="text/css">
	* {font-family:monospace;}
	</style>
</head>
<body>

<table width="50%" align="center">
<tr valign="top">
	<td width="50%">
		<h4>Связи инфоблоков (первый взгляд)</h4>
		<?
		foreach ($iblockRelayProps as $iblock => $relayEx) {

			?>
			<p>
			<b><?=$iblocks[ $iblock ]['NAME']?> (<?=$iblocks[ $iblock ]['ID']?>)</b><br />
			<?

			foreach ($relayEx as $relay) {
				?>
				<small><?=$relayProps[ $relay['LINK_ID'] ]['NAME']?> [<?=$relayProps[ $relay['LINK_ID'] ]['CODE']?>]</small>
				&mdash;&gt; <b><?=$iblocks[ $relay['TO'] ]['NAME']?> (<?echo ($relay['TO'] == 'WTF' ? '<font style="color:red">' : ''); echo $relay['TO']; echo ($relay['TO'] == 'WTF' ? '</font>' : '');?>)</b><br />
				<?
			}

			?>
			</p>
			<?
		}
		?>
	</td>
<tr>
</table>

</body>
</html>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>