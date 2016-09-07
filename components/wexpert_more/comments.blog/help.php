<?
exit;

$APPLICATION->IncludeComponent('icontext:comments.blog', '', array(
	'CODE'                       => $_REQUEST['CODE'],	// символьный код элемента, к которому комментарии
	'LINK_IB_PROP_CODE'          => 'blog_post_id', // символьный код свойства с привязкой к посту в инфоблоке (число)
	'BLOG_POST_COMMENT_TEMPLATE' => '.default',
	'BLOG_ID'                    => 1,
	'CACHE_TIME'                 => 3600,
	'NEED_NAV' 					 => 'Y',
	'COMMENTS_COUNT' 			 => 25
));

?>