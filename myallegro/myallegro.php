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
      $this->warning = $this->l('No name provided');
  }
  public function install()
  {
if (Shop::isFeatureActive())
    Shop::setContext(Shop::CONTEXT_ALL);
 
  if (!parent::install() ||
    !$this->registerHook('leftColumn') ||
    !$this->registerHook('header') ||
    !Configuration::updateValue('MYALLEGRO_NAME', 'my friend')
  )
    return false;

  return true;
  }


 public function uninstall()
{
  if (!parent::uninstall() ||
    !Configuration::deleteByName('MYALLEGRO_NAME')
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
    }
    return $output.$this->displayForm();
} 
  
  
}
