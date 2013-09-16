<?php

/**
 * isotope_transferujpl extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog Ltd
 *
 * @package isotope_transferujpl
 * @author  Codefog Ltd <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Extension version
 */
@define('ISOTOPE_TRANSFERUJPL_VERSION', '1.1');
@define('ISOTOPE_TRANSFERUJPL_BUILD', '1');


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
