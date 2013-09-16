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
		$objOrder = new IsotopeOrder();

		// Return false if order was not found
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			return false;
		}

		// Return true if payment was successful
		if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
		{
			IsotopeFrontend::clearTimeout();
			return true;
		}

		if (IsotopeFrontend::setTimeout())
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'processing';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];
			return $objTemplate->parse();
		}

		$this->log('Payment could not be processed.', 'PaymentTransferujPl processPostSale()', TL_ERROR);
		$this->redirect($this->addToUrl('step=failed', true));
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

			$strHash = md5($this->transferujpl_id . $this->Input->post('tr_id') . $objOrder->grandTotal . $objOrder->id . $this->transferujpl_code);

			if ($this->Input->post('md5sum') == $strHash)
			{
				// Checkout failed
				if (!$objOrder->checkout())
				{
					$this->log('Transferuj.pl checkout for order ID "' . $objOrder->id . '" failed', 'PaymentTransferujPl processPostSale()', TL_ERROR);
					return;
				}

				$arrPayment = deserialize($objOrder->payment_data, true);
				$arrPayment['POSTSALE'][] = $_POST;
				$objOrder->payment_data = $arrPayment;

				$objOrder->date_paid = time();
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
		IsotopeFrontend::clearTimeout();
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
		$objAddress = (ISO_VERSION < 1.4) ? (object) $this->Isotope->Cart->billingAddress : $this->Isotope->Cart->billingAddress;

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
<input type="hidden" name="email" value="' . $objAddress->email . '"' . $endTag . '
<input type="hidden" name="nazwisko" value="' . $objAddress->lastname . '"' . $endTag . '
<input type="hidden" name="imie" value="' . $objAddress->firstname . '"' . $endTag . '
<input type="hidden" name="adres" value="' . $objAddress->street_1 . '"' . $endTag . '
<input type="hidden" name="miasto" value="' . $objAddress->city . '"' . $endTag . '
<input type="hidden" name="kod" value="' . $objAddress->postal . '"' . $endTag . '
<input type="hidden" name="kraj" value="' . $objAddress->country . '"' . $endTag . '
<input type="hidden" name="telefon" value="' . $objAddress->phone . '"' . $endTag . '
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
	 * Return information or advanced features in the backend
	 * @param integer
	 * @return string
	 */
	public function backendInterface($orderId)
	{
		$objOrder = new IsotopeOrder();

		if (!$objOrder->findBy('id', $orderId))
		{
			return parent::backendInterface($orderId);
		}

		$arrPayment = $objOrder->payment_data;

		if (!is_array($arrPayment['POSTSALE']) || empty($arrPayment['POSTSALE']))
		{
			return parent::backendInterface($orderId);
		}

		$arrPayment = array_pop($arrPayment['POSTSALE']);
		ksort($arrPayment);
		$i = 0;

		$strBuffer = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=payment', '', $this->Environment->request)).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['ISO_LANG']['PAY'][$this->type][0] . ')' . '</h2>

<table class="tl_show">
<tbody>';

		foreach ($arrPayment as $k => $v)
		{
			if (is_array($v))
			{
				continue;
			}

			$strBuffer .= '
  <tr>
    <td' . ($i%2 ? '' : ' class="tl_bg"') . '><span class="tl_label">' . $k . ': </span></td>
    <td' . ($i%2 ? '' : ' class="tl_bg"') . '>' . $v . '</td>
  </tr>';

			++$i;
        }

        $strBuffer .= '
</tbody></table>
</div>';

		return $strBuffer;
	}
}
