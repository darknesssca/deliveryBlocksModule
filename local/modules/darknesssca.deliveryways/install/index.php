<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Base;

class darknesssca_deliveryways extends CModule
{
    var $MODULE_ID = "darknesssca.deliveryways";
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $WaysDeliveryTable = "Darknesssca\\DeliveryWays\\WaysDeliveryTable";
    var $WaysByDeliveryServicesTable = "Darknesssca\\DeliveryWays\\WaysByDeliveryServicesTable";

    function __construct()
    {
        $this->MODULE_VERSION = "0.0.1";
        $this->MODULE_VERSION_DATE = "02.02.2020";
        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage("MODULE_DESCRIPTION");
        $this->PARTNER_NAME = 'Darknesssca';
    }

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        $this->InstallDB();
        $this->InstallFiles();
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
        return true;
    }

    function InstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            $connection = Application::getConnection();

            if (!$connection->isTableExists(Base::getInstance($this->WaysDeliveryTable)->getDBTableName())
            ) {
                Base::getInstance($this->WaysDeliveryTable)->createDbTable();
            }

            if (!$connection->isTableExists(Base::getInstance($this->WaysByDeliveryServicesTable)->getDBTableName())
            ) {
                Base::getInstance($this->WaysByDeliveryServicesTable)->createDbTable();
            }
        }
        return true;
    }

    function UnInstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID))
        {
            if (Application::getConnection()->isTableExists(Base::getInstance($this->WaysDeliveryTable)->getDBTableName())) {
                Application::getConnection()->dropTable(Base::getInstance($this->WaysDeliveryTable)->getDBTableName());
            }

            if (Application::getConnection()->isTableExists(Base::getInstance($this->WaysByDeliveryServicesTable)->getDBTableName())) {
                Application::getConnection()->dropTable(Base::getInstance($this->WaysByDeliveryServicesTable)->getDBTableName());
            }

        }
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/$this->MODULE_ID/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/$this->MODULE_ID/install/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        return true;
    }
}