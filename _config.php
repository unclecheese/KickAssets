<?php

$link = 'admin/'.KickAssetAdmin::$url_segment;
CMSMenu::replace_menu_item("AssetAdmin",_t('KickAssetAdmin.BROWSE','Browse files...'),$link,"KickAssetAdmin",2);

// We're using our own custom menu insertion. Don't need another one.
CMSMenu::remove_menu_item("KickAssetAdmin");

Director::addRules(10, array (
	$link => 'KickAssetAdmin'
));

$dir = basename(dirname(__FILE__));
if($dir != "kickassets") {
	user_error('Directory name must be "kickassets" (currently "'.$dir.'")',E_USER_ERROR);
}

LeftAndMain::require_javascript("kickassets/javascript/fancybox/fancybox.js");
LeftAndMain::require_css("kickassets/javascript/fancybox/fancybox.css");
LeftAndMain::require_javascript("kickassets/javascript/kickassets_init.js");
