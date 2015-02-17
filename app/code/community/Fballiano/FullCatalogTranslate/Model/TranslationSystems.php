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

class Fballiano_FullCatalogTranslate_Model_TranslationSystems
{
    public function toOptionArray()
    {
        $toreturn = array();
	    $toreturn[] = array(
		    "value" => "googletranslate",
		    "label" => "Google Translate"
	    );
	    $toreturn[] = array(
		    "value" => "custom",
		    "label" => "Custom"
	    );

        return $toreturn;
    }
}