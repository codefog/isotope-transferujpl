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
 * Extension version
 */
@define('ISOTOPE_TRANSFERUJPL_VERSION', '1.0');
@define('ISOTOPE_TRANSFERUJPL_BUILD', '0');


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['transferujpl'] = 'PaymentTransferujPl';


/**
 * Hack the postsale process
 */
if ($_POST['tr_status'] && $_POST['id'])
{
	$_POST['transferujpl_id'] = $_POST['id'];
	unset($_POST['id']);
}
