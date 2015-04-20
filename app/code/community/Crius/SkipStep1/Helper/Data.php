<?php
class Crius_SkipStep1_Helper_Data extends Mage_Core_Helper_Data
{
    public function isSkipEnabled()
    {
        return Mage::getStoreConfig('checkout/skipstep1/enabled');
    }
}