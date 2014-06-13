<?php
/**
 * FBalliano
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   FBalliano
 * @package    FBalliano_FullCatalogTranslate
 * @copyright  Copyright (c) 2014 Fabrizio Balliano (http://fabrizioballiano.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$db = Mage::getSingleton('core/resource')->getConnection("core_write");
$config_table_name = Mage::getSingleton('core/resource')->getTableName("core_config_data");
$configs = $db->fetchAll("SELECT * FROM $config_table_name WHERE path='fballiano_full_catalog_translate/general/attributes_to_translate'");
foreach ($configs as $config) {
    $newvalues = array();
    $values = explode(",", $config["value"]);
    foreach ($values as $value) {
        $value = trim($value);
        if (strlen($value)) $newvalues[] = $value;
    }
    $newvalues = implode(",", $newvalues);
    $db->update($config_table_name, array("value" => $newvalues), "config_id={$config["config_id"]}");
}

$installer->endSetup();