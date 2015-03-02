<?php // Our API CLASS. 
class Netreviews_Avisverifies_Helper_API{ 
    
    protected $request;
    protected $msg = array();
    
    public $checksum = array(
        'errorQuery' => 0,
        'errorDiscussion' => 0,
        'insert' => 0,
        'update' => 0,
        'delete' => 0
    ); 
    public $debug = array();

    public function construct(Mage_Core_Controller_Request_Http $request) {
        $this->request = $request;
        if ($request->getPost('message'))
            $this->msg = unserialize($this->AC_decode_base64($request->getPost('message')));
    }
    
    public function msg($index){
        // check for isset is essential, bcz we could have empty $msg.
        return (isset($this->msg[$index])) ? $this->msg[$index] : null ;
    }
    // convert product reviews format
    public function productReviews($websiteId){
        // if null;
        if (!isset($this->msg['data']))
            return array();
        // else
        $reviews = explode("\n",trim($this->msg['data']));// "Array des lignes (séparateur \n)"
        $tmp = array();
        foreach ($reviews as $line) {
            $column = explode("\t",$line); // "Récupération des colonnes pour chaque ligne, dans un array (séparateur \t = tabulation)"
            $data = array();
            switch ($column[0]) {
                case 'NEW': case 'UPDATE':
                    $data = $this->column($column);
                    $data = array_merge($data, $this->discussion($column));
                    break;
                case 'DELETE':
                    $data = array('error' =>false,'query' => $column[0],'id_product_av' => $column[2],'ref_product'=>$column[4]);
                    $this->checksum['delete']++;
                    break;
                case 'AVG':
                    $data = array('query' => $column[0], 'id_product_av' => $column[1],
                    'ref_product' => $column[2],'rate' => $column[3],'error' =>false,
                    'nb_reviews' => urlencode($column[4]),'horodate_update' => time());
                    $this->checksum['update']++;
                    break;
                default:
                    $data = array('id_product_av' => 0,'error' => true);
                    $this->debug[0] = 'Aucune action (NEW, UPDATE, DELETE) envoyée : ['.$column[0].']';
                    $this->checksum['errorQuery']++;
                    break;
            }
            $data = array_merge($data,array('website_id' => $websiteId));
            $tmp[] = $data;
        }
        if (array_sum($this->checksum) != count($reviews)) {
            $this->debug[] = "Une erreur s'est produite. Le nombre de lignes "
                . " reçues ne correspond pas au nombre de lignes traitées par l'API."
                . " Des données ont quand même pu être enregistré";
        }
        return $tmp;
    }
    // Diffirent column structure according to diffirent query.
    protected function column($column){
        return array(
            'query' => $column[0],
            'id_product_av' => $column[2],
            'ref_product' => $column[4],
            'rate' => $column[7],
            'review' => urlencode(($column[6])),
            'horodate' => $column[5],
            'customer_name' => urlencode((ucfirst($column[8][0]).". " .ucfirst($column[9]))),
            );
    }
    
    protected function discussion($column){
        // "Vérification de la présence d'échanges (nombre d'échange stocké dans 11) et chaque échange est de 3."
        // count($column) equal {$column[0]...$column[11]}-> 12 + 3*($column[11]).
        // NB: all this info is councatunated in a string with (séparateur \t = tabulation).
        // empty return true for: '',0,'0',NULL. and that what we are checking for.
        $echange = (!empty($column[11]))? $column[11] : 0;
        // "Teste si le nombre de paramètres est correct : 12 paramètres sont passés puis 3 par échange"
        if (($echange*3 + 12) == count($column)) {
            $discussion = array();
            for ($i = 0 ; $i < $echange ; $i++) {
                $discussion[] =  array(
                    'horodate' => $column[11+($i*3)+1],
                    'origine' => $column[11+($i*3)+2],
                    'commentaire' => $column[11+($i*3)+3],
                );
            }							
            $this->checksum['insert']++;
            return array('error' =>false,'discussion' => $this->AC_encode_base64(serialize($discussion)));
        }
        else {
            $this->debug[$column[2]] = 'Nombre de paramètres passés par la ligne incohérents (Nb échanges : '.($echange*3).')  : '.(count($column)-12);
            $this->checksum['errorDiscussion']++;
            return array('discussion' => '','error' => true);
        }
    }
    // encode message
    public function AC_encode_base64($sData){ 
        $sBase64 = base64_encode($sData); 
        return strtr($sBase64, '+/', '-_'); 
    } 
    // decode message
    public function AC_decode_base64($sData){ 
        $sBase64 = strtr($sData, '-_', '+/'); 
        return base64_decode($sBase64); 
    }
}