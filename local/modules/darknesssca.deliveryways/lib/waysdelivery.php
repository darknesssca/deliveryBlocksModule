<?php

namespace Darknesssca\DeliveryWays;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\SystemException;

class WaysDeliveryTable extends Entity\DataManager
{
    private static $link_delivery_field = 'WAY';

    public static function getTableName()
    {
        return 'b_darknesssca_ways_delivery';
    }

    public static function getMap()
    {
        try {
            return [
                (new Entity\IntegerField('ID'))
                    ->configurePrimary(true)
                    ->configureAutocomplete(true)
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_ID_FIELD')),

                //TODO Добавить валидатор
                (new Entity\StringField('NAME'))
                    ->configureRequired('true')
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_NAME_FIELD')),

                (new Entity\BooleanField('ACTIVE'))
                    ->configureRequired(true)
                    ->configureStorageValues('N', 'Y')
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_ACTIVE_FIELD')),

                (new Entity\StringField('DESCRIPTION'))
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_DESCRIPTION_FIELD')),

                (new Entity\IntegerField('SORT'))
                    ->configureDefaultValue(100)
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_SORT_FIELD')),

                (new OneToMany('DELIVERIES', WaysByDeliveryServicesTable::class, self::$link_delivery_field))
                    ->configureTitle(Loc::getMessage('DELIVERY_WAY_ENTITY_DELIVERIES_FIELD'))
            ];
        } catch (SystemException $e) {
            return [];
        }
    }
}