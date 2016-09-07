<table>
	<?foreach($FORM->cfg['f'] as $v): if($v['type']=='submit')continue;?>
	<tr>
		<td>
			<?=$v['label']?>
		</td>
		<td>
			<?=$v['value']?>
		</td>
	</tr>
	<? endforeach;?>
</table>
