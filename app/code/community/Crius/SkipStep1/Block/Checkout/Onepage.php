<?php
class Crius_SkipStep1_Block_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    public function getSteps() {
        if (Mage::helper('skipstep1')->isSkipEnabled()) {
            $steps = parent::getSteps();
            if (array_key_exists('login', $steps)) {
                unset($steps['login']);
            }
            return $steps;
        } else {
            return parent::getSteps();
        }
    }
    
    public function getActiveStep()
    {
        if (Mage::helper('skipstep1')->isSkipEnabled()) {
            return 'billing';
        } else {
            return parent::getActiveStep();
        }
    }
}