<?php

class Barcala_MassStock_TestController extends Mage_Core_Controller_Front_Action {
    protected $_params;

    public function _construct()
    {
        $this->_params = [
            'consumerKey'     => 'e156b1ab20c7e0c33dfcbb41af1378dc',
            'consumerSecret'  => '674e35e1cc448f5a5a896785fddbc635',
            'callbackUrl'     => $this->_getBaseUrl() . 'massstock/test/callback',
            'siteUrl'         => $this->_getBaseUrl() . 'oauth',
            'requestTokenUrl' => $this->_getBaseUrl() . 'oauth/initiate',
            'authorizeUrl'    => $this->_getBaseUrl() . 'admin/oauth_authorize',
            'accessTokenUrl'  => $this->_getBaseUrl() . 'oauth/token'
        ];
    }

    protected function _storeRequestToken($token)
    {
        Mage::getSingleton('core/session')->setRequestToken(serialize($token));
    }

    protected function _restoreRequestToken()
    {
        return unserialize(Mage::getSingleton('core/session')->getRequestToken());
    }

    protected function _storeAccessToken($token)
    {
        Mage::getSingleton('core/session')->setAccessToken(serialize($token));
    }

    protected function _restoreAccessToken()
    {
        return unserialize(Mage::getSingleton('core/session')->getAccessToken());
    }

    protected function _getBaseUrl()
    {
        return Mage::getUrl();
    }

    public function indexAction() {
        $consumer = new Zend_Oauth_Consumer($this->_params);
        $requestToken = $consumer->getRequestToken();
        $this->_storeRequestToken($requestToken);
        $consumer->redirect();
    }
 
    public function callbackAction()
    {
        $consumer = new Zend_Oauth_Consumer($this->_params);
        $accessToken = $consumer->getAccessToken($_GET, $this->_restoreRequestToken());
        $this->_storeAccessToken($accessToken);
        return $this->_redirect('massstock/test/call');
    }

    public function callAction()
    {
        /* @var Zend_Oauth_Token_Access $accessToken */
        $accessToken = $this->_restoreAccessToken();

        if (!$accessToken) {
            return $this->_redirect('massstock/test/index');
        }

        $restClient = $accessToken->getHttpClient($this->_params);
        $restClient->setUri($this->_getBaseUrl() . 'api/rest/customstockitems');
        $restClient->setHeaders('Accept', 'application/json');
        $restClient->setHeaders('Content-Type', 'application/json');
        $restClient->setMethod(Zend_Http_Client::PUT);
        $restClient->setRawData('[
            {"product_id":"232","qty":"100.0000"},
            {"product_id":"236","stock_id":2,"qty":"200.0000"},
            {"item_id":"384","qty":"300.0000"}
        ]');

        $response = $restClient->request();
        Zend_Debug::dump($response);
    }
 
}
