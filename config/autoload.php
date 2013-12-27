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
 * Register PSR-0 namespace
 */
NamespaceClassLoader::add('Isotope', 'system/modules/isotope_transferujpl/library');


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'iso_payment_transferujpl' => 'system/modules/isotope_transferujpl/templates/payment'
));
