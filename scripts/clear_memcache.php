<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

echo 'clear memcache<br /><br />';

$memcache_obj = new Memcache;
echo 'connect ';
var_dump( $memcache_obj->connect(constant('BX_MEMCACHE_HOST'), constant('BX_MEMCACHE_PORT')) );

echo '<br />flush ';
var_dump( $memcache_obj->flush() );

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>