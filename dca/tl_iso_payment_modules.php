<?php

/**
 * isotope_transferujpl extension for Contao Open Source CMS
 * 
 * Copyright (C) 2012 Codefog
 * 
 * @package isotope_transferujpl
 * @link    http://codefog.pl
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Add a palette to tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['transferujpl'] = '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},transferujpl_id,transferujpl_code;{price_legend:hide},price,tax_class;{enabled_legend},enabled';


/**
 * Add fields to tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['transferujpl_id'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['transferujpl_id'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'rgxp'=>'digit', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['transferujpl_code'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['transferujpl_code'],
	'inputType'		=> 'text',
	'eval'			=> array('maxlength'=>32, 'tl_class'=>'w50')
);
