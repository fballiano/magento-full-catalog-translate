<?php

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
        $this->store_source = $args[0];
        $this->store_dest = $args[1];

        require_once "Magmi/inc/magmi_defs.php";
        require_once "Magmi/integration/inc/magmi_datapump.php";
        require_once "Magmi/integration/inc/productimport_datapump.php";
        require_once "Magmi/integration/inc/productimport_datapump.php";
        $this->datapump = new Magmi_ProductImport_Datapump();
        $this->datapump->beginImportSession("default", "create");

        $appEmulation = Mage::getSingleton("core/app_emulation");

        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_dest);
        $this->language_dest = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        $this->ws_url .= "&target={$this->language_dest}";
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($this->store_source);
        $this->language_source = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        $this->ws_url .= "&source={$this->language_source}";

        $products = Mage::getResourceModel("catalog/product_collection")
            ->addStoreFilter()
            ->addAttributeToFilter("fb_translate", "1");
        foreach ($this->attributes_to_translate as $attribute) {
            $products->addAttributeToFilter($attribute, array('like' => '%'));
        }

        Mage::getSingleton('core/resource_iterator')->walk(
            $products->getSelect(),
            array(array($this, 'productCollectionWalkCallback'))
        );

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
}

$shell = new Fballiano_FullCatalogTranslate_Shell();
$shell->run();