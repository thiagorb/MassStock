<?php

class Barcala_MassStock_TestController extends Mage_Core_Controller_Front_Action {
    protected $_params;

    public function _construct()
    {
        $baseUrl = Mage::getUrl();
        $this->_params = [
            'consumerKey'     => '5dbb7f32d520b62c271caca6ca2f24f1',
            'consumerSecret'  => 'b6c2832d55bceb83efd6d8fb5315bdff',
            'callbackUrl'     => $baseUrl . 'massstock/test/callback',
            'siteUrl'         => $baseUrl . 'oauth',
            'requestTokenUrl' => $baseUrl . 'oauth/initiate',
            'authorizeUrl'    => $baseUrl . 'admin/oauth_authorize',
            'accessTokenUrl'  => $baseUrl . 'oauth/token'
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
        $accessToken = $this->_restoreAccessToken();
        $restClient = $accessToken->getHttpClient($this->_params);
        $restClient->setUri(Mage::getUrl('api/rest/stockitems'));
        $restClient->setHeaders('Accept', 'application/json');
        $restClient->setMethod(Zend_Http_Client::GET);
        $response = $restClient->request();
        Zend_Debug::dump($response);
    }
 
}
