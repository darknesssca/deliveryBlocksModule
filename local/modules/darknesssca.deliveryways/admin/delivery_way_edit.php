<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
Bitrix\Main\Loader::includeModule('darknesssca.deliveryways');

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Darknesssca\DeliveryWays\WaysDeliveryTable;
use Darknesssca\DeliveryWays\WaysByDeliveryServicesTable;
use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;

$request = Application::getInstance()->getContext()->getRequest();
$way_id = intval($request->getQuery('WAY_ID'));
$page_title = ($way_id > 0) ? Loc::getMessage("EDIT_WAY") : Loc::getMessage("ADD_WAY");
$link_field = 'DELIVERIES';
$arErrors = [];

//Получаем поля
$arFields = WaysDeliveryTable::getEntity()->getFields();
$arValues = [];
if ($way_id > 0) {
    $oValues = WaysDeliveryTable::getById($way_id)->fetchObject();
    foreach ($arFields as $field) {
        if ($field->getName() != $link_field) {
            $arValues[$field->getName()] = $oValues->get($field->getName());
        } elseif ($field->getName() == $link_field) {
            $rsDeliveries = WaysByDeliveryServicesTable::getList([
                'select' => ['DELIVERY_ID', 'ID_1C'],
                'filter' => ['WAY_ID' => $way_id]
            ]);

            while ($delivery = $rsDeliveries->fetch()) {
                $arValues[$link_field][] = $delivery['DELIVERY_ID'];
                $arValues['ID_1C'][] = $delivery['ID_1C'];
            }

            //Получаем названия и описания служб доставки
            $rsDeliveries = DeliveryTable::getList([
                'select' => ['ID', 'NAME', 'DESCRIPTION'],
                'filter' => ['@ID' => $arValues[$link_field]]
            ]);

            while ($delivery = $rsDeliveries->fetch()) {
                $arValues['DELIVERIES_NAMES'][] = $delivery['NAME'];
                $arValues['DELIVERIES_DESCS'][] = $delivery['DESCRIPTION'];
            }
        }
    }
}

//Обработка сохранения
if ($request->getRequestMethod() == 'POST' && ($save != '' || $apply != '')) {
    if (intval($request->getPost('ID')) == 0) {
        $active = !empty($request->getPost('ACTIVE')) ? 'Y' : 'N';
        $result = WaysDeliveryTable::add([
            'NAME' => $request->getPost('NAME'),
            'ACTIVE' => $active,
            'DESCRIPTION' => $request->getPost('DESCRIPTION'),
            'SORT' => $request->getPost('SORT'),
        ]);

        if ($result->isSuccess()) {
            $id = $result->getId();
            $arDeliveries = array_map('intval', $request->getPost('DELIVERY'));
            $ar1CIds = array_map('intval', $request->getPost('ID_1C'));

            foreach ($arDeliveries as $key => $delivery_id) {
                if ($delivery_id != 0) {
                    $rswaybydel = WaysByDeliveryServicesTable::add([
                        'WAY_ID' => $id,
                        'DELIVERY_ID' => $delivery_id,
                        'ID_1C' => $ar1CIds[$key]
                    ]);

                    if (!$rswaybydel->isSuccess()) {
                        $arErrors = array_merge($arErrors, $result->getErrorMessages());
                        break;
                    }
                }
            }

            if ($apply != '') {
                LocalRedirect('/bitrix/admin/delivery_way_edit.php?WAY_ID=' . $id . '&lang=ru');
            } elseif ($save != '') {
                LocalRedirect('/bitrix/admin/delivery_ways.php?lang=ru');
            }
        } else {
            $arErrors = array_merge($arErrors, $result->getErrorMessages());
        }
    } elseif (intval($request->getPost('ID')) > 0) {
        $id = intval($request->getPost('ID'));
        $active = !empty($request->getPost('ACTIVE')) ? 'Y' : 'N';
        $result = WaysDeliveryTable::update($id, [
            'NAME' => $request->getPost('NAME'),
            'ACTIVE' => $active,
            'DESCRIPTION' => $request->getPost('DESCRIPTION'),
            'SORT' => $request->getPost('SORT'),
        ]);
        if ($result->isSuccess()) {
            $rsDeliveries = WaysByDeliveryServicesTable::getList([
                'select' => ['DELIVERY_ID', 'ID_1C'],
                'filter' => ['WAY_ID' => $id]
            ]);

            while ($arDeliveries = $rsDeliveries->fetch()) {
                WaysByDeliveryServicesTable::delete(['WAY_ID' => $id, 'DELIVERY_ID' => $arDeliveries['DELIVERY_ID']]);
            }

            $arDeliveries = array_map('intval', $request->getPost('DELIVERY'));
            $ar1CIds = array_map('intval', $request->getPost('ID_1C'));
            foreach ($arDeliveries as $key => $delivery_id) {
                if ($delivery_id != 0) {
                    $rswaybydel = WaysByDeliveryServicesTable::add([
                        'WAY_ID' => $id,
                        'DELIVERY_ID' => $delivery_id,
                        'ID_1C' => $ar1CIds[$key]
                    ]);

                    if (!$rswaybydel->isSuccess()) {
                        $arErrors = array_merge($arErrors, $result->getErrorMessages());
                        break;
                    }
                }
            }
            //Управление переходами с кнопок
            if ($apply != '') {
                LocalRedirect('/bitrix/admin/delivery_way_edit.php?WAY_ID=' . $id . '&lang=ru');
            } elseif ($save != '') {
                LocalRedirect('/bitrix/admin/delivery_ways.php?lang=ru');
            }
        } else {
            $arErrors = array_merge($arErrors, $result->getErrorMessages());
        }
    }
}

$aTabs = array(
    array(
        "DIV" => "delivery_way",
        "TAB" => Loc::getMessage("DELIVERY_WAY"),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage("DELIVERY_WAY_TITLE")
    ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$APPLICATION->SetTitle($page_title);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php"); ?>

<style>
    .ways_table_td{
        padding:0 10px;
    }
</style>

<form method="POST" Action="<? echo $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data" name="post_form">
    <? echo bitrix_sessid_post(); ?>
    <input type="hidden" name="ID" value="<?= $way_id ?>">
    <? $tabControl->Begin(); ?>
    <? $tabControl->BeginNextTab(); ?>
    <tr>
        <td width="40%"><?= $arFields['ID']->getTitle() . ':' ?></td>
        <td width="60%"><?= $way_id ?></td>
    </tr>

    <tr>
        <td width="40%"><?= $arFields['NAME']->getTitle() . ':' ?></td>
        <td width="60%"><input type="text" name="NAME" value="<?= $arValues['NAME']; ?>" size="30" maxlength="255"/>
        </td>
    </tr>

    <tr>
        <td width="40%"><?= $arFields['ACTIVE']->getTitle() . ':' ?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE"
                               value="Y"<? if ($arValues['ACTIVE'] == "Y") {
                echo " checked";
            } ?> /></td>
    </tr>

    <tr>
        <td width="40%"><?= $arFields['DESCRIPTION']->getTitle() . ':' ?></td>
        <td width="60%"><input type="text" name="DESCRIPTION" value="<?= $arValues['DESCRIPTION']; ?>" size="30"
                               maxlength="255"/></td>
    </tr>

    <tr>
        <td width="40%"><?= $arFields['SORT']->getTitle() . ':' ?></td>
        <td width="60%"><input type="text" name="SORT" value="<?= $arValues['SORT']; ?>" size="30" maxlength="255"/>
        </td>
    </tr>

    <tr>
        <td width="40%"><?= $arFields['DELIVERIES']->getTitle() . ':' ?></td>
        <td width="60%">
            <table id="delivery_table">
                <tr>
                    <td>ID 1C</td>
                    <td style="display: none">ID службы доставки</td>
                    <td class="ways_table_td">Название доставки</td>
                    <td class="ways_table_td">Описание</td>
                    <td></td>
                </tr>
                <? if (!empty($arValues['DELIVERIES'])) : ?>
                    <? foreach ($arValues['DELIVERIES'] as $key => $value) : ?>
                        <? $index = $key + 1; ?>
                        <tr id="str<?= $index ?>">
                            <td>
                                <input type="text" size="5" name="ID_1C[<?= $index ?>]" id="input_1c_<?= $index ?>"
                                       value="<?= $arValues['ID_1C'][$key] ?>">
                            </td>
                            <td style="display: none">
                                <input type="text" id="input_delivery_<?= $index ?>" size="5"
                                       name="DELIVERY[<?= $index ?>]" value="<?= $value ?>">
                            </td>
                            <td id="name<?= $index ?>" class="ways_table_td">
                                <?=$arValues['DELIVERIES_NAMES'][$key]?>
                            </td>
                            <td id="desc<?= $index ?>" class="ways_table_td">
                                <?=$arValues['DELIVERIES_DESCS'][$key]?>
                            </td>
                            <td>
                                <input type="button" value="Выбрать" title="Выбрать службу доставки" onclick="chooseDeliveryService(this)">
                            </td>
                            <td>
                                <input type="button" value="Удалить" title="Удалить службу доставки из списка" onclick="deleteDeliveryService(this)">
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? else : ?>
                    <tr id="str1">
                        <td>
                            <input type="text" size="5" name="ID_1C[1]" id="input_1c_1" value="">
                        </td>
                        <td style="display: none">
                            <input type="text" id="input_delivery_1" size="5" name="DELIVERY[1]" value="">
                        </td>
                        <td id="name1" class="ways_table_td">
                            <?=''?>
                        </td>
                        <td id="desc1" class="ways_table_td">
                            <?=''?>
                        </td>
                        <td>
                            <input type="button" value="Выбрать" title="Выбрать службу доставки" onclick="chooseDeliveryService(this)">
                        </td>
                        <td>
                            <input type="button" value="Удалить" title="Удалить службу доставки из списка" onclick="deleteDeliveryService(this)">
                        </td>
                    </tr>
                <? endif; ?>
            </table>

            <input type="button" value="Еще" width="100%" id="add_str">
        </td>
    </tr>

    <script>

        $(document).on('ready', function () {
            $('#add_str').on('click', addStr);
        });

        function chooseDeliveryService(item) {
            let number, arIds;
            number = getNumStr(false, item);
            arIds = getTempIds();

            window.open('/local/php_interface/include/qsoft/tools/delivery_services_search.php?num=' + number + '&id=' + arIds,
                '',
                'scrollbars=yes,resizable=yes,width=760,height=500,top=' + Math.floor((screen.height - 560) / 2 - 14) + ',left=' + Math.floor((screen.width - 760) / 2 - 5)
            );
        }

        function deleteDeliveryService(item) {
            let number;
            number = getNumStr(false, item);

            $('#input_delivery_' + number).val('');
            $('#name' + number).html('');
            $('#desc' + number).html('');

        }

        function addStr() {
            let number, content, input_delivery, input_1c,
                td_delivery_name, td_delivery_desc;
            number = getNumStr(true);

            content = $('#str' + number).clone();
            content.attr('id', 'str' + (number + 1));


            input_delivery = content.find('#input_delivery_' + number);
            input_delivery.attr('id', 'input_delivery_' + (number + 1));
            input_delivery.attr('name', 'DELIVERY[' + (number + 1) + ']');
            input_delivery.val('');

            td_delivery_name = content.find('#name' + number);
            td_delivery_name.attr('id', 'name' + (number + 1));
            td_delivery_name.html('');

            td_delivery_desc = content.find('#desc' + number);
            td_delivery_desc.attr('id', 'desc' + (number + 1));
            td_delivery_desc.html('');

            input_1c = content.find('#input_1c_' + number);
            input_1c.attr('id', 'input_1c_' + (number + 1));
            input_1c.attr('name', 'ID_1C[' + (number + 1) + ']');
            input_1c.val('');

            $('#delivery_table').append(content);
        }

        function getNumStr(last = false, item) {
            let id, num;

            if (last) {
                id = $('#delivery_table').find('tr').last().attr('id');
            } else {
                id = $(item).closest('tr').attr('id');
            }

            num = id.replace('str', '');
            return parseInt(num);
        }

        function getTempIds() {
            let number, i, id, arIds = [];

            number = getNumStr(true);

            for (i = 1; i <= number; i++){
                id = $('#input_delivery_' + i).val();
                arIds.push(id);
            }

            return arIds;
        }
    </script>

    <? $tabControl->Buttons(
        array(
            "back_url" => "delivery_ways.php?lang=" . LANG,
        )
    ); ?>
    <? $tabControl->End(); ?>
</form>
