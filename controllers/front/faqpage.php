<?php

class faqfaqpageModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;

    
	public function initContent()
	{
		parent::initContent();
        $this->path = __PS_BASE_URI__.'modules/faq/';
        $this->context->controller->addCSS($this->path.'views/css/faq.css', 'all');
        $faq = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'faq_lang where id_lang='.$this->context->language->id);
        $this->context->smarty->assign('faqs', $faq);
		$this->setTemplate('faq.tpl');
	}
}