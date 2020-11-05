<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Aramex
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
// @codingStandardsIgnoreFile
namespace Webkul\Aramex\Model;

use Magento\Framework\Module\Dir;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Model\Order\Shipment;
use Magento\Checkout\Model\Session as CheckoutSession;

class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**#@+
     * Carrier Product indicator
     */
    const DHL_CONTENT_TYPE_DOC = 'D';
    const DHL_CONTENT_TYPE_NON_DOC = 'N';
    /**
     * Code of the carrier
     *
     * @var string
     */
    const CODE = 'webkularamex';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * Path to wsdl file of rate service
     *
     * @var string
     */
    protected $_rateServiceWsdl;
     /**
      * Path to wsdl file of rate service
      *
      * @var string
      */
    protected $_addressServiceWsdl;

    /**
     * Path to wsdl file of ship service
     *
     * @var string
     */
    protected $_shipServiceWsdl = null;

    /**
     * Path to wsdl file of track service
     *
     * @var string
     */
    protected $_trackServiceWsdl = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var SessionManager
     */
    protected $coreSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /*
    * string
    */
    private $customsValue;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Aramex\Logger\Logger $aramexLogger,
        SessionManager $coreSession,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->string = $string;
        $this->_objectManager = $objectManager;
        $this->coreSession = $coreSession;
        $this->checkoutSession = $checkoutSession;
        $this->aramexLogger = $aramexLogger;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Webkul_Aramex') . '/wsdl/Aramex/';
        if ($this->getConfigData('sandbox_mode')) {
            $this->_shipServiceWsdl = $wsdlBasePath . 'TestMode/' . 'shipping.wsdl';
            $this->_rateServiceWsdl = $wsdlBasePath . 'TestMode/' . 'aramex-rates-calculator-wsdl.wsdl';
            $this->_trackServiceWsdl = $wsdlBasePath . 'TestMode/' . 'Tracking.wsdl';
        } else {
            $this->_shipServiceWsdl = $wsdlBasePath . 'shipping.wsdl';
            $this->_rateServiceWsdl = $wsdlBasePath . 'aramex-rates-calculator-wsdl.wsdl';
            $this->_trackServiceWsdl = $wsdlBasePath . 'Tracking.wsdl';
        }
    }

    /**
     * Create soap client with selected wsdl
     *
     * @param string $wsdl
     * @param bool|int $trace
     * @return \SoapClient
     */
    protected function _createSoapClient($wsdl, $trace = false)
    {
        ini_set("soap.wsdl_cache_enabled", 0);
        $client = new \SoapClient($wsdl, ['trace' => $trace]);
        return $client;
    }

    /**
     * Create rate soap client
     *
     * @return \SoapClient
     */
    protected function _createRateSoapClient()
    {
        return $this->_createSoapClient($this->_rateServiceWsdl, 1);
    }

    /**
     * Create ship soap client
     *
     * @return \SoapClient
     */
    protected function _createShipSoapClient()
    {
        return $this->_createSoapClient($this->_shipServiceWsdl, 1);
    }

    /**
     * Create track soap client
     *
     * @return \SoapClient
     */
    protected function _createTrackSoapClient()
    {
        return $this->_createSoapClient($this->_trackServiceWsdl, 1);
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $requestAramex = clone $request;
        if (!$this->canCollectRates()) {
            return false;
        }
        $this->setRequest($requestAramex);
        $response = $this->getRequestParam();
        $result = $this->_rateFactory->create();
        
        if (empty($response)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('webkularamex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($response as $method => $values) {
                $rate = $this->_rateMethodFactory->create();
                $currencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
                $handlingFee = $this->getConfigData('handling_fee');
                $price = $values['amount'];
                if ($this->getConfigData('handling_type') == 'P' && $handlingFee) {
                    $price += $price * $handlingFee * 0.01;
                } else {
                    $price += $handlingFee;
                }
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($values['label']);
                $rate->setCost($price);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
         return $result;
    }

    /**
     * Prepare and set request to this instance
     *
     * @param setRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(\Magento\Framework\DataObject $request)
    {
        $this->_request = $request;
        $this->setStore($request->getStoreId());
        $r = new \Magento\Framework\DataObject();
        $r->setUserName($this->getConfigData('user'));
        $r->setPassword($this->getConfigData('password'));
        $r->setAccountNumber($this->getConfigData('account'));
        $r->setAccountEntity($this->getConfigData('entity'));
        $r->setAccountPin($this->getConfigData('pin'));
        $r->setAccountCountryCode($this->getConfigData('accountcountry'));

        $r->setOrigState(
            $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            )
        );

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $r->setOrigCountry($origCountry);

        $r->setOrigCity(
            $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            )
        );
        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        }
        $r->setDestCountry($this->_countryFactory->create()->load($destCountry)->getData('iso2_code'));

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        if ($request->getDestCity()) {
            $r->setDestCity($request->getDestCity());
        }

        $r->setDestState($request->getDestRegionId());
        if ($origCountry == $destCountry) {
            $r->setProductGroup('DOM');
            $r->setProductType('ONP');
        } else {
            $r->setProductGroup('EXP');
            $r->setProductType('PPX');
        }
        $this->setRawRequest($r);

        return $this;
    }

    public function getRequestParam()
    {
        $r = $this->_rawRequest;
        $request = $this->_request;
        $pices = 0;
        $weight = 0;
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
            } else {
                $pices ++;
                $weight = $weight + $item->getWeight()*$item->getQty();
            }
        }
        $params = [
            'ClientInfo'  => [
                            'AccountCountryCode'    => $r->getAccountCountryCode(),
                            'AccountEntity'         => $r->getAccountEntity(),
                            'AccountNumber'         => $r->getAccountNumber(),
                            'AccountPin'            => $r->getAccountPin(),
                            'UserName'              => $r->getUserName(),
                            'Password'              => $r->getPassword(),
                            'Version'               => 'v1.0'
                        ],
                                    
            'OriginAddress' => [
                            'StateOrProvinceCode'   => $r->getOrigState(),
                            'City'                  => $r->getOrigCity(),
                            'PostCode'              => $r->getOrigPostal(),
                            'CountryCode'           => $r->getOrigCountry(),
                        ],
                                    
            'DestinationAddress' => [
                            'StateOrProvinceCode'   =>$r->getDestState(),
                            'City'                  => $r->getDestCity(),
                            'PostCode'              => self::USA_COUNTRY_ID == $r->getDestCountry()?
                                                        substr($r->getDestPostal(), 0, 5):
                                                        $r->getDestPostal(),
                            'CountryCode'           => $r->getDestCountry(),
                        ],
            'ShipmentDetails' => [
                            'ProductType'            => $r->getProductType(),
                            'PaymentType'            => 'P',
                            'ProductGroup'           => $r->getProductGroup(),
                            'ActualWeight'           => [
                                'Value' => $weight,
                                'Unit' => $this->getConfigData('unit_of_measure') == 'L'?'LB':'KG',
                            ],
                            'ChargeableWeight'       => [
                                'Value' => $weight,
                                'Unit' => $this->getConfigData('unit_of_measure') == 'L'?'LB':'KG',
                            ],
                            'NumberOfPieces'         => $pices
                        ]
        ];

        $priceArr  =[];
        $allowedMethodsKey = 'international_methods';
        $allowedMethods = $this->_objectManager->get(
            'Webkul\Aramex\Model\Source\InternationalMethods'
        )->toKeyArray();

        if ($r->getOrigCountry() == $r->getDestCountry()) {
            $allowedMethods = $this->_objectManager->get(
                'Webkul\Aramex\Model\Source\DomasticMethods'
            )->toKeyArray();
            $allowedMethodsKey = 'domestic_methods';
        }
        $adminAllowedMethods = explode(',', $this->getConfigData($allowedMethodsKey));
        $adminAllowedMethods = array_flip($adminAllowedMethods);
        $allowedMethods = array_intersect_key($allowedMethods, $adminAllowedMethods);
        $paymentMethod = $this->checkoutSession->getQuote()->getPayment()->getMethod();
        $client = $this->_createRateSoapClient();
        foreach ($allowedMethods as $methodValue => $title) {
            try {
                $results = $client->CalculateRate($params);
                if ($results->HasErrors) {
                    if (count($results->Notifications->Notification) > 1) {
                        $error="";
                        foreach ($results->Notifications->Notification as $notifyError) {
                            $error.=__('Aramex: ' . $notifyError->Code .' - '. $notifyError->Message)."<br>";
                        }
                        $response['type']='error';
                        $response['error'] = $error;
                        $this->aramexLogger->info($error);
                    } else {
                        $results->Notifications->Notification->Message;
                        $error =__(
                            'Aramex: ' . $results->Notifications->Notification->Code .
                            ' - '.
                            $results->Notifications->Notification->Message
                        );
                        $this->aramexLogger->info($error);
                        $response['type']='error';
                        $response['error'] = $error;
                    }
                    continue;
                } else {
                    $response['type']='success';
                    $codCharge = 0;

                    if ($this->coreSession->getAramexCodAmount() &&
                        $this->coreSession->getAramexCodMethod()) {
                        $codCharge = $this->coreSession->getAramexCodAmount();
                    }
                    $value = $results->TotalAmount->Value + $codCharge;
                    $priceArr[$methodValue] = ['label' => $title, 'amount'=> $value];
                }
            } catch (\Exception $e) {
                    $response['type']='error';
                    $this->aramexLogger->info($e->getMessage());
                    $response['error'] = $e->getMessage();
            }
        }
        $this->_debug($response);

        return $priceArr;
    }

    /**
     * Get result of request
     *
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Get allowed shipping methods.
     *
     * @return string[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedMethods()
    {
        return ['webkularamex' => $this->getConfigData('name')];
    }
     /**
      * Get tracking
      *
      * @param string|string[] $trackings
      * @return Result|null
      */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $this->_getXmlTrackingInfo($trackings);

        return $this->_result;
    }
    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    protected function _getXmlTrackingInfo($trackings)
    {
        foreach ($trackings as $tracking) {
            $this->_parseXmlTrackingResponse($tracking);
        }
    }

    /**
     * Parse xml tracking response
     *
     * @param string $trackingvalue
     * @param string $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _parseXmlTrackingResponse($trackingvalue)
    {
        $resultArr = [];

        $result = $this->_trackFactory->create();

        $defaults = $this->getDefaults();

        $client = $this->_createTrackSoapClient();
        $params = [
            'ClientInfo' => [
                'AccountCountryCode'    => $this->getConfigData('accountcountry'),
                'AccountEntity'         => $this->getConfigData('entity'),
                'AccountNumber'         => $this->getConfigData('account'),
                'AccountPin'            => $this->getConfigData('pin'),
                'UserName'              => $this->getConfigData('user'),
                'Password'              => $this->getConfigData('password'),
                'Version'               => 'v1.0'
            ],
            'Shipments' =>  [$trackingvalue],
        ];
        $response = $client->TrackShipments($params);
        
        if (is_object($response) && !$response->HasErrors) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier('webkularamex');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            if (!empty(
                $response->TrackingResults
                ->KeyValueOfstringArrayOfTrackingResultmFAkxlpY
                ->Value
                ->TrackingResult
            )
            ) {
                $tracking->setTrackSummary(
                    $this->getTrackingInfoTable(
                        $response->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value
                    )
                );
            } else {
                $tracking->setTrackSummary(
                    'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.'
                );
            }
            $result->append($tracking);
        } else {
            $errorTitle = '';
            foreach ($response->Notifications as $notification) {
                $errorTitle .= '<b>' . $notification->Code . '</b>' . $notification->Message;
            }
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier('webkularamex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        }
        $this->_result = $result;
    }
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getTrackingInfoTable($HAWBHistory)
    {
        $resultTable = '<table summary="Item Tracking"  class="data-table">';
        $resultTable .= '<col width="1">
                          <col width="1">
                          <col width="1">
                          <col width="1">
                          <thead>
                          <tr class="first last">
                          <th>Location</th>
                          <th>Action Date/Time</th>
                          <th class="a-right">Tracking Description</th>
                          <th class="a-center">Comments</th>
                          </tr>
                          </thead><tbody>';

        foreach ($HAWBHistory as $HAWBUpdate) {
            $resultTable .= '<tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr>';
        }
        $resultTable .= '</tbody></table>';

        return $resultTable;
    }
    /**
     * Get tracking response
     *
     * @return string
     */
    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof \Magento\Shipping\Model\Tracking\Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (!empty($data['track_summary'])) {
                            $statuses .= __($data['track_summary']);
                        } else {
                            $statuses .= __('Empty response');
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = __('Empty response');
        }

        return $statuses;
    }
    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        return [
            self::DHL_CONTENT_TYPE_DOC => __('Documents'),
            self::DHL_CONTENT_TYPE_NON_DOC => __('Non Documents')
        ];
    }

    /**
     * Returns value of given variable
     *
     * @param string|int $origValue
     * @param string $pathToValue
     * @return string|int|null
     */
    protected function _getDefaultValue($origValue, $pathToValue)
    {
        if (!$origValue) {
            $origValue = $this->_scopeConfig->getValue(
                $pathToValue,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            );
        }

        return $origValue;
    }
    /**
     * return service type for shipment.
     *
     * @return string
     */
    protected function _getServiceCode()
    {
        $request = $this->_rawRequest;
        $order = $request->getOrderShipment()->getOrder();
        $shippingmethod = explode('webkularamex_', $order->getShippingMethod());
        return $shippingmethod[1];
    }

    /**
     * Do shipment request to carrier web service,
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $this->_mapPackageToShipment($request);
        $this->setShipemntRequest($request);
        return $this->_createShipmentRequest();
    }
    /**
     * Make DHL Shipment Request.
     *
     * @return string xml
     */
    protected function _createShipmentRequest()
    {
        $request = $this->_rawRequest;
        $order = $request->getOrderShipment()->getOrder();
        
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $productGroup = 'EXP';
        $productType = 'PPX';
        if ($request->getDestCountryId() ==
            $request->getOrigCountryId()
        ) {
            $productGroup = 'DOM';
            $productType = $this->_getServiceCode();
        }
        $params = [
            'Shipments' => [
                'Shipment' => [
                    'Shipper' => [
                        'Reference1' => $order->getIncrementId(),
                        'AccountNumber' => $request->getAccountNumber(),
                        'PartyAddress' => [
                            'Line1' => $request->getOrigStreet(),
                            'Line2' => $request->getOrigStreetLine2(),
                            'City' => $request->getOrigCity(),
                            'StateOrProvinceCode' => $request->getOrigState(),
                            'PostCode' => $request->getOrigPostal(),
                            'CountryCode' => $request->getOrigCountryId()
                        ],
                        'Contact' => [
                            'PersonName' => $request->getOrigPersonName(),
                            'CompanyName' => $request->getOrigCompanyName(),
                            'PhoneNumber1' => $request->getOrigPhoneNumber(),
                            'CellPhone' => $request->getOrigPhoneNumber(),
                            'EmailAddress' => $request->getOrigEmail(),
                        ]
                    ],
                    'Consignee' => [
                        'Reference1' => $order->getIncrementId(),
                        'AccountNumber' => '',
                        'PartyAddress' => [
                            'Line1' => $request->getDestStreet(),
                            'Line2' => $request->getDestStreetLine2(),
                            'City' => $request->getDestCity(),
                            'StateOrProvinceCode' => $request->getDestState(),
                            'PostCode' => $request->getDestPostal(),
                            'CountryCode' => $request->getDestCountryId()
                        ],
                        'Contact' => [
                            'PersonName' => $request->getDestPersonName(),
                            'CompanyName' => $request->getDestCompanyName()
                                ?$request->getDestCompanyName()
                                :$request->getDestPersonName(),
                            'PhoneNumber1' => $request->getDestPhoneNumber(),
                            'CellPhone' => $request->getDestPhoneNumber(),
                            'EmailAddress' => $request->getDestEmail()
                        ]
                    ],
                    'ThirdParty' => [
                        'Reference1' => $order->getIncrementId(),
                        'AccountNumber' => $request->getAccountNumber(),
                        'PartyAddress' => [
                            'Line1' => $request->getOrigStreet(),
                            'Line2' => $request->getOrigStreetLine2(),
                            'City' => $request->getOrigCity(),
                            'StateOrProvinceCode' => $request->getOrigState(),
                            'PostCode' => $request->getOrigPostal(),
                            'CountryCode' => $request->getOrigCountryId()
                        ],
                        'Contact' => [
                            'PersonName' => $request->getOrigPersonName(),
                            'CompanyName' => $request->getOrigCompanyName(),
                            'PhoneNumber1' => $request->getOrigPhoneNumber(),
                            'CellPhone' => $request->getOrigPhoneNumber(),
                            'EmailAddress' => $request->getOrigEmail(),
                        ]
                    ],
                    'Reference1' => $order->getIncrementId(),
                    'TransportType' => 0,
                    'ShippingDateTime' => time(),
                    'DueDate' => time() + (7 * 24 * 60 * 60),

                ]
            ],
            'ClientInfo' => [
                'AccountCountryCode' => $request->getAramexCountryId(),
                'AccountEntity' => $request->getAccountEntity(),
                'AccountNumber' => $request->getAccountNumber(),
                'AccountPin' => $request->getAccountPin(),
                'UserName' => $request->getUserName(),
                'Password' => $request->getPassword(),
                'Version' => 'v1.0',
                'Source' => 31,
            ],
            'Transaction' => [
                'Reference1' => $order->getIncrementId(),
            ],
            'LabelInfo' => [
                'ReportID'  => 9729,
                'ReportType' => 'RPT',
            ]
        ];

        $params['Shipments']['Shipment']['Details'] = [
            'ActualWeight' => [
                'Value' => $request->getWeight(),
                'Unit' => $this->getConfigData('unit_of_measure') == 'L'?'LB':'KG',
            ],
            'ProductGroup' => $productGroup,
            'ProductType' => $productType,
            'PaymentType' => 3,
            'PaymentOptions' => '',
            'NumberOfPieces' => $request->getQty(),
            'DescriptionOfGoods' => $request->getItemDesc(),
            'GoodsOriginCountry' => $request->getOrigCountryId(),
            'Services' => '',
        ];
        
        if ($this->getConfigData('cod') == 1 &&
            ($paymentMethod == 'webkularamex' && $request->getOrigCountryId() == $request->getDestCountryId())
        ) {
            $params['Shipments']['Shipment']['Details']['Services'] = 'CODS';
            $params['Shipments']['Shipment']['Details']['CashOnDeliveryAmount'] =[
                'Value'                 => $request->getTotalValue() + $request->getShippingCharge(),
                'CurrencyCode'          =>  $order->getOrderCurrencyCode()
            ];
            $params['Shipments']['Shipment']['Details']['CustomsValueAmount'] = [
                'Value'                 => 0,
                'CurrencyCode'          => $order->getOrderCurrencyCode()
            ];
        } else {
            $params['Shipments']['Shipment']['Details']['Services'] = '';
            $params['Shipments']['Shipment']['Details']['CustomsValueAmount'] = [
                'Value'                 => $this->customsValue,
                'CurrencyCode'          => $order->getOrderCurrencyCode()
            ];
        }
        
        try {
            $response = $this->_createShipSoapClient()->CreateShipments($params);
            
            $result = new \Magento\Framework\DataObject();
            if ($response->HasErrors) {
                $debugData = [
                    'request' => $params,
                    'result' => ['error' => '', 'code' => '', 'xml' => $response],
                ];
                
                if (empty($response->Shipments)) {

                    if (count($response->Notifications->Notification) > 1) {
                        foreach ($response->Notifications->Notification as $notifyError) {
                            $debugData['result']['code'] .= $notifyError->Code . '; ';
                            $debugData['result']['error'] .= $notifyError->Message . '; ';
                        }
                        $this->aramexLogger->info($debugData['result']['error']);
                    } else {
                        $debugData['result']['code'] = $response->Notifications->Notification->Code . ' ';
                        $debugData['result']['error'] = $response->Notifications->Notification->Message . ' ';
                        $this->aramexLogger->info($response->Notifications->Notification->Message);
                    }
                } else {
                    if (count($response->Shipments->ProcessedShipment->Notifications->Notification) > 1) {
                        foreach ($response->Shipments->ProcessedShipment->Notifications->Notification as $notifyError) {
                            $debugData['result']['code'] .= $notifyError->Code . '; ';
                            $debugData['result']['error'] .= $notifyError->Message . '; ';
                        }
                    } else {
                        $debugData['result']['code'] = $response->Shipments
                            ->ProcessedShipment
                            ->Notifications
                            ->Notification->Code . ' ';
                        $debugData['result']['error'] = $response->Shipments
                            ->ProcessedShipment
                            ->Notifications
                            ->Notification
                            ->Message . ' ';
                    }
                }
                $this->_debug($debugData);
                throw new \Magento\Framework\Exception\LocalizedException(__((string)$debugData['result']['error']));
            } else {
                $shippingLabelContent = $response->Shipments->ProcessedShipment->ShipmentLabel->LabelFileContents;
                $trackingNumber = $response->Shipments->ProcessedShipment->ID;
                $result->setShippingLabelContent($shippingLabelContent);
                $result->setTrackingNumber($trackingNumber);
            }
        } catch (\Exception $e) {
            $this->aramexLogger->info($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__((string)$e->getMessage()));
        }
        return $result;
    }
     /**
      * Map request to shipment
      *
      * @param \Magento\Framework\DataObject $request
      * @return void
      * @throws \Magento\Framework\Exception\LocalizedException
      */
    protected function _mapPackageToShipment(\Magento\Framework\DataObject $request)
    {
        $request->setOrigCountryId($request->getShipperAddressCountryCode());
        $this->setRawRequest($request);
        $customsValue = 0;
        $packageWeight = 0;
        $totalPrice = 0;
        $itemsQty = 0;
        $itemsDesc = [];
        $packages = $request->getPackages();
        foreach ($packages as &$piece) {
            $params = $piece['params'];
            $weightUnits = $piece['params']['weight_units'];
            $customsValue += $piece['params']['customs_value'];
            $packageWeight += $piece['params']['weight'];

            foreach ($piece['items'] as $item) {
                $totalPrice += $item['price'];
                $itemsQty += $item['qty'];
                $itemsDesc[] = $item['name'];
            }
        }
        $this->customsValue = $customsValue;
        $request->setPackages($packages)
            ->setPackageWeight($packageWeight)
            ->setPackageValue($customsValue)
            ->setValueWithDiscount($customsValue)
            ->setPackageCustomsValue($customsValue)
            ->setQty($itemsQty)
            ->setTotalValue($totalPrice)
            ->setItemDesc(implode(',', $itemsDesc))
            ->setFreeMethodWeight(0);
    }

    /**
     * Prepare and set request in property of current instance
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setShipemntRequest(\Magento\Framework\DataObject $request)
    {
        $this->_request = $request;
        $order = $request->getOrderShipment()->getOrder();
        $this->setStore($request->getStoreId());

        $paramObject = new \Magento\Framework\DataObject();

        //set default credentials
        $paramObject->setUserName($this->getConfigData('user'));
        $paramObject->setPassword($this->getConfigData('password'));
        $paramObject->setAccountNumber($this->getConfigData('account'));
        $paramObject->setAccountPin($this->getConfigData('pin'));
        $paramObject->setAccountEntity($this->getConfigData('entity'));
        $paramObject->setAramexCountryId($this->getConfigData('accountcountry'));
        $paramObject->setIsGenerateLabelReturn($request->getIsGenerateLabelReturn());

        $paramObject->setStoreId($request->getStoreId());

        if ($request->getDestPostcode()) {
            $paramObject->setDestPostal($request->getDestPostcode());
        }

        $paramObject->setOrigCountry(
            $this->_getDefaultValue($request->getOrigCountry(), Shipment::XML_PATH_STORE_COUNTRY_ID)
        )->setOrigCountryId(
            $this->_getDefaultValue($request->getOrigCountryId(), Shipment::XML_PATH_STORE_COUNTRY_ID)
        );

        $shippingWeight = $request->getPackageWeight();
        $destAddress = $request->getOrderShipment()->getShippingAddress();
        $street = $destAddress->getStreet();
        $street[1] = isset($street[1])?$street[1]:'';

        $originStreet2 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $paramObject->getStoreId()
        );
        $paramObject->setValue(round($request->getPackageValue(), 2))
            ->setValueWithDiscount($request->getPackageValueWithDiscount())
            ->setCustomsValue($request->getPackageCustomsValue())
            ->setDestStreet($this->string->substr(str_replace("\n", '', $street[0]), 0, 35))
            ->setDestStreetLine2($street[1])
            ->setDestCity($destAddress->getCity())
            ->setDestPhoneNumber($destAddress->getTelephone())
            ->setDestPersonName($destAddress->getName())
            ->setDestCompanyName($destAddress->getCompany())
            ->setDestEmail($destAddress->getEmail())
            ->setDestCountryId($destAddress->getCountryId())
            ->setDestState($destAddress->getRegionId())
            ->setDestPostal($destAddress->getPostcode())
            ->setOrigCompanyName($request->getShipperContactCompanyName())
            ->setOrigCity($request->getShipperAddressCity())
            ->setOrigPhoneNumber($request->getShipperContactPhoneNumber())
            ->setOrigPersonName($request->getShipperContactPersonName())
            ->setOrigEmail(
                $this->_scopeConfig->getValue(
                    'trans_email/ident_general/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $paramObject->getStoreId()
                )
            )
            ->setOrigPostal($request->getShipperAddressPostalCode())
            ->setOrigStreetLine2($originStreet2);

        $paramObject->setOrigStreet(
            $request->getShipperAddressStreet() ? $request->getShipperAddressStreet() : $originStreet2
        );

        $paramObject->setOrigState($request->getShipperAddressStateOrProvinceCode());

        $shippingCharge = $order->getShippingAmount();

        $paramObject->setWeight($shippingWeight)
            ->setQty($request->getQty())
            ->setTotalValue($request->getTotalValue())
            ->setItemDesc($request->getItemDesc())
            ->setOrderShipment($request->getOrderShipment())
            ->setShippingCharge($shippingCharge);

        $paramObject->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($paramObject);

        return $this;
    }
    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }
        $result = $this->_doShipmentRequest($request);

        $response = new \Magento\Framework\DataObject(
            [
                'info' => [
                    [
                        'tracking_number' => $result->getTrackingNumber(),
                        'label_content' => $result->getShippingLabelContent(),
                    ],
                ],
            ]
        );

        $request->setMasterTrackingId($result->getTrackingNumber());

        return $response;
    }

    /**
     * Retrieve minimum allowed value for dimensions in given dimension unit
     *
     * @param string $dimensionUnit
     * @return int
     */
    protected function _getDimension($dimensionUnit)
    {
        return $dimensionUnit == "CENTIMETER" ? self::DIMENSION_MIN_CM : self::DIMENSION_MIN_IN;
    }
}
