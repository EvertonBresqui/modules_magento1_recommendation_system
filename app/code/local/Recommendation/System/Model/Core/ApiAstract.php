<?php
class Recommendation_System_Model_Core_ApiAstract {

    private $_domain = null;
    private $_token = null;
    protected $_client = null;

    public static $CURL_OPTS = array(
        CURLOPT_USERAGENT => "MAGENTO.RECOMMENDATION.SYSTEM.MODULE",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 60
    );

    public function auth(){
        $helper = Mage::helper('recommendation_system');

        $route = 'auth';
        $body = array(
            'username' => $helper->getSettingsExport('user'),
            'password' => $helper->getSettingsExport('pass'),
            'sale_group' => (int) $helper->getSettingsExport('sale_group')
        );
        
        $result = $this->post($route, $body);

        if(isset($result['body']->result->result->token)){
            $this->_token = $result['body']->result->result->token;
            return true;
        }
        return false;
    }

    public function setDomain($domain){
        $this->_domain = $domain;
    }

    public function setToken($token){
        $this->_token = $token;
    }

    public function getToken(){
        return $this->_token;
    }
    
    public function get($path, $params = null) {
        $exec = $this->execute($path, null, $params);
    
        return $exec;
    }
    
    public function post($path, $body = null, $params = array()) {
        $body = json_encode($body);

        $opts = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }
    
    public function put($path, $body = null, $params=array()) {
        $body = json_encode($body);
        $opts = array(
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $body
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }
    
    public function delete($path, $params=array()) {
        $opts = array(
            CURLOPT_CUSTOMREQUEST => "DELETE"
        );
    
        $exec = $this->execute($path, $opts, $params);
    
        return $exec;
    }

    public function execute($path, $opts = array(), $params = array()) {
        $uri = $this->make_path($path, $params);
    
        $ch = curl_init($uri);
        curl_setopt_array($ch, self::$CURL_OPTS);
        
        $opts[CURLOPT_HTTPHEADER] = array(
            'Accept: application/json',
            'Content-Type: application/json'
        );
        
        if(!empty($opts))
            curl_setopt_array($ch, $opts);
    
        $return["body"] = json_decode(curl_exec($ch));
        $return["httpCode"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        return $return;
    }
    
    public function make_path($path, $params = array()) {
        if (!preg_match("/^http/", $path)) {
            if (!preg_match("/^\//", $path)) {
                $path = '/'.$path;
            }
            $uri = $this->_domain.$path;
        } else {
            $uri = $path;
        }
    
        if($this->_token != null && $this->_token !=  ''){
            //Adiciona token
            $params['token'] = $this->_token;
        }

        if(!empty($params)) {
            $paramsJoined = array();
    
            foreach($params as $param => $value) {
                $paramsJoined[] = "$param=$value";
            }
            $params = '?'.implode('&', $paramsJoined);
            $uri = $uri.$params;
        }
    
        return $uri;
    }
    
}