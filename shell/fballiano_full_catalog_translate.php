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
    protected $translation_system = null;
    protected $api_key = null;
    protected $command = null;
    protected $store_source = null;
    protected $store_dest = null;
    protected $language_source = null;
    protected $language_dest = null;
    protected $ws_url = "https://translation.googleapis.com/language/translate/v2";
    protected $attributes_to_translate = null;
    protected $category_attributes_to_translate = null;
    protected $datapump = null;
    protected $debug_mode = false;
    protected $dry_run = false;

    public function run()
    {
        $this->debug_mode = $this->getArg("debug");
        $this->dry_run = $this->getArg("dry");
        if ($this->dry_run) $this->debug_mode = true;
        $this->helper = Mage::helper("fballiano_fullcatalogtranslate");
        $this->translation_system = $this->helper->getTranslationSystem();
        $this->attributes_to_translate = $this->helper->getAttributesToTranslate();
        $this->category_attributes_to_translate = $this->helper->getCategoryAttributesToTranslate();
        $this->api_key = $this->helper->getApiKey();
        $this->ws_url .= "?key={$this->api_key}";
        $this->command = $this->helper->getCommand();

        switch ($this->translation_system) {
            case "googletranslate":
                if (!$this->api_key) die("Please set your API key in the Magento admin configuration.\n");
                if (!function_exists("mb_strlen")) die("Please install mbstring PHP extension\n");
                break;
            case "custom":
                if (!$this->command) die("Please set the translation command in the Magento admin configuration.\n");
                break;
            default:
                die("Unrecognized translation system: {$this->translation_system}.\n");
        }

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

        $this->translateCategories($store_id_dest);

        $product = Mage::getModel("catalog/product");
        foreach ($products as $product_id) {
            $product->load($product_id);
            $row = $product->getData();

            echo "Translating {$row["sku"]} from {$this->language_source} to {$this->language_dest}... ";
            if ($this->debug_mode) echo "\n";
            $translated_row = array();
            $translated_row["store"] = (string)$this->store_dest;
            $translated_row["sku"] = (string)$row["sku"];
            $translated_row["fb_translate"] = "0"; //leave it as string otherwise magmi won't save it
            foreach ($this->attributes_to_translate as $attribute) {
                if (strlen($row[$attribute])) {
                    $translated_row[$attribute] = $this->translateString($row[$attribute]);
                }
                if ($this->debug_mode) {
                    echo "\t[$attribute] [{$row[$attribute]}] -> [{$translated_row[$attribute]}]\n";
                }
            }
            if (!$this->dry_run) $this->datapump->ingest($translated_row);
            echo "OK\n";
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        $this->datapump->endImportSession();
        echo "Terminated\n";
    }

    public function translateCategories($store_id_dest)
    {
        $appEmulation = Mage::getSingleton("core/app_emulation");
        $attribute_id = Mage::getModel("catalog/entity_attribute")->loadByCode(Mage_Catalog_Model_Category::ENTITY, "fb_translate")->getId();
        $table_name = Mage::getSingleton("core/resource")->getTableName("catalog_category_entity_int");
        $categories = Mage::getSingleton("core/resource")->getConnection("core_read")->fetchCol("SELECT entity_id FROM {$table_name} WHERE attribute_id={$attribute_id} AND store_id={$store_id_dest} AND value=1");

        foreach ($categories as $category_id) {
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_source);
            $category = Mage::getModel("catalog/category");
            $category->load($category_id);
            $row = $category->getData();
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            echo "Translating category {$row["entity_id"]} from {$this->language_source} to {$this->language_dest}... ";
            if ($this->debug_mode) echo "\n";
            $translated_row = array();
            $translated_row["fb_translate"] = 0;
            foreach ($this->category_attributes_to_translate as $attribute) {
                if (strlen($row[$attribute])) {
                    $translated_row[$attribute] = $this->translateString($row[$attribute]);
                }
                if ($this->debug_mode) {
                    echo "\t[$attribute] [{$row[$attribute]}] -> [{$translated_row[$attribute]}]\n";
                }
            }
            if (!$this->dry_run) {
                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_dest);
                $category = Mage::getModel("catalog/category");
                $category->load($category_id);
                $category->addData($translated_row);
                $category->save();
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
            echo "OK\n";
        }
    }

    public function productCollectionWalkCallback($args)
    {
        $row = $args["row"];
        echo "Translating {$row["sku"]} from {$this->language_source} to {$this->language_dest}... ";
        if ($this->debug_mode) echo "\n";
        $translated_row = array();
        $translated_row["store"] = $this->store_dest;
        $translated_row["sku"] = $row["sku"];
        $translated_row["fb_translate"] = 0;
        foreach ($this->attributes_to_translate as $attribute) {
            if (strlen($row[$attribute])) {
                if ($this->translation_system == "googletranslate" and mb_strlen($translated_row[$attribute]) >= 5000) {
                    echo "\t[$attribute] more than 5000 chars long, unsupported by Google Translate, not translated\n";
                    continue;
                }
                $translated_row[$attribute] = $this->translateString($row[$attribute]);
                if ($this->debug_mode) {
                    echo "\t[$attribute] [{$row[$attribute]}] -> [{$this->language_dest} {$translated_row[$attribute]}]\n";
                }
            }
        }

        if (!$this->dry_run) $this->datapump->ingest($translated_row);
        echo "OK\n";
    }

    public function translateString($string)
    {
        switch ($this->translation_system) {
            case "googletranslate":
                $ws_url = "{$this->ws_url}&q=" . urlencode($string);
                if ($this->debug_mode) echo "\t{$ws_url}\n";
                $translated = json_decode(file_get_contents($ws_url), true);
                return (string)$translated["data"]["translations"][0]["translatedText"];
            case "custom":
                $command = str_replace(
                    array("%SOURCELANGUAGE%", "%TARGETLANGUAGE%", "%STRING%"),
                    array($this->language_source, $this->language_dest, $string),
                    $this->command
                );
                if ($this->debug_mode) echo "\t{$command}\n";
                return shell_exec($command);
        }
    }

    public function usageHelp()
    {
        return <<<USAGE

Usage:\tphp -f fballiano_full_catalog_translate.php source_store_view_code target_store_view_code [-debug] [-dry]

-debug\tshows every call to traslation systems and the results
-dry\ttranslates everything (automatically enables debug also) but do not save anything on the database

USAGE;
    }
}

$shell = new Fballiano_FullCatalogTranslate_Shell();
$shell->run();