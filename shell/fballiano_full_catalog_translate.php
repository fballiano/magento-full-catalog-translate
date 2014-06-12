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

require_once 'abstract.php';

class Fballiano_FullCatalogTranslate_Shell extends Mage_Shell_Abstract
{
    protected $helper = null;
    protected $api_key = null;
    protected $store_source = null;
    protected $store_dest = null;
    protected $language_source = null;
    protected $language_dest = null;
    protected $ws_url = "https://www.googleapis.com/language/translate/v2";
    protected $attributes_to_translate = null;
    protected $datapump = null;

    public function run()
    {
        $this->helper = Mage::helper("fballiano_fullcatalogtranslate");
        $this->attributes_to_translate = $this->helper->getAttributesToTranslate();
        $this->api_key = $this->helper->getApiKey();
        if (!$this->api_key) die("Please set your API key in the Magento admin configuration.\n");
        $this->ws_url .= "?key={$this->api_key}";

        $args = array_keys($this->_args);
        $this->store_source = @$args[0];
        $this->store_dest = @$args[1];
        if (!$this->store_source or !$this->store_dest) die($this->usageHelp());

        require_once "Magmi/inc/magmi_defs.php";
        require_once "Magmi/integration/inc/magmi_datapump.php";
        require_once "Magmi/integration/inc/productimport_datapump.php";
        require_once "Magmi/integration/inc/productimport_datapump.php";
        $this->datapump = new Magmi_ProductImport_Datapump();
        $this->datapump->beginImportSession("default", "create");

        $appEmulation = Mage::getSingleton("core/app_emulation");

        try {
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_dest);
        } catch (Mage_Core_Model_Store_Exception $e) {
            die("Target store view \"{$this->store_dest}\" doesn't seem to exist\n" . $this->usageHelp());
        }

        $store_id_dest = Mage::app()->getStore()->getId();
        $this->language_dest = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        $this->ws_url .= "&target={$this->language_dest}";

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        $attribute_id = Mage::getModel("catalog/entity_attribute")->loadByCode(Mage_Catalog_Model_Product::ENTITY, "fb_translate")->getId();
        $table_name = Mage::getSingleton("core/resource")->getTableName("catalog_product_entity_int");
        $products = Mage::getSingleton("core/resource")->getConnection("core_read")->fetchCol("SELECT entity_id FROM {$table_name} WHERE attribute_id={$attribute_id} AND store_id={$store_id_dest} AND value=1");

        try {
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_source);
        } catch (Mage_Core_Model_Store_Exception $e) {
            die("Source store \"{$this->store_source}\" view doesn't seem to exist\n" . $this->usageHelp());
        }

        $this->language_source = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        $this->ws_url .= "&source={$this->language_source}";

        $product = Mage::getModel("catalog/product");
        foreach ($products as $product_id) {
            $product->load($product_id);
            $row = $product->getData();

            echo "Translating {$row["sku"]} from {$this->language_source} to {$this->language_dest}... ";
            $translated_row = array();
            $translated_row["store"] = (string)$this->store_dest;
            $translated_row["sku"] = (string)$row["sku"];
            $translated_row["fb_translate"] = "0"; //leave it as string otherwise magmi won't save it
            foreach ($this->attributes_to_translate as $attribute) {
                if (strlen($row[$attribute])) {
                    $ws_url = "{$this->ws_url}&q=" . urlencode($row[$attribute]);
                    $translated = json_decode(file_get_contents($ws_url), true);
                    $translated = $translated["data"]["translations"][0]["translatedText"];
                    $translated_row[$attribute] = (string)$translated;
                }
            }
            $this->datapump->ingest($translated_row);
            echo "OK\n";
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        $this->datapump->endImportSession();
        echo "Terminated\n";
    }

    public function productCollectionWalkCallback($args)
    {
        $row = $args["row"];
        echo "Translating {$row["sku"]} from {$this->language_source} to {$this->language_dest}... ";
        $translated_row = array();
        $translated_row["store"] = $this->store_dest;
        $translated_row["sku"] = $row["sku"];
        $translated_row["fb_translate"] = 0;
        foreach ($this->attributes_to_translate as $attribute) {
            if (strlen($row[$attribute])) {
                $ws_url = "{$this->ws_url}&q=" . urlencode($row[$attribute]);
                $translated = json_decode(file_get_contents($ws_url), true);
                $translated = $translated["data"]["translations"][0]["translatedText"];
                $translated_row[$attribute] = $translated;
            }
        }

        $this->datapump->ingest($translated_row);
        echo "OK\n";
    }

    public function usageHelp()
    {
        return <<<USAGE

Usage:  php -f fballiano_full_catalog_translate.php source_store_view_code target_store_view_code

USAGE;
    }
}

$shell = new Fballiano_FullCatalogTranslate_Shell();
$shell->run();