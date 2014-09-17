<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class MyAllegro extends Module
{
  public function __construct()
  {
    $this->name = 'myallegro';
    $this->tab = 'administration';
    $this->version = '1.0.0';
    $this->author = 'Michał Nowak';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('My Allegro Integrator');
    $this->description = $this->l('Basic Allegro WebApi functions.');
 
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
    if (!Configuration::get('MYALLEGRO_NAME'))      
      $this->warning = $this->l('No Allegro user name provided');
    if (!Configuration::get('MYALLEGRO_PASS'))      
      $this->warning = $this->l('No Allegro user password provided');
    if (!Configuration::get('MYALLEGRO_APIKEY'))      
      $this->warning = $this->l('No Allegro APIKEY provided');  
  }
  public function install()
  {
if (Shop::isFeatureActive())
    Shop::setContext(Shop::CONTEXT_ALL);
 
  if (!parent::install() ||
    !$this->registerHook('leftColumn') ||
    !$this->registerHook('header') ||
    !Configuration::updateValue('MYALLEGRO_NAME', 'Allegro_user') ||
    !Configuration::updateValue('MYALLEGRO_PASS', 'Allegro_user_password') ||
    !Configuration::updateValue('MYALLEGRO_APIKEY', 'Allegro_user_APIKEY')
  )
    return false;

  return true;
  }


 public function uninstall()
{
  if (!parent::uninstall() ||
    !Configuration::deleteByName('MYALLEGRO_NAME')||
    !Configuration::deleteByName('MYALLEGRO_PASS')||
    !Configuration::deleteByName('MYALLEGRO_APIKEY')
  )
    return false;
 
  return true;
}  
 
public function getContent()
{
    $output = null;
 
    if (Tools::isSubmit('submit'.$this->name))
    {
        $my_module_name = strval(Tools::getValue('MYALLEGRO_NAME'));
        if (!$my_module_name
          || empty($my_module_name)
          || !Validate::isGenericName($my_module_name))
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        else
        {
            Configuration::updateValue('MYALLEGRO_NAME', $my_module_name);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        
        $my_module_pass = strval(Tools::getValue('MYALLEGRO_PASS'));
        if (!$my_module_pass
          || empty($my_module_pass)
          || !Validate::isPasswd($my_module_pass)
          )
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        else
        {
            Configuration::updateValue('MYALLEGRO_PASS', $my_module_pass);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        
      $my_module_apikey = strval(Tools::getValue('MYALLEGRO_APIKEY'));
        if (!$my_module_apikey
          || empty($my_module_apikey)
          || !Validate::isPasswdAdmin($my_module_apikey)
          )
            $output .= $this->displayError($this->l('Invalid Configuration value'));
        else
        {
            Configuration::updateValue('MYALLEGRO_APIKEY', $my_module_apikey);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }  
        
        
        
    }
    return $output.$this->displayForm();
} 
  
 public function displayForm()
{
    // Get default language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
    // Init Fields form array
    $fields_form[0]['form'] = array(
        'legend' => array(
            'title' => $this->l('Settings'),
        ),
        'input' => array(
            array(
                'type' => 'text',
                'label' => $this->l('Allegro user name'),
                'name' => 'MYALLEGRO_NAME',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'password',
                'label' => $this->l('Allegro password'),
                'name' => 'MYALLEGRO_PASS',
                'size' => 20,
                'required' => true
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Allegro WebApi Key'),
                'name' => 'MYALLEGRO_APIKEY',
                'size' => 50,
                'required' => true
            ),
			array(
                'type' => 'textarea',
                'label' => $this->l('Allegro Output'),
                'name' => 'MYALLEGRO_OUTPUT',                
                'required' => false
            )
        ),
        'submit' => array(
            'title' => $this->l('Save'),
            'class' => 'button'
        )
    );
     
    $helper = new HelperForm();
     
    // Module, token and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;
     
    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
        'save' =>
        array(
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
        'back' => array(
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back to list')
        )
    );
     
    // Load current value
    $helper->fields_value['MYALLEGRO_NAME'] = Configuration::get('MYALLEGRO_NAME');
    $helper->fields_value['MYALLEGRO_PASS'] = Configuration::get('MYALLEGRO_PASS');
    $helper->fields_value['MYALLEGRO_APIKEY'] = Configuration::get('MYALLEGRO_APIKEY');
    
 // try Allegro   
    define('COUNTRY_CODE', 1);
 //   define('COUNTRY_CODE', 228);
    
define('WEBAPI_USER_LOGIN', Configuration::get('MYALLEGRO_NAME'));
define('WEBAPI_USER_ENCODED_PASSWORD', base64_encode(hash('sha256', Configuration::get('MYALLEGRO_PASS'), true)));
define('WEBAPI_KEY', Configuration::get('MYALLEGRO_APIKEY'));
 

$options = array( 
        // Stuff for development. 
        //'trace' => 1, 
        //'exceptions' => true, 
        //'cache_wsdl' => WSDL_CACHE_NONE, 
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS); 

//$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
//print_r($options);
//$helper->fields_value['MYALLEGRO_OUTPUT'] = print_r($options);

try {
    
	$soapClient = new SoapClient('https://webapi.allegro.pl/service.php?wsdl', $options);
//	$soapClient = new SoapClient('https://webapi.allegro.pl.webapisandbox.pl/service.php?wsdl', $options);
    $request = array(
        'countryId' => COUNTRY_CODE,
        'webapiKey' => WEBAPI_KEY
    );
    $result = $soapClient->doQueryAllSysStatus($request);
 
    $versionKeys = array();
    foreach ($result->sysCountryStatus->item as $row) {
        $versionKeys[$row->countryId] = $row;
    }
 
    $request = array(
        'userLogin' => WEBAPI_USER_LOGIN,
        'userHashPassword' => WEBAPI_USER_ENCODED_PASSWORD,
        'countryCode' => COUNTRY_CODE,
        'webapiKey' => WEBAPI_KEY,
        'localVersion' => $versionKeys[COUNTRY_CODE]->verKey,
    );
    $session = $soapClient->doLoginEnc($request);
 
    $request = array(
        'sessionId' => $session->sessionHandlePart,
        'pageSize' => 50
    );
 
    $myWonItems = $soapClient->doGetMyWonItems($request);
    //var_dump($myWonItems);
	$helper->fields_value['MYALLEGRO_OUTPUT'] = print_r($myWonItems,1);
 
} catch(Exception $e) {
    //echo $e;
	
	$helper->fields_value['MYALLEGRO_OUTPUT'] = print_r($e,1);
}
   
    
    
    return $helper->generateForm($fields_form);
} 
}
