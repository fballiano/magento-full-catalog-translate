<?php
/**
 * Created by PhpStorm.
 * User: Fabrizio
 * Date: 09/06/14
 * Time: 21.31
 */ 
class Fballiano_FullCatalogTranslate_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return array
     */
    public function getAttributesToTranslate()
    {
        $attributes = Mage::getStoreConfig("fballiano_full_catalog_translate/general/attributes_to_translate");
        $attributes = explode(",", $attributes);
        foreach ($attributes as $k=>$v) {
            $attributes[$k] = trim($v);
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig("fballiano_full_catalog_translate/google_translate/api_key");
    }
}