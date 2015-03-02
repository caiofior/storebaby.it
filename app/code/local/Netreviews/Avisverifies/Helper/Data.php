<?php 
/*
 * This Helper serve as the data container for the api
 * AND for translation purpose also.
 */
class Netreviews_Avisverifies_Helper_Data extends Mage_Core_Helper_Abstract{
        
    public $idwebsite;
    public $secretkey;
    public $statusChoosen;
    public $processChoosen;
    public $allowedProducts;
    public $enabledwebsite;
    public $forbiddenMailExtensions;
    public $delay;
    public $SHA1;
    public $allShopIds;
    
    public function setup(array $config){
        // Magento Shop Info
        $this->idwebsite = $config['idWebsite'];
        $this->secretkey = $this->getModuleSecretKey($config['idWebsite']);
        $this->enabledwebsite = (empty($this->secretkey))? 0 : 1;
        $this->allShopIds = $this->getModuleActiveStoresIds($config['idWebsite']);
        // shop configuration
        $magesel = Mage::getModel("core/store")->load(reset($this->allShopIds));
        
        // order config.
        $this->allowedProducts = $magesel->getConfig(strtolower('AVISVERIFIES/system/GETPRODREVIEWS')); //récupérer les avis produits ou non 
        $this->processChoosen = $magesel->getConfig(strtolower('AVISVERIFIES/system/PROCESSINIT')); //onorder or onorderstatuschange
        $this->statusChoosen = explode(';', $magesel->getConfig(strtolower('AVISVERIFIES/system/ORDERSTATESCHOOSEN'))); //status choisis
        $this->forbiddenMailExtensions = explode(';', $magesel->getConfig(strtolower('AVISVERIFIES/system/FORBIDDEN_EMAIL'))); //emails interdit
        $this->delay = $magesel->getConfig(strtolower('AVISVERIFIES/system/DELAY'));
        // SHA1, secret Hashing.
        if (isset($config['query'])) {
            $this->SHA1 = SHA1($config['query'].$this->idwebsite.$this->secretkey);
        }
    }
    
    // we are going to filter by idWebsite and is active 
    public function getModuleActiveStoresIds($idWebsite){
        // get config by id
        // get config by id
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('value',$idWebsite);
        // now filter again by is active 
        $scope_id = array();
        foreach ($resource as $val) {
            $scope_id[] = $val->getData('scope_id');
        }
        // now filter with the config set to enabledwebsite = 1
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('scope_id',array("in"=>$scope_id))
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')
                ->addFieldToFilter('value','1');
        
        $usedSotersIds = array();
        foreach ($resource as $val) {
            $usedSotersIds[] = $val->getData('scope_id');
        }
        return $usedSotersIds;
    }
    
    public function getModuleSecretKey($idWebsite){
        // get config by id
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('value',$idWebsite);
        // now filter again by is active 
        $scope_id = array();
        foreach ($resource as $val) {
            $scope_id[] = $val->getData('scope_id');
        }
        // now filter with the config set to enabledwebsite = 1
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('scope_id',array("in"=>$scope_id))
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')
                ->addFieldToFilter('value','1')->getFirstItem();
        // use the first element to get the config
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('path','avisverifies/system/secretkey')
                ->addFieldToFilter('scope_id',$resource->getData('scope_id'))
                ->getFirstItem();

        return $resource->getData('value');
    }
    
    // Check if the module is active    
    public function isActive(){
        return (Mage::getStoreConfig('avisverifies/system/enabledwebsite') == 1 &&
            Mage::getStoreConfig(strtolower('avisverifies/system/DISPLAYPRODREVIEWS'))=='yes');
    }
    
    // Check if the default Magento review is disactive
    public function isDefaultDisplay() {
        // defaultReviews / 1 to hide and 0 to show
        // so we need to retun !defaultReviews
        return !Mage::getStoreConfig('avisverifies/extra/defaultReviews');
    }
    
    public function addReviewToProductPage() {
        // default false , change to true
        return Mage::getStoreConfig('avisverifies/extra/addReviewToProductPage');
    }
    
    public function hasjQuery() {
        // default to No = 0 
        return (boolean)Mage::getStoreConfig('avisverifies/extra/hasjQuery');
    }
    
}