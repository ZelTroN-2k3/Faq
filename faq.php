<?php
/**
 * Module Vip Card for Prestashop 1.6.x.x
 *
 * NOTICE OF LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 *
 * @author bortonecesario <cesario.bortone@hotmail.it>
 * @Copyright (c) 2016 bortonecesario
 * @version   1.0.0
 * @license   Free
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'faq/classes/FaqBlock.php';

class faq extends Module
{
	    /** @var string */
    public $folder_path;
    
    public $html = '';
    protected $_errors = array();
    
    /**
     * @see Module::__construct()
     */    
    public function __construct()
    {
        $this->name                   = 'faq';
        $this->tab                    = 'front_office_features';
        $this->version                = '1.0.1';
        $this->author                 = 'Bortone Cesario / ZelTroN2k3';
        $this->need_instance          = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap              = true;
        
        parent::__construct();
        
        $this->folder_path = $this->local_path;
        $this->displayName = $this->l('FAQ');
        $this->description = $this->l('This module allows you to create a page to answer frequently asked questions.');
        
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	    
	if (!Configuration::get('faq')) {
            $this->warning = $this->l('No name provided');
        }     
    }
    
    /**
     * @see Module::install()
     */    
    public function install()
    {
        
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);
        
        if (!parent::install())
            return false;

		/**
		* Creates tables
		*
		* @return bool
		*/        
        $sql = array();
        require_once(dirname(__FILE__) . '/sql/install.php');
        foreach ($sql as $sq):
            if (!Db::getInstance()->Execute($sq))
                return false;
        endforeach;
        
        $sql     = 'select max(id_linksmenutop) idl from ' . _DB_PREFIX_ . 'linksmenutop where id_shop=' . (int) Context::getContext()->shop->id;
        $content = Db::getInstance()->executeS($sql);
    	$link = Context::getContext()->link;
        $ind = $link->getModuleLink('faq','faqpage');
        foreach ($content as $key => $value) {

			foreach (Language::getLanguages(false) as $lang) {
				    $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'linksmenutop_lang` (id_linksmenutop,id_lang,id_shop, label,link) 
                    VALUES(' . $value['idl'] . ',' .$lang['id_lang']. ',' . (int) Context::getContext()->shop->id . ',"FAQ","'.$ind.'")';
						if (!Db::getInstance()->Execute($sql))
							return false;
				}

        }
        
        $shops = Shop::getContextListShopID();
        $conf  = null;
        
        if (count($shops) > 1) {
            foreach ($shops as $key => $shop_id) {
                $shop_group_id = Shop::getGroupFromShop($shop_id);
                $conf .= (string) ($key > 1 ? ',' : '') . Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
            }
        } else {
            $shop_id       = (int) $shops[0];
            $shop_group_id = Shop::getGroupFromShop($shop_id);
            $conf          = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
        }
        
        Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', $conf . ',LNK' . $value['idl']);
		foreach (Language::getLanguages(false) as $lang) 
		{
			$footer_text = Configuration::get('FOOTER_CMS_TEXT_'.$lang['id_lang']);
			Configuration::updateValue('FOOTER_CMS_TEXT_'.(int)$lang['id_lang'], 
            	'<a href="'.$ind.'" title="'.$this->displayName.'">'.$this->displayName.'</a><br><br>' . $footer_text, true);
		}
        
		Tools::clearSmartyCache();
		Tools::clearXMLCache();
		Media::clearCache();
        return true;
    }

    /**
     * @see Module::uninstall()
     */    
    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('faq')) {
            return false;
        }

        $sql     = 'select id_linksmenutop from ' . _DB_PREFIX_ . 'linksmenutop_lang where label="FAQ" and id_shop='
        		. (int) Context::getContext()->shop->id;
        $content = Db::getInstance()->executeS($sql);
        foreach ($content as $key => $value) {
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'linksmenutop` WHERE id_linksmenutop =' . $value['id_linksmenutop'] ;
            if (!Db::getInstance()->Execute($sql))
                return false;
        
        	$sql = 'DELETE FROM `' . _DB_PREFIX_ . 'linksmenutop_lang` WHERE label="FAQ"' ;
            if (!Db::getInstance()->Execute($sql))
                return false;
		}

		$link = Context::getContext()->link;
        $ind = $link->getModuleLink('faq','faqpage');
        
        foreach (Language::getLanguages(false) as $lang) 
		{
			Configuration::updateValue('FOOTER_CMS_TEXT_'.(int)$lang['id_lang'], '', true);
		}
        
		$shops = Shop::getContextListShopID();
        $conf  = null;
        
        if (count($shops) > 1) {
            foreach ($shops as $key => $shop_id) {
                $shop_group_id = Shop::getGroupFromShop($shop_id);
                $conf .= (string) ($key > 1 ? ',' : '') . Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
            }
        } else {
            $shop_id       = (int) $shops[0];
            $shop_group_id = Shop::getGroupFromShop($shop_id);
            $conf          = Configuration::get('MOD_BLOCKTOPMENU_ITEMS', null, $shop_group_id, $shop_id);
        }
		Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', trim($conf, ",LNK". $value['id_linksmenutop']));
        
        // Uninstall Tabs
		$sql = array();
        require_once(dirname(__FILE__) . '/sql/uninstall.php');
        foreach ($sql as $sq):
            if (!Db::getInstance()->Execute($sq))
                return false;
        endforeach;
		
		Tools::clearSmartyCache();
		Tools::clearXMLCache();
		Media::clearCache();

        
        return true;
    }

    /**
     * Creates a configuration page
     *
     * @return string
     */
    /**
     * Load the configuration form
     */     
    public function getContent()
    {
		$output = '';
		
        $this->context->smarty->assign(
			array(
				'ps_version' => _PS_VERSION_,
				'module_dir' => $this->_path,
				'module_version' => $this->version
			)
		);
		    
        $id_qa = (int) Tools::getValue('id_qa');
        
        if (Tools::isSubmit('savefaq')) {
            if (!Tools::getValue('answer_' . (int) Configuration::get('PS_LANG_DEFAULT'), false))
                return $this->html . $this->displayError($this->l('You must fill in all fields.')) . $this->renderForm();
            elseif ($this->processSaveFaq())
                return $this->html . $this->renderList();
            else
                return $this->html . $this->renderForm();
        } elseif (Tools::isSubmit('updatefaq') || Tools::isSubmit('addfaq')) {
            $this->html .= $this->renderForm();
            return $this->html;
        } else if (Tools::isSubmit('deletefaq')) {
            $this->deletefaq();
            Tools::redirectAdmin(AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . 
            		Tools::getAdminTokenLite('AdminModules'));
        } else {
            //$this->html .= $this->renderList();
            $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
            //return $this->html;
            return $output . $this->renderList();
        }
    }
    
    protected function renderList()
    {
        $this->fields_list          = array();
        $this->fields_list['id_qa'] = array(
            'title' => $this->l('id'),
            'type' => 'text',
            'search' => false,
            'orderby' => false
        );
        
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
            $this->fields_list['shop_name'] = array(
                'title' => $this->l('Shop'),
                'type' => 'text',
                'search' => false,
                'orderby' => false
            );
        
        $this->fields_list['question'] = array(
            'title' => $this->l('Question text'),
            'type' => 'text',
            'search' => false,
            'orderby' => false
        );
        
        $helper                     = new HelperList();
        $helper->shopLinkType       = '';
        $helper->simple_header      = false;
        $helper->identifier         = 'id_qa';
        $helper->actions            = array(
            'edit',
            'delete'
        );
        $helper->show_toolbar       = true;
        $helper->imageType          = 'jpg';
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . $this->name . '&token=' 
            	. Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );
        
        $helper->title        = $this->displayName;
        $helper->table        = $this->name;
        $helper->token        = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        
        $content = $this->getListContent($this->context->language->id);
        
        return $helper->generateList($content, $this->fields_list);
    }
    
    protected function getListContent($id_lang = null)
    {
        if (is_null($id_lang))
            $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        $sql = 'SELECT r.`id_qa`, rl.`question`, rl.`answer`, s.`name` as shop_name
            FROM `' . _DB_PREFIX_ . 'faq` r
            LEFT JOIN `' . _DB_PREFIX_ . 'faq_lang` rl ON (r.`id_qa` = rl.`id_qa`)
            LEFT JOIN `' . _DB_PREFIX_ . 'shop` s ON (r.`id_shop` = s.`id_shop`)
            WHERE `id_lang` = ' . (int) $id_lang . ' AND (';
        
        if ($shop_ids = Shop::getContextListShopID())
            foreach ($shop_ids as $id_shop)
                $sql .= ' r.`id_shop` = ' . (int) $id_shop . ' OR ';
        
        $sql .= ' r.`id_shop` = 0 )';
        
        $content = Db::getInstance()->executeS($sql);
        
        foreach ($content as $key => $value) {
            $content[$key]['question'] = substr(strip_tags($value['question']), 0, 200);
        }
        
        return $content;
    }
    
    protected function renderForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        
        $fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('New Question block')
            ),
            'input' => array(
                'id_qa' => array(
                    'type' => 'hidden',
                    'name' => 'id_qa'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Question:'),
                    'name' => 'question',
                    'autoload_rte' => true,
                    'required' => true,
                    'lang' => true,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Answer:'),
                    'name' => 'answer',
                    'autoload_rte' => true,
                    'required' => true,
                    'lang' => true,
                    'rows' => 5,
                    'cols' => 40,
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}'
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            ),
            'buttons' => array(
                array(
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'title' => $this->l('Back to list'),
                    'icon' => 'process-icon-back'
                )
            )
        );
        
        if (Shop::isFeatureActive() && Tools::getValue('id_qa') == false) {
            $fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso_theme'
            );
        }
        
        
        $helper                  = new HelperForm();
        $helper->module          = $this;
        $helper->name_controller = 'faq';
        $helper->identifier      = $this->identifier;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang)
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
        
        $helper->currentIndex             = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language    = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll           = true;
        $helper->title                    = $this->displayName;
        $helper->submit_action            = 'savefaq';
        
        $helper->fields_value = $this->getFormValues();
        
        return $helper->generateForm(array(
            array(
                'form' => $fields_form
            )
        ));
    }
    
    public function getFormValues()
    {
        $fields_value = array();
        $id_qa        = (int) Tools::getValue('id_qa');
        
        foreach (Language::getLanguages(false) as $lang) {
            if ($id_qa) {
                $info                                             = new FaqBlock((int) $id_qa);
                $fields_value['question'][(int) $lang['id_lang']] = $info->question[(int) $lang['id_lang']];
                $fields_value['answer'][(int) $lang['id_lang']]   = $info->answer[(int) $lang['id_lang']];
            } else {
                $fields_value['question'][(int) $lang['id_lang']] = Tools::getValue('question_' . (int) $lang['id_lang'], '');
                $fields_value['answer'][(int) $lang['id_lang']]   = Tools::getValue('answer_' . (int) $lang['id_lang'], '');
            }
        }
        
        
        $fields_value['id_qa'] = (int) Tools::getValue('id_qa');
        
        return $fields_value;
    }
    
    public function processSaveFaq()
    {
        if ($id_qa = Tools::getValue('id_qa'))
            $info = new FaqBlock((int) $id_qa);
        else {
            $info = new FaqBlock();
            if (Shop::isFeatureActive()) {
                $shop_ids = Tools::getValue('checkBoxShopAsso_configuration');
                if (!$shop_ids) {
                    $this->html .= '<div class="alert alert-danger conf error">' . $this->l('You have to select at least one shop.') . '</div>';
                    return false;
                }
            } else
                $info->id_shop = Shop::getContextShopID();
        }
        
        $languages = Language::getLanguages(false);
        
        $text  = array();
        $text1 = array();
        
        foreach ($languages AS $lang) {
            $text[$lang['id_lang']]  = str_replace("<p>", "", Tools::getValue('answer_' . $lang['id_lang']));
            $text1[$lang['id_lang']] = str_replace("<p>", "", Tools::getValue('question_' . $lang['id_lang']));
        }
        
        $info->question = $text1;
        $info->answer   = $text;
        
        if (Shop::isFeatureActive() && !$info->id_shop) {
            $saved = true;
            foreach ($shop_ids as $id_shop) {
                $info->id_shop = $id_shop;
                $saved &= $info->add();
            }
        } else
            $saved = $info->save();
        
        if ($saved)
            $this->_clearCache('faq.tpl');
        else
            $this->html .= '<div class="alert alert-danger conf error">' . $this->l('An error occurred while attempting to save.') . '</div>';
        
        return $saved;
        
    }
    
    public function deletefaq()
    {
        $id_qa = Tools::getValue('id_qa');
        Db::getInstance()->delete($this->name, 'id_qa = ' . $id_qa);
        Db::getInstance()->delete($this->name . '_lang', 'id_qa = ' . $id_qa);
        $this->_clearCache('faq.tpl');
    }
	
    public function gettitle()
    {
        return strtoupper($this->name);
    }	

    public function getlink()
    {
    	$link = Context::getContext()->link;
        return $link->getModuleLink($this->name,$this->name.'page');
    }   
}
