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

//$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, "fb_translate");
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "fb_translate", array(
        "group" => "General",
        "type" => "int",
        "default" => "0",
        "label" => "Translate automatically?",
        "note"=> "Should this product be translated automatically when the store admin decides so?",
        "input" => "select",
        'source' => 'eav/entity_attribute_source_boolean',
        "global" => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        "backend" => "",
        "frontend" => "",
        "is_configurable" => false,
        "required" => false,
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'visible_in_advanced_search' => false,
        'used_in_product_listing' => false,
        'unique' => false,
        'apply_to' => ''
    )
);

$installer->endSetup();