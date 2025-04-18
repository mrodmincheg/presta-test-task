<?php

namespace PrestaShop\Module\ProductLabel\Install;

class Installer
{
    public function install(\Module $module)
    {
        if (!$this->registerHooks($module)) {
            return false;
        }
        if (!$this->createDatabaseTable()) {
            return false;
        }

        if (!$this->createJoinTable()) {
            return false;
        }

        if (!$this->installTab($module)) {
            return false;
        }

        return true;
    }

    private function registerHooks(\Module $module): bool
    {
        $hooks = [
            'actionProductFormBuilderModifier',
            'actionAfterUpdateProductFormHandler',
            'displayBackOfficeHeader',
            'displayAfterProductThumbs',
            'displayProductPriceBlock',
            'header'
        ];

        return (bool) $module->registerHook($hooks);
    }

    private function installTab(\Module $module)
    {
        /** @var TabRepository $tabRepository */
        $tabRepository = $module->get('prestashop.core.admin.tab.repository');

        $parentTab = $tabRepository->findOneByClassName('AdminCatalog');

        $tab = new \Tab();
        $tab->class_name = 'AdminProductLabel';
        $tab->module = $module->name;
        $tab->id_parent = $parentTab->getId();
        $tab->position = 1;
        foreach (\Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Product Labels';
        }
        return $tab->save();
    }

    private function createDatabaseTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_label` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `color` VARCHAR(7) NOT NULL,
        `visible` TINYINT(1) DEFAULT 1
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4;";

        return \Db::getInstance()->execute($sql);
    }

    private function createJoinTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "product_label_product` (
        `label_id` INT NOT NULL,
        `product_id` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`label_id`, `product_id`),
        FOREIGN KEY (`label_id`) REFERENCES `" . _DB_PREFIX_ . "product_label`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `" . _DB_PREFIX_ . "product`(`id_product`) ON DELETE CASCADE
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4;";

        return \Db::getInstance()->execute($sql);
    }
}
