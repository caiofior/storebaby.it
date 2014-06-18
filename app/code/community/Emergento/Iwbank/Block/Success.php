<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
/* Emergento */
class Emergento_Iwbank_Block_Success extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
          $successUrl = Mage::getUrl('*/*/success');
          $html = '<html>'
                . '<meta http-equiv="refresh" content="0; URL='.$successUrl.'">'
                . '<body>'
                . '<p>' . $this->__('Il pagamento è stato effettuato correttamente dal nostro sistema.') . '</p>'
                . '<p>' . $this->__('Click <a href="%s">qui</a> se non vieni reindirizzato automaticamente.', $successUrl) . '</p>'
                . '</body></html>';

        return $html;
    }
}
