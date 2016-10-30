<?php

class Barcala_MassstockTest_TestController extends Mage_Core_Controller_Front_Action {
    /**
     * Store request token
     *
     * @param Zend_Oauth_Token_Request $token
     */
    protected function _storeRequestToken($token)
    {
        Mage::getSingleton('core/session')->setRequestToken(serialize($token));
    }

    /**
     * Retrieve stored request token
     *
     * @return Zend_Oauth_Token_Request|null
     */
    protected function _restoreRequestToken()
    {
        return unserialize(Mage::getSingleton('core/session')->getRequestToken());
    }

    /**
     * Forget stored request token
     */
    protected function _forgetRequestToken()
    {
        Mage::getSingleton('core/session')->setRequestToken(null);
    }

    /**
     * Store access token
     *
     * @param Zend_Oauth_Token_Access $token
     */
    protected function _storeAccessToken($token)
    {
        Mage::getSingleton('core/session')->setAccessToken(serialize($token));
    }

    /**
     * Retrieve stored access token
     *
     * @return Zend_Oauth_Token_Access|null
     */
    protected function _restoreAccessToken()
    {
        return unserialize(Mage::getSingleton('core/session')->getAccessToken());
    }

    /**
     * Forget stored access token
     */
    protected function _forgetAccessToken()
    {
        Mage::getSingleton('core/session')->setAccessToken(null);
    }

    /**
     * Store consumer data
     *
     * @param array $consumerData
     */
    protected function _storeConsumerData($consumerData)
    {
        Mage::getSingleton('core/session')->setConsumerData(serialize($consumerData));
    }

    /**
     * Retrieve stored consumer data
     *
     * @return array|null
     */
    protected function _restoreConsumerData()
    {
        return unserialize(Mage::getSingleton('core/session')->getConsumerData());
    }

    /**
     * Get Magento base url
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        return Mage::getUrl();
    }

    public function indexAction() {
        $consumerData = $this->_restoreConsumerData();
        
        if (!$consumerData) {
            return $this->_redirect('massstock/test/consumer');
        }
        
        $accessToken = $this->_restoreAccessToken();
        
        if (!$accessToken) {
            $consumer = new Zend_Oauth_Consumer($this->_getParams());
            $requestToken = $consumer->getRequestToken();
            $this->_storeRequestToken($requestToken);
            return $consumer->redirect();
        }
    
        $this->loadLayout();
        $this->renderLayout();
    }

    public function callbackAction()
    {
        $consumer = new Zend_Oauth_Consumer($this->_getParams());
        $accessToken = $consumer->getAccessToken($_GET, $this->_restoreRequestToken());
        $this->_storeAccessToken($accessToken);
        return $this->_redirect('massstock/test');
    }
    
    public function consumerAction()
    {
        if (
               !$this->getRequest()->isPost() || 
               empty($this->getRequest()->getPost('consumer_key')) || 
               empty($this->getRequest()->getPost('consumer_secret'))
        ) {
            return $this->loadLayout()->renderLayout();
        }
        
        $this->_storeConsumerData([
            'consumerKey'    => $this->getRequest()->getPost('consumer_key'),
            'consumerSecret' => $this->getRequest()->getPost('consumer_secret'),
        ]);
        
        $this->_forgetRequestToken();
        $this->_forgetAccessToken();
        
        return $this->_redirect('massstock/test');
    }
    
    protected function _ajaxRequest()
    {
        $accessToken = $this->_restoreAccessToken();
        
        if (!$accessToken) {
            return [
                'error' => 'Access token is no longer valid'
            ];
        }
        
        $requestContent = $this->getRequest()->getPost('request_content');
        if (!$requestContent) {
            return [
                'error' => 'Request body cannot be empty'
            ];
        }
        
        $contentType = $this->getRequest()->getPost('content_type');
        if (array_search($contentType, ['application/json', 'text/csv']) === false) {
            return [
                'error' => 'Content type is not valid'
            ];
        }
        
        $restClient = $accessToken->getHttpClient($this->_getParams());
        $restClient->setUri($this->_getBaseUrl() . 'api/rest/customstockitems');
        $restClient->setHeaders('Accept', 'application/json');
        $restClient->setHeaders('Content-Type', $contentType);
        if ($contentType == 'text/csv') {
            $restClient->setHeaders('Content-Delimiter', ';');
        }
        $restClient->setMethod(Zend_Http_Client::PUT);
        $restClient->setRawData($requestContent);
        
        $response = $restClient->request()->getBody();
        
        /*
        Mage::log(
                [
                    'request' => $requestContent,
                    'response' => $response
                ],
                null,
                'ajax_requests.log',
                true
                );
        */
        
        return [
            'response' => json_decode($response)
        ];
    }

    public function callAjaxAction()
    {
        $response = $this->_ajaxRequest();
        $this->getResponse()->setBody(json_encode($response));
    }
    
    protected function _getParams()
    {
        return array_merge(
            [
                'callbackUrl'     => $this->_getBaseUrl() . 'massstock/test/callback',
                'siteUrl'         => $this->_getBaseUrl() . 'oauth',
                'requestTokenUrl' => $this->_getBaseUrl() . 'oauth/initiate',
                'authorizeUrl'    => $this->_getBaseUrl() . 'admin/oauth_authorize',
                'accessTokenUrl'  => $this->_getBaseUrl() . 'oauth/token'
            ],
            $this->_restoreConsumerData()
        );
    }
}
