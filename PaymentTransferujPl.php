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
 * Class PaymentTransferujPl
 * 
 * Provide a payment method "Transferuj.pl" for Isotope.
 */
class PaymentTransferujPl extends IsotopePayment
{

	/**
	 * Return a list of status options
	 * @return array
	 */
	public function statusOptions()
	{
		return array('pending', 'processing', 'complete', 'on_hold');
	}


	/**
	 * Process checkout payment
	 * @return mixed
	 */
	public function processPayment()
	{
		return true;
	}


	/**
	 * Process Transaction URL notification
	 */
	public function processPostSale()
	{
		if ($this->Input->post('tr_error') != 'none')
		{
			$this->log('Transferuj.pl response error: ' . $this->Input->post('tr_error'), 'PaymentTransferujPl processPostSale()', TL_ERROR);
			return;
		}

		if ($this->Input->post('transferujpl_id') == $this->transferujpl_id && $this->Input->post('tr_status') == 'TRUE')
		{
			$objOrder = new IsotopeOrder();

			// Order was not found
			if (!$objOrder->findBy('id', $this->Input->post('tr_crc')))
			{
				$this->log('Order ID "' . $objOrder->id . '" not found', 'PaymentTransferujPl processPostSale()', TL_ERROR);
				return;
			}

			// Checkout failed
			if (!$objOrder->checkout())
			{
				$this->log('Transferuj.pl checkout for order ID "' . $objOrder->id . '" failed', 'PaymentTransferujPl processPostSale()', TL_ERROR);
				return;
			}

			$strHash = md5($this->transferujpl_id . $this->Input->post('tr_id') . $objOrder->grandTotal . $objOrder->id . $this->transferujpl_code);

			if ($this->Input->post('md5sum') == $strHash)
			{
				// Store the payment data
				$arrPayment = deserialize($objOrder->payment_data, true);
				$arrPayment['POSTSALE'][] = $_POST;
				$objOrder->payment_data = $arrPayment;

				$objOrder->date_paid = $time;
				$objOrder->save();

				$this->log('Transferuj.pl data accepted for order ID "' . $objOrder->id . '"', 'PaymentTransferujPl processPostSale()', TL_GENERAL);
			}
		}

		die('TRUE');
	}


	/**
	 * HTML form for checkout
	 * @return string
	 */
	public function checkoutForm()
	{
		$arrProducts = array();
		$objOrder = new IsotopeOrder();
		$objOrder->findBy('cart_id', $this->Isotope->Cart->id);

		foreach ($this->Isotope->Cart->getProducts() as $objProduct)
		{
			$strOptions = '';
			$arrOptions = $objProduct->getOptions();

			if (is_array($arrOptions) && count($arrOptions))
			{
				$options = array();

				foreach ($arrOptions as $option)
				{
					$options[] = $option['label'] . ': ' . $option['value'];
				}

				$strOptions = ' (' . implode(', ', $options) . ')';
			}

			$arrProducts[] = specialchars($objProduct->name . $strOptions);
		}

		list($endTag, $startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();
		$intPrice = number_format($this->Isotope->Cart->grandTotal, 2, '.', '');
		$strHash = md5($this->transferujpl_id . $intPrice . $this->transferujpl_code);

		$strBuffer .= '
<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][0] . '</h2>
<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][1] . '</p>
<form id="payment_form" action="https://secure.transferuj.pl" method="post">
<input type="hidden" name="id" value="' . $this->transferujpl_id . '"' . $endTag . '
<input type="hidden" name="kwota" value="' . $intPrice . '"' . $endTag . '
<input type="hidden" name="crc" value="' . $objOrder->id . '"' . $endTag . '
<input type="hidden" name="opis" value="' . implode(', ', $arrProducts) . '"' . $endTag . '
<input type="hidden" name="md5" value="' . $strHash . '"' . $endTag . '
<input type="hidden" name="wyn_url" value="' . $this->Environment->base . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id . '"' . $endTag . '
<input type="hidden" name="pow_url" value="' . $this->Environment->base . IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->addToUrl('step=complete')) . '"' . $endTag . '
<input type="hidden" name="pow_url_blad" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '"' . $endTag . '
<input type="hidden" name="email" value="' . $this->Isotope->Cart->billingAddress['email'] . '"' . $endTag . '
<input type="hidden" name="nazwisko" value="' . $this->Isotope->Cart->billingAddress['lastname'] . '"' . $endTag . '
<input type="hidden" name="imie" value="' . $this->Isotope->Cart->billingAddress['firstname'] . '"' . $endTag . '
<input type="hidden" name="adres" value="' . $this->Isotope->Cart->billingAddress['street_1'] . '"' . $endTag . '
<input type="hidden" name="miasto" value="' . $this->Isotope->Cart->billingAddress['city'] . '"' . $endTag . '
<input type="hidden" name="kod" value="' . $this->Isotope->Cart->billingAddress['postal'] . '"' . $endTag . '
<input type="hidden" name="kraj" value="' . $this->Isotope->Cart->billingAddress['country'] . '"' . $endTag . '
<input type="hidden" name="telefon" value="' . $this->Isotope->Cart->billingAddress['phone'] . '"' . $endTag . '
<input type="hidden" name="jezyk" value="' . $GLOBALS['TL_LANGUAGE'] . '"' . $endTag . '
<input type="submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][2]) . '"' . $endTag . '
</form>

' . $startScript . '
window.addEvent( \'domready\' , function() {
  $(\'payment_form\').submit();
});
' . $endScript;

		return $strBuffer;
	}


	/**
	 * Return a list of valid credit card types for this payment module
	 * @return array
	 */
	public function getAllowedCCTypes()
	{
		return array();
	}
}
