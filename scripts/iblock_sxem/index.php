<?
/**
 * iblock structure first look
 * @version 2.X
 * @author 2015, wexpert.ru
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$VERSION = '2.2';

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
$allProps			= array();

$rs = CIBlockProperty::GetList(array('sort' => 'asc', 'NAME' => 'ASC'), array(/*'PROPERTY_TYPE' => 'E'*/));
while ($pr = $rs->Fetch()) {

	$allProps[ $pr['IBLOCK_ID'] ][ $pr['ID'] ] = $pr;

	if ($pr['PROPERTY_TYPE'] != 'E') {
		continue;
	}

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
	<title>Связи инфоблоков (первый взгляд <?=$VERSION?>)</title>
	<style type="text/css">
	* {font-family:consolas,monospace;}
	.graph_canva {width:90%; height:800px; margin:0px auto;}
	ul {padding-top:0px; margin-top:0px;}
	</style>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script type="text/javascript" src="cytoscape/arbor.js"></script>
	<script type="text/javascript" src="cytoscape/cytoscape.min.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
</head>
<body>

<table width="70%" align="center">
<tr valign="top">
	<td width="40%">
		<h3>Связи инфоблоков (первый взгляд <?=$VERSION?>)</h3>
		<?
		foreach ($iblockRelayProps as $iblock => $relayEx) {

			if (! isset($iblocks[ $iblock ]['NAME'])) {
				continue;
			}

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
	<td>
		<h3>Все свойства инфоблоков</h3>
		<?foreach ($iblocks as $id => $iblock) {
			$ibPrs = $allProps[ $iblock['ID'] ];
			?>
			<p>
				<b><?=$iblock['NAME']?> (<?=$iblock['ID']?>)</b>

				<?if (count($ibPrs) > 0) {?>
				<small><ul>
					<?foreach ($ibPrs as $pr) {?>
						<li><?=$pr['NAME']?> (<?=$pr['ID']?>, <b><?=$pr['CODE']?></b>) [<?=$pr['PROPERTY_TYPE']?>]</li>
					<?}?>
				</ul></small>
				<?}?>
			</p>
		<?}?>
	</td>
<tr>
</table>

<script type="text/javascript">
$(function(){

	var nodes = [
		<?foreach ($iblocks as $iblock => $bl) {?>
			{
				data: { id: '<?=$iblock?>', name: "<?=addslashes( $bl['NAME'] . ' (' . $bl['ID'] . ')'  )?>", weight: 65, height: 174 }
			} <?if ($k < count($iblocks) -1 ) {?>,<?}?>
		<?}?>
	];

	var edges = [
		<?foreach ($iblockRelayProps as $iblock => $relayEx) {
			foreach ($relayEx as $relay) {
				if (! isset($iblocks[ $relay['TO'] ])) {
					continue;
				}
				?>
				{
					data: {
						source: '<?=$iblock?>', target: '<?=$relay['TO']?>',
						'sash' : 'none', 'width' : 4, label : '<?=$allProps[ $iblock ][ $relay['LINK_ID'] ]['CODE']?>'
					}
				},
				<?
			}
		}?>
	];

	var options = {
		showOverlay: false,
		minZoom: 0.5,
		maxZoom: 2,
		zoomingEnabled: false,

		layout: arbor_layout,

		style: cytoscape.stylesheet()
			.selector('node').css({
				'content': 'data(name)',
				'font-family': 'consolas,monospace',
				'font-size': 12,
				'text-outline-width': 0,
				'text-outline-color': '#000',
				'text-opacity': 0.9,
				'text-valign': 'center',
				'color': '#000',
				'width': 'mapData(weight, 30, 80, 20, 50)',
				'height': 'mapData(height, 0, 200, 10, 45)',
				'border-color': '#fff',
				'background-color': '#888'
			})
			.selector(':selected').css({
			})
			.selector('edge').css({
				'width': 'data(width)',
				'target-arrow-shape': 'triangle',
				'source-arrow-shape': 'data(sash)',
				'line-color': '#a9cda9',
				'source-arrow-color': '#a9cda9',
				'target-arrow-color': '#a9cda9',
				'content': 'data(label)',
				'edge-text-rotation': 'autorotate',
				'font-family': 'consolas,monospace',
				'font-size': 11,
				'color': '#888'
			}),

		elements: {
			nodes: nodes,
			edges: edges
		}
	};

	$('#graph').cytoscape(options);
});
</script>


<div id="graph" class="graph_canva"></div>


</body>
</html>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>