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
@define('ISOTOPE_TRANSFERUJPL_VERSION', '1.1');
@define('ISOTOPE_TRANSFERUJPL_BUILD', '2');


/**
 * Payment modules
 */
\Isotope\Model\Payment::registerModelType('transferujpl', 'Isotope\Model\Payment\TransferujPl');


/**
 * Hooks
 */
$GLOBALS['ISO_HOOKS']['initializePostsale'][] = array('Isotope\Model\Payment\TransferujPl', 'updatePaymentId');
