<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!$this->NavShowAlways)
{
	if ($this->NavRecordCount == 0 || ($this->NavPageCount == 1 && $this->NavShowAll == false))
		return;
}

//echo "<pre>"; print_r($this->);echo "</pre>";

$strNavQueryString = ($this->NavQueryString != "" ? $this->NavQueryString."&amp;" : "");
$strNavQueryStringFull = ($this->NavQueryString != "" ? "?".$this->NavQueryString : "");
?>
<font class="text">

	<?if ($this->NavPageNomer > 1):?>
		<a href="<?=$strNavQueryString?>">первая</a>
		|
		<?if($this->NavPageNomer > 0):?>
			<?if($this->NavPageNomer - $this->nPageWindow < 1):?>
				<a href="<?=$strNavQueryString?>">группа назад</a>&nbsp;|
			<?else:?>
			    <a href="<?=$strNavQueryString?>page<?echo $this->NavPageNomer-$this->nPageWindow; ?>/">группа назад</a>&nbsp;|
			<?endif;?>
		<?endif;?>
		<?if(($this->NavPageNomer!=2)):?>
			<a href="<?=$strNavQueryString?>page<?=($this->NavPageNomer-1)?>/">пред.</a>
		<?else:?>
			<a href="<?=$strNavQueryString?>">пред.</a>
		<?endif?>
		|
	<?else:?>
		первая&nbsp;|&nbsp;группа назад&nbsp;|&nbsp;пред.&nbsp;|
	<?endif;?>

	<?while($this->nStartPage <= $this->nEndPage):?>

		<?if ($this->nStartPage == $this->NavPageNomer):?>
			<b><?=$this->nStartPage?></b>
		<?elseif($this->nStartPage == 1 ):?>
			<a href="<?=$strNavQueryString?>"><?=$this->nStartPage?></a>
		<?else:?>
			<a href="<?=$strNavQueryString?>page<?=$this->nStartPage?>/"><?=$this->nStartPage?></a>
		<?endif?>
		<?$this->nStartPage++?>
	<?endwhile?>
	|
	<?if($this->NavPageNomer < $this->NavPageCount):?>
		<a href="<?=$strNavQueryString?>page<?=($this->NavPageNomer+1)?>/">след.</a>&nbsp;|
		<?if($this->NavPageNomer + $this->nPageWindow > $this->NavPageCount):?>
		    &nbsp;<a href="<?=$strNavQueryString?>page<?=$this->NavPageCount?>/">группа вперед</a>&nbsp;|
		<?else:?>
		    &nbsp;<a href="<?=$strNavQueryString?>page<?echo $this->NavPageNomer+$this->nPageWindow; ?>/">группа вперед</a>&nbsp;|
		<?endif;?>
		<a href="<?=$strNavQueryString?>page<?=$this->NavPageCount?>/">последняя</a>
	<?else:?>
		след.&nbsp;|&nbsp;группа вперед&nbsp;|&nbsp;последняя
	<?endif?>

</font>
