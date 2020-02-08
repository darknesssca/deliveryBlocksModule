<?php defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$aMenu = array(
    array(
        'parent_menu' => 'global_menu_services',
        'sort' => 400,
        'text' => "Способы доставки",
        'title' => "",
        'url' => 'delivery_ways.php',
        'items_id' => 'delivery_ways'
    )
);
return $aMenu;
