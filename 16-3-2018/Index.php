<?php


namespace Evdpl\Fitment\Block\Index;

class Index extends \Magento\Framework\View\Element\Template
{

	protected $_productAttributeRepository;

	protected $_productCollectionFactory;


	 public function __construct(        
        \Magento\Framework\View\Element\Template\Context $context,   
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Catalog\Model\Product\Visibility $visibleProduts,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = [] 
    ){        
        parent::__construct($context,$data);
        $this->_productAttributeRepository = $productAttributeRepository;
      	$this->_visibleProduts            = $visibleProduts;
        $this->_productCollectionFactory = $productCollectionFactory;
    } 


	public function getYear(){

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://ws.atdconnect.com/ws/3_4/fitments",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:veh=\"http://api.atdconnect.com/atd/3_4/vehicles\" xmlns:com=\"http://api.atdconnect.com/atd/3_4/common\">\r\n   <soapenv:Header>\r\n      <wsse:Security mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">\r\n            <wsse:UsernameToken atd:clientId=\"ONE_20\" xmlns:atd=\"http://api.atdconnect.com/atd\">\r\n                <wsse:Username>one20api</wsse:Username>\r\n                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">tExE8Aph</wsse:Password>\r\n            </wsse:UsernameToken>\r\n        </wsse:Security>\r\n   </soapenv:Header>\r\n   <soapenv:Body>\r\n    <veh:getVehicleTrimOptionRequest>\r\n            <veh:vehicle>\r\n            \t<veh:year>2017</veh:year>\r\n            \t<veh:make>Ford</veh:make>\r\n            \t<veh:model>C-Max</veh:model>\r\n            \t<veh:trim>C-Max</veh:trim>\r\n            </veh:vehicle>\r\n        </veh:getVehicleTrimOptionRequest>\r\n   </soapenv:Body>\r\n</soapenv:Envelope>\r\n",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: text/xml",
		    "postman-token: eccc7856-c9d0-3c08-cde1-a4ccdf95494d"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}

	public function getOptions($code){
        $manufacturerOptions = $this->_productAttributeRepository->get($code)->getOptions();       
        $values = array();
        foreach ($manufacturerOptions as $manufacturerOption) { 
        	if($this->getProductQuantity($manufacturerOption->getValue(),$code) > 0){
            $values[$manufacturerOption->getValue()] = $manufacturerOption->getLabel();  // Label
            }
        }
        return $values;
    }

    public function getProductQuantity($optionId,$attCode)
    {
        $collection = $this->_productCollectionFactory->create()->setVisibility($this->_visibleProduts->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')->addAttributeToFilter($attCode, ['eq' => $optionId])->addAttributeToFilter('status', array('in' => array(1) ))->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', 'qty!=0')->joinField('position', 'catalog_category_product_index', 'position','product_id=entity_id', 'cat_index.position!=0');
        return $collection->getSize();
    } 

}
