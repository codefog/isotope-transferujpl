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
 * Extension version
 */
@define('ISOTOPE_TRANSFERUJPL_VERSION', '2.0');
@define('ISOTOPE_TRANSFERUJPL_BUILD', '3');


/**
 * Payment modules
 */
\Isotope\Model\Payment::registerModelType('transferujpl', 'Isotope\Model\Payment\TransferujPl');


/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['initializePostsale'][] = array('Isotope\Model\Payment\TransferujPl', 'updatePaymentId');
