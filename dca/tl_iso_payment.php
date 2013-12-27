<?php

/**
 * isotope_transferujpl extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog
 *
 * @package isotope_transferujpl
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Add a palette to tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['palettes']['transferujpl'] = '{type_legend},name,label,type;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},transferujpl_id,transferujpl_code;{price_legend:hide},price,tax_class;{enabled_legend},enabled';


/**
 * Add fields to tl_iso_payment
 */
$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['transferujpl_id'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment']['transferujpl_id'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50'),
	'sql'           => "varchar(10) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_iso_payment']['fields']['transferujpl_code'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment']['transferujpl_code'],
	'inputType'		=> 'text',
	'eval'			=> array('maxlength'=>32, 'tl_class'=>'w50'),
	'sql'           => "varchar(32) NOT NULL default ''"
);
