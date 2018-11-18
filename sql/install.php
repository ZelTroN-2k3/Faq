<?php
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'faq` (
  `id_qa` int(11) NOT NULL auto_increment,
  `id_shop` int(11) NOT NULL,
	PRIMARY KEY (`id_qa`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'faq_lang` (
  `id_qa` int(11) NOT NULL auto_increment,
  `id_lang` int(11) NOT NULL ,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP ,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id_qa`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'linksmenutop` (id_shop, new_window) VALUES(
  '.(int)Context::getContext()->shop->id.',0)';
?>