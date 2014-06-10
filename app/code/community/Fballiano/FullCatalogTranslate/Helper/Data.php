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