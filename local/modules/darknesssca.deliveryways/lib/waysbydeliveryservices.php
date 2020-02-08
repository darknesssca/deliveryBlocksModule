<?php

namespace Darknesssca\DeliveryWays;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;


class WaysByDeliveryServicesTable extends Entity\DataManager
{
    private static $table_name = 'b_darknesssca_ways_by_delivery_services';

    public static function getTableName()
    {
        return self::$table_name;
    }

    public static function getMap()
    {
        try {
            return [
                (new Entity\IntegerField('WAY_ID'))
                    ->configurePrimary(true),

                (new Reference('WAY', WaysDeliveryTable::class,
                    Join::on('this.WAY_ID', 'ref.ID')))
                    ->configureJoinType('inner'),

                (new Entity\IntegerField('DELIVERY_ID'))
                    ->configurePrimary(true),

                (new Reference('DELIVERY', DeliveryTable::class,
                    Join::on('this.DELIVERY_ID', 'ref.ID')))
                    ->configureJoinType('inner'),
            ];
        } catch (ArgumentException $e) {
            return [];
        } catch (SystemException $e) {
            return [];
        }
    }

    public static function getDeliveryObject($delivery_id)
    {
        return DeliveryTable::getByPrimary($delivery_id, ['select' => ['*']])->fetchObject();
    }
    public static function getDeliveryArray($delivery_id)
    {
        return DeliveryTable::getByPrimary($delivery_id, ['select' => ['*']])->fetch();
    }
}