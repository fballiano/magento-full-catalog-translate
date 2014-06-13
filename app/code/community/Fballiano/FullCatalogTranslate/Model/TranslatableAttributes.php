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

class Fballiano_FullCatalogTranslate_Model_TranslatableAttributes
{
    public function toOptionArray()
    {
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType(Mage_Catalog_Model_Product::ENTITY)
            ->getTypeId();

        $attributes = Mage::getModel("catalog/entity_attribute")->getCollection()
            ->addFieldToFilter("entity_type_id", $entityTypeId)
            ->addFieldToFilter("backend_type", array("in" => array("varchar", "text", "textarea")))
            ->addFieldToFilter("frontend_input", array("in" => array("text", "textarea")))
            ->addFieldToFilter("attribute_code", array("nin" => array("custom_layout_update", "recurring_profile")))
            ->setOrder("attribute_code", "ASC");

        $toreturn = array();
        foreach ($attributes as $attribute) {
            $toreturn[] = array(
                "value" => $attribute["attribute_code"],
                "label" => ucfirst(str_replace("_", " ", $attribute["attribute_code"]))
            );
        }

        return $toreturn;
    }
}