<?php
class Netreviews_Avisverifies_DialogController extends Mage_Core_Controller_Front_Action
{
    
    public function indexAction() { 
        
        /**************************************
         * GET PARENT IDS FROM CHILD ID:      *
         * Table: catalog_product_super_link  *
         * Where IN childs Id equal ect...    *
         **************************************/
      
        $request = $this->getRequest();
        // decode API Message
        $API = Mage::helper('avisverifies/API');
        $API->construct($request);
        // load Magento config
        $DATA = Mage::helper('avisverifies/Data');
        // check for post
        if ($request->getPost()) {
            // load magento configuration
            $DATA->setup(array('idWebsite'=>$API->msg('idWebsite'),'query'=>$API->msg('query')));
            // check if active
            $IsActiveVar = $this->isActiveModule($DATA,$API->msg('query'));
            // if not active return error.
            if ($IsActiveVar['return'] != 1) {
                // error is serialized and enocdeed
                echo $API->AC_encode_base64(serialize($IsActiveVar));
                exit;
            }
            // now check if the SecurityData.
            $checkSecurityVar = $this->checkSecurityData($DATA,$API);
            // if not valid return error.
            if ($checkSecurityVar['return'] != 1 ) {
                // error is serialized and enocdeed
                echo $API->AC_encode_base64(serialize($checkSecurityVar));
                exit;
            }
            /* ############ DEBUT DU TRAITEMENT ############*/
            // switch case on query type.
            switch ($request->getPost('query')) {
                case 'isActiveModule':
                    $toReply = $IsActiveVar;
                    break;
                case 'setModuleConfiguration' : 
                    $toReply = $this->setModuleConfiguration($DATA,$API);
                    break;	
                case 'getModuleAndSiteConfiguration' : 
                    $toReply = $this->getModuleAndSiteConfiguration($DATA,$API);
                    break;
                case 'getOrders' : 
                    $toReply = $this->getOrders($DATA,$API);
                    break;
                case 'setProductsReviews' : 
                    $toReply = $this->setProductsReviews($DATA,$API);
                    break;	
                case 'truncateTables' : 
                    $toReply = $this->truncateTables($DATA,$API);
                    break;
                case 'getUrlProducts' : 
                    $toReply = $this->getUrlProducts($DATA,$API);
                    break;
                case 'cleanCache' : 
                    $toReply = $this->cleanCache();
                    break;
                default:
                    $reponse['debug']  = "Aucun variable ACTION reçues";
                    $reponse['return'] = 2; //A definir
                    $reponse['query'] = $request->getPost('query');
                    // error is serialized and enocdeed
                    echo $API->AC_encode_base64(serialize($reponse));
                    exit;
            }
            // Affichage du retour des fonctions pour récupération du résultat par AvisVerifies
            echo $API->AC_encode_base64(serialize($toReply));
            exit;
        }
        else
        {
            $reponse['debug'] = "Aucun variable POST reçues";
            $reponse['return'] = 2; //A definir
            $reponse['query'] = "";
            // error is serialized and enocdeed
            echo $API->AC_encode_base64(serialize($reponse));
            exit;
        }
    }       
    
    protected function cleanCache(){
        $mageselc = new Mage_Core_Model_Config();
        $mageselc->cleanCache(); // remove cache
        Mage::app()->cleanCache(); // remove cache
        try { // remove cache
            $allTypes = Mage::app()->useCache();
            foreach ($allTypes as $type => $blah) {
              Mage::app()->getCacheInstance()->cleanType($type);
            }
            $reponse['debug'] = "Cache Cleaned";
            $reponse['return'] = 1; //A definir
            $reponse['query'] = "cleanCache";
        } 
        catch (Exception $e) {
            // do something
            error_log($e->getMessage());
            $reponse['debug'] = "Error in cleanCache ";
            $reponse['return'] = 3; //A definir
            $reponse['query'] = "cleanCache";
        }
        
        return $reponse;
    }


    protected function checkSecurityData($DATA,$API) {
        // Vérification si identifiants non vide
        if (!$DATA->idwebsite OR !$DATA->secretkey) {
            $reponse['debug'] = "Identifiants clients non renseignés sur le module";
            $reponse['message'] = "Identifiants clients non renseignés sur le module";
            $reponse['return'] = 3; //A definir
            $reponse['query'] = 'checkSecurityData';
            return $reponse;
        }
        //Vérification si idWebsite OK
        elseif ($API->msg('idWebsite') !== $DATA->idwebsite) {
            $reponse['message'] = "Clé Website incorrecte";
            $reponse['debug'] = "Clé Website incorrecte";		
            $reponse['return'] = 4; //A definir
            $reponse['query'] = 'checkSecurityData';
            return $reponse;
        }
        //Vérification si Signature OK
        elseif ($DATA->SHA1 !== $API->msg('sign')) {
            $reponse['message'] = "La signature est incorrecte";		
            $reponse['debug'] = "La signature est incorrecte";			
            $reponse['return'] = 5; //A definir
            $reponse['query'] = 'checkSecurityData';	
            return $reponse;
        }
        $reponse['message'] = "Identifiants Client Ok";	
        $reponse['debug'] = "Identifiants Client Ok";	
        $reponse['return'] = 1; //A definir
        $reponse['query'] = 'checkSecurityData';	
        return $reponse;

    }

    protected function setModuleConfiguration($DATA,$API) {
        $mageselc = new Mage_Core_Model_Config();
        $mageselc->cleanCache(); // remove cache
        $allIdShops = $DATA->allShopIds;
        foreach ($allIdShops as $idShop) {
            $mageselc->saveConfig(strtolower('avisverifies/system/PROCESSINIT'),$API->msg('init_reviews_process'),'stores',$idShop);
            //Implode si plusieurs éléments donc is_array
            $ORDERSTATESCHOOSEN = (is_array($API->msg('id_order_status_choosen'))) ? implode(';',$API->msg('id_order_status_choosen')) : $API->msg('id_order_status_choosen');
            $mageselc->saveConfig(strtolower('avisverifies/system/ORDERSTATESCHOOSEN'), $ORDERSTATESCHOOSEN,'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/DELAY'), $API->msg('delay'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/GETPRODREVIEWS'),$API->msg('get_product_reviews'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/DISPLAYPRODREVIEWS'),$API->msg('display_product_reviews'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/SCRIPTFIXE_ALLOWED'),$API->msg('display_fixe_widget'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/SCRIPTFIXE_POSITION'),$API->msg('position_fixe_widget'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/SCRIPTFLOAT_ALLOWED'),$API->msg('display_float_widget'),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/URLCERTIFICAT'),$API->msg('url_certificat'),'stores',$idShop);
            //Implode si plusieurs éléments donc is_array
            $FORBIDDENEMAIL = (is_array($API->msg('forbidden_mail_extension'))) ? implode(';',$API->msg('forbidden_mail_extension')) : $API->msg('forbidden_mail_extension');
            $mageselc->saveConfig(strtolower('avisverifies/system/FORBIDDEN_EMAIL'), $FORBIDDENEMAIL,'stores',$idShop); 
            $mageselc->saveConfig(strtolower('avisverifies/system/SCRIPTFIXE'),str_replace(array("\r\n", "\n"), '', stripslashes(str_replace('\"','"',$API->msg('script_fixe_widget')))),'stores',$idShop);
            $mageselc->saveConfig(strtolower('avisverifies/system/SCRIPTFLOAT'),str_replace(array("\r\n", "\n"), '',  stripslashes(str_replace('\"','"',$API->msg('script_float_widget')))),'stores',$idShop);
            // Force Product Parent ID.
            $mageselc->saveConfig(strtolower('avisverifies/extra/FORCE_PRODUCT_PARENT_ID'),$API->msg('force_product_parent_id'),'stores',$idShop);
        }
        Mage::app()->cleanCache(); // remove cache
        $mageselc->cleanCache(); // remove cache
        
        $reponse['message'] = $this->_getModuleAndSiteInfos($DATA);		
        $reponse['debug'] = "La configuration du site a été mise à jour";		
        $reponse['return'] = 1; //A definir		
        $reponse['query'] = $API->msg('query');

        return $reponse;

    }

    protected function truncateTables($DATA,$API){ 
        $reponse['return'] = 1;
        $reponse['debug']  = "Tables vidées";
        $reponse['message'] = "Tables vidées";
        
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $write->delete($resource->getTableName('avisverifies/reviews'));
        $write->delete($resource->getTableName('avisverifies/average'));

        $reponse['query'] = $this->getRequest()->getPost('query'); // get request post
        Mage::app()->cleanCache();
        return $reponse;
    }

    
    protected function getUrlProducts($DATA,$API){
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $where = array();
        foreach ($API->msg('list_produits') as $id) {
            $where[] = (int)$id;
        }
        $listProduits = array();
        $listProduits = $read->query("SELECT url.product_id as id_product,url.request_path as url,media.value as url_image "
        . " FROM {$resource->getTableName('catalog_product_entity_media_gallery')} media " 
        . " LEFT JOIN {$resource->getTableName('core/url_rewrite')} url ON media.entity_id = url.product_id "
        . " WHERE url.product_id IN (".implode(',', $where).")"
        . " GROUP BY url.product_id")->fetchAll();
        
        $reponse['message']['list_produits'] = $listProduits;
        $reponse['return'] = 1;
        $reponse['debug']  = "product url + image url";
        $reponse['query'] = $this->getRequest()->getPost('query'); // get request post
        return $reponse;
    }


    protected function isActiveModule($DATA,$query){
        if ($DATA->enabledwebsite) {
            if ($DATA->enabledwebsite != 1) {		
                $reponse['debug'] = "Modulé Désactivé";
                $reponse['return'] = 2; //Module désactivé
                $reponse['query'] = $query; 
                return $reponse;
            }
        }
        else {
            $reponse['debug'] = "Modulé Introuvable";
            $reponse['return'] = 3; //Module non installé
            $reponse['query'] = $query; 
            return $reponse;
        }
        $reponse['debug']="Modulé Installé et activé";
        $reponse['return'] = 1; //Module OK
        $reponse['query'] = $query; 
        return $reponse;
    }

    protected function getModuleAndSiteConfiguration($DATA,$API) {		
        $reponse['message'] = $this->_getModuleAndSiteInfos($DATA);
        $reponse['query'] = $API->msg('query');
        $reponse['return'] = (empty($reponse['message']))? 2 : 1; // 2:error, 1:success.
        return $reponse;
    }

    protected function getOrders($DATA,$API){
        $helperData = Mage::helper('avisverifies/Export');
        $helperData->createStoresIds($DATA->allShopIds);
        $helperData->exportStruct($DATA->allowedProducts);
        
        if ($API->msg('force') == 1) {
            if ($API->msg('date_deb') && $API->msg('date_fin')) {
                $from = date("Y-m-d H:i:s",strtotime($API->msg('date_deb')));
                $to = date("Y-m-d H:i:s",strtotime($API->msg('date_fin')));
                $helperData->createExportAPI(array('flag'=>true,'from'=>$from,'to'=>$to));
                $reponse['debug']['mode'] = "[forcé] ".$helperData->count()." commandes récupérées en force du ".$from." au ".$to;
            }
            else { // en cas d'erreur 
                $reponse['debug'][] = "Aucune période renseignée pour la récupération des commandes en mode forcé";
                return $reponse;
            }
        }
        elseif ($DATA->processChoosen == 'onorder') {	
            $helperData->createExportAPI(array('flag'=>true));		
            $reponse['debug']['mode'] = "[onorder] ".$helperData->count()." commandes récupérées";
        }
        elseif ($DATA->processChoosen == 'onorderstatuschange') {			
            if (count($DATA->statusChoosen) >= 1) {			
                $helperData->createExportAPI(array('flag'=>true,'status'=>$DATA->statusChoosen));
                $reponse['debug']['mode'] = "[onorderstatuschange] ".$helperData->count()." commandes récupérées avec statut ".implode(";", $DATA->statusChoosen);
            }
            else { // en cas d'erreur
                $reponse['debug'][] = "Aucun statut n'a été renseigné pour la récupération des commandes en fonction de leur statut";
                $reponse['return'] = 2;
                return $reponse;
            }
        }
        else { // en cas d'erreur
            $reponse['debug'][] = "Aucun évènement onorder ou onorderstatuschange n'a été renseigné pour la récupération des commandes";
            $reponse['return'] = 3;
            return $reponse;
        }
        
        $ordersIdsMarketPlace = $ordersIds = $tmp = array();
        foreach ($helperData->getDataExport() as $order) {
            $customerEmailExtension =  explode('@', $order['email']);
            if (!in_array($customerEmailExtension[1],$DATA->forbiddenMailExtensions)) {
                        // save same order info once.
                        $id = (int)$order['entity_id'];
                        if (empty($tmp[$id])) {
                            $tmp[$id] = array(
                            'id_order' => $order['order_id'],
                            'date_order' => $order['timestamp'], //date timestamp de la table orders    
                            'amount_order' => $order['amount_order'], //date timestamp de la table orders
                            'date_order_formatted' => $order['date'], //date de la table orders formatté			
                            'date_av_getted_order' => $order['date_av_getted_order'], //date de la table order_history de récup par AV
                            'is_flag' => $order['is_flag'], //si la commande est déjà flaggué		
                            'state_order' => $order['status_order'], // we use the status and not the state.
                            'firstname_customer' => $order['prenom'],
                            'lastname_customer' => $order['nom'],
                            'email_customer' =>  $order['email'],
                            );// add order products as array.
                        }
                        // if the product exist then do nothing
                        if (!$this->productExistInArray($tmp[$id],$order['product_id'])) {
                            $tmp[$id]['products'][] = array(
                                'id_product' => $order['product_id'],
                                'name_product' => $order['product_name'],
                                'url' => $order['url'],
                                'url_image' => $order['url_image'],
                            );
                        }
                    $ordersIds[] = $id;
            }
            else {
                $reponse['message']['Emails_Interdits'][] = 'Commande n°'.$order['order_id'].' Email:'.$order['email'];
                $id = (int)$order['entity_id'];
                $ordersIdsMarketPlace[] = $id;
            }
        }
        // always change marketplace orders to 1
        $array_chunk = array_chunk($ordersIdsMarketPlace, 50, true);
        foreach($array_chunk as $array_chunk_ordersIdsMarketPlace){
            $helperData->updateFlag($array_chunk_ordersIdsMarketPlace); 
        } 
        // update Flag db;
        $noFlag = $API->msg('no_flag');
        if(isset($noFlag) && $noFlag == 0){
            $array_chunk = array_chunk($ordersIds, 50, true);
            foreach($array_chunk as $array_chunk_ordersIds){
                $helperData->updateFlag($array_chunk_ordersIds); 
            }
        }
        // return value
        
        $reponse['return'] = 1;
        $reponse['query'] = $this->getRequest()->getPost('query'); // get request post
        $reponse['message']['nb_orders'] = count($tmp);
        $reponse['message']['delay'] = $DATA->delay;	
        $reponse['message']['nb_orders_bloques'] = 0;
        $reponse['message']['list_orders'] = $tmp;
        $reponse['debug']['force'] = $API->msg('force');
        $reponse['debug']['produit'] = $DATA->allowedProducts;
        $reponse['debug']['no_flag'] = $API->msg('no_flag');
        return $reponse;
    }
    
    protected function productExistInArray($array,$id) {
        // first test if product array exist
        if (empty($tmp[$id]['products'])) {
            return false;
        }    
        // else
        foreach ($array as $prod) {
            if ($prod['id_product'] == $id) {
                return true;
            }
        }
        return false;
    }


    protected function setProductsReviews($DATA,$API) {
        $microtime_deb = microtime();
        // we send 1 store view config, we use in_array to check if product is in all stores config
        $reviews = $API->productReviews(reset($DATA->allShopIds));
        foreach ($reviews as $data) {
            if ($data['error']) continue;
            if ($data['query'] == "DELETE") {
                $coll = Mage::getModel('avisverifies/reviews')->getCollection()
                            ->addFieldToFilter('id_product_av',$data['id_product_av'])
                            ->addFieldToFilter('ref_product',$data['ref_product']);
                foreach($coll as $val) {
                    $val->delete();
                }    
            }
            else {
                $resource = Mage::getSingleton('core/resource');
                $write = $resource->getConnection('core_write');
                $read = $resource->getConnection('core_read');
                $table = ($data['query'] == "AVG")? $resource->getTableName('avisverifies/average') : $resource->getTableName('avisverifies/reviews');
                $where = ($data['query'] == "AVG")? " ref_product " : ' id_product_av ';
                $id = ($data['query'] == "AVG")? $data['ref_product'] : $data['id_product_av'];
                $idwebsite = $data['website_id'];
                $select = $read->select()->from($table)
                            ->where($where.' = ?', $id)
                            ->where('website_id = ?',$idwebsite);
                $res = $read->fetchOne($select);
                unset($data['query'],$data['error']); // remove extra fields.
                if ($res == false) {
                    $write->insert($table,$data);
                }
                else {
                    unset($data[$where],$data['website_id']); // remove primary key field.
                    $where = $write->quoteInto($where.' = ? ', $id);
                    $andWhere = $write->quoteInto(' and website_id = ? ', $idwebsite);
                    $write->update($table, $data, $where.$andWhere);
                }
            }
        }
        $microtime_fin = microtime();			
        $reponse['return'] = 1;
        $reponse['query'] = $this->getRequest()->getPost('query'); // get request post
        $reponse['message']['lignes_recues'] = $reviews;
        $reponse['message']['count_line_reviews']= count($reviews);
        $reponse['message']['nb_update_new'] = $API->checksum['insert'] + $API->checksum['update'];
        $reponse['message']['nb_delete'] = $API->checksum['delete'];
        $reponse['message']['nb_errors'] = $API->checksum['errorQuery'] + $API->checksum['errorDiscussion'];

        $reponse['message']['microtime'] = $microtime_fin - $microtime_deb;
        $reponse['debug'] = $API->debug;
        Mage::app()->cleanCache();
        return $reponse;

    }
    
    protected function _getModuleAndSiteInfos($DATA){
        $Magento = Mage::getVersion();
        $module  = "".Mage::getConfig()->getModuleConfig("Netreviews_Avisverifies")->version; // object to string
        $salesModule = "".Mage::getConfig()->getModuleConfig("Mage_Sales")->version; // object to string
        $orderStatutList = Mage::getSingleton('sales/order_config')->getStatuses();
        
        $temp = array(
            'Version_PS' => $Magento,
            'Version_Sales' => $salesModule,
            'Version_Module' => $module,		
            'idWebsite' => $DATA->idwebsite,
            'Nb_Multiboutique' => '',
            'Websites' => '',
            'Id_Website_encours' => '',
            );
        // our configuration
        $champ = array('Delay'=>'DELAY',
            'Statut_choisi'=>'ORDERSTATESCHOOSEN',
            'Initialisation_du_Processus'=>'PROCESSINIT',
            'Recuperation_Avis_Produits'=>'GETPRODREVIEWS',
            'Affiche_Avis_Produits'=>'DISPLAYPRODREVIEWS',
            'Affichage_Widget_Flottant'=>'SCRIPTFLOAT_ALLOWED',
            'Script_Widget_Flottant'=>'SCRIPTFLOAT',
            'Affichage_Widget_Fixe'=>'SCRIPTFIXE_ALLOWED',
            'Position_Widget_Fixe'=>'SCRIPTFIXE_POSITION',
            'Script_Widget_Fixe'=>'SCRIPTFIXE',
            'Emails_Interdits'=>'FORBIDDEN_EMAIL',
            'Enabled_Website'=>'ENABLEDWEBSITE',);
        // load our configuration
        $magesel = Mage::getModel("core/store")->load(reset($DATA->allShopIds));

        foreach($champ as $key=>$champsname) {
            $temp[$key]=$magesel->getConfig('avisverifies/system/'.strtolower($champsname));
        }
        $temp['Force_Parent_id'] = $magesel->getConfig('avisverifies/extra/'.strtolower('FORCE_PRODUCT_PARENT_ID'));
        // JUDO CODE //
        // get all stores + idwebsite
        $mapConfiguration = array('all'=>false,'webistes'=>array());
        $default = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','default')
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')
                ->addFieldToFilter('value','1')->getFirstItem();
        $mapConfiguration['all'] = ($default->getData('value') == 1)? true : false;
        $webistes = Mage::getModel("core/website")->getCollection();
        foreach ($webistes as $web) {
            $mapConfiguration['webistes'][$web->getData('website_id')]['name'] = $web->getData('name');
            $config = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','websites')
                ->addFieldToFilter('scope_id',$web->getData('website_id'))
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')->getFirstItem();
            $mapConfiguration['webistes'][$web->getData('website_id')]['is_active'] = ($config->getData('value') == 1)? true : false;
            $stores = Mage::getModel("core/store")->getCollection()->addFieldToFilter('website_id',$web->getData('website_id'));
            foreach ($stores as $store) {
                $mapConfiguration['webistes'][$web->getData('website_id')]['stores'][$store->getData('store_id')]['name'] = $store->getData('name');
                $storeConfig = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('scope_id',$store->getData('store_id'))
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')->getFirstItem();
                $mapConfiguration['webistes'][$web->getData('website_id')]['stores'][$store->getData('store_id')]['is_active'] = ($storeConfig->getData('value') == 1)? true : false;;
            }
        }        
        // JUDO CODE //
        $temp['Map_Configuration'] = $mapConfiguration;
        $temp['Liste_des_statuts'] = $orderStatutList;
        $temp['Dossier_CSV'] = 'media\avisverifies';
        $temp['Date_Recuperation_Config']= date('Y-m-d H:i:s');

        return $temp;
    }
}