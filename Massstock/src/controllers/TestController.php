<?php

class Barcala_Massstock_TestController extends Mage_Core_Controller_Front_Action {
    protected $_params;

    public function _construct()
    {
        $this->_params = [
            'consumerKey'     => '5dbb7f32d520b62c271caca6ca2f24f1',
            'consumerSecret'  => 'b6c2832d55bceb83efd6d8fb5315bdff',
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
        $restClient->setRawData('[{"item_id":"366","qty":17},{"item_id":"367","qty":103},{"item_id":"368","qty":27},{"item_id":"369","qty":28},{"item_id":"370","qty":28},{"item_id":"371","qty":203},{"item_id":"372","qty":25},{"item_id":"373","qty":28},{"item_id":"374","qty":28},{"item_id":"375","qty":3},{"item_id":"376","qty":28},{"item_id":"377","qty":28},{"item_id":"378","qty":8},{"item_id":"379","qty":27},{"item_id":"380","qty":26},{"item_id":"381","qty":28},{"item_id":"382","qty":28},{"item_id":"383","qty":28},{"item_id":"384","qty":303},{"item_id":"385","qty":26},{"item_id":"386","qty":28},{"item_id":"387","qty":28},{"item_id":"388","qty":16},{"item_id":"389","qty":27},{"item_id":"390","qty":28},{"item_id":"391","qty":28},{"item_id":"392","qty":28},{"item_id":"393","qty":28},{"item_id":"394","qty":28},{"item_id":"395","qty":27},{"item_id":"396","qty":28},{"item_id":"397","qty":28},{"item_id":"398","qty":28},{"item_id":"402","qty":21},{"item_id":"403","qty":18},{"item_id":"404","qty":28},{"item_id":"405","qty":28},{"item_id":"406","qty":28},{"item_id":"407","qty":28},{"item_id":"408","qty":28},{"item_id":"409","qty":28},{"item_id":"410","qty":13},{"item_id":"411","qty":28},{"item_id":"412","qty":28},{"item_id":"413","qty":28},{"item_id":"414","qty":28},{"item_id":"415","qty":28},{"item_id":"416","qty":28},{"item_id":"417","qty":28},{"item_id":"418","qty":28},{"item_id":"419","qty":14},{"item_id":"420","qty":-3},{"item_id":"421","qty":10},{"item_id":"422","qty":10},{"item_id":"423","qty":27},{"item_id":"424","qty":28},{"item_id":"425","qty":28},{"item_id":"426","qty":13},{"item_id":"427","qty":27},{"item_id":"428","qty":27},{"item_id":"429","qty":13},{"item_id":"430","qty":24},{"item_id":"431","qty":26},{"item_id":"432","qty":27},{"item_id":"433","qty":28},{"item_id":"434","qty":28},{"item_id":"435","qty":28},{"item_id":"436","qty":28},{"item_id":"437","qty":27},{"item_id":"438","qty":28},{"item_id":"439","qty":28},{"item_id":"440","qty":27},{"item_id":"441","qty":28},{"item_id":"442","qty":28},{"item_id":"443","qty":28},{"item_id":"444","qty":28},{"item_id":"445","qty":22},{"item_id":"446","qty":-7},{"item_id":"447","qty":16},{"item_id":"448","qty":27},{"item_id":"449","qty":27},{"item_id":"460","qty":28},{"item_id":"461","qty":28},{"item_id":"462","qty":28},{"item_id":"463","qty":28},{"item_id":"464","qty":28},{"item_id":"472","qty":10},{"item_id":"473","qty":22},{"item_id":"474","qty":28},{"item_id":"475","qty":28},{"item_id":"476","qty":27},{"item_id":"477","qty":27},{"item_id":"478","qty":28},{"item_id":"479","qty":28},{"item_id":"480","qty":28},{"item_id":"481","qty":9},{"item_id":"482","qty":28},{"item_id":"483","qty":28},{"item_id":"484","qty":28},{"item_id":"485","qty":28}]');

        $response = $restClient->request();
        var_export($response);
    }
 
}
