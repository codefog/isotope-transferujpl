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

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;

/**
 * Class TransferujPl
 *
 * Provide a payment method "Transferuj.pl" for Isotope.
 */
class TransferujPl extends Postsale implements IsotopePayment
{

    /**
     * Update the payment ID (postsale workaround)
     */
    public function updatePaymentId()
    {
        if ($_POST['tr_status'] && $_POST['id'])
        {
            $_POST['transferujpl_id'] = $_POST['id'];
            unset($_POST['id']);
        }
    }


    /**
     * Process Transaction URL notification
     * @param IsotopeProductCollection
     */
    public function processPostSale(IsotopeProductCollection $objOrder)
    {
        if (\Input::post('tr_error') != 'none')
        {
            \System::log('Transferuj.pl response error: ' . \Input::post('tr_error'), __METHOD__, TL_ERROR);
            die('TRUE');
        }

        if (\Input::post('transferujpl_id') == $this->transferujpl_id && \Input::post('tr_status') == 'TRUE')
        {
            $strHash = md5($this->transferujpl_id . \Input::post('tr_id') . number_format(round($objOrder->getTotal(), 2), 2, '.', '') . $objOrder->id . $this->transferujpl_code);

            if (\Input::post('md5sum') == $strHash)
            {
                // Checkout failed
                if (!$objOrder->checkout())
                {
                    \System::log('Transferuj.pl checkout for order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
                    die('TRUE');
                }

                $arrPayment = deserialize($objOrder->payment_data, true);
                $arrPayment['POSTSALE'][] = $_POST;
                $objOrder->payment_data = $arrPayment;
                $objOrder->date_paid = time();
                $objOrder->updateOrderStatus($this->new_order_status);
                $objOrder->save();

                \System::log('Transferuj.pl data accepted for order ID "' . $objOrder->id . '"', __METHOD__, TL_GENERAL);
            }
        }

        die('TRUE');
    }


    /**
     * Get the postsale order
     * @return object
     */
    public function getPostsaleOrder()
    {
        return Order::findByPk(\Input::post('tr_crc'));
    }


    /**
     * HTML form for checkout
     * @param object
     * @param object
     * @return string
     */
    public function checkoutForm(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        $arrProducts = array();

        foreach ($objOrder->getItems() as $objItem)
        {
            // Set the active product for insert tags replacement
            Product::setActive($objItem->getProduct());

            $strOptions = '';
            $arrOptions = Isotope::formatOptions($objItem->getOptions());

            Product::unsetActive();

            if (is_array($arrOptions) && count($arrOptions))
            {
                $options = array();

                foreach ($arrOptions as $option)
                {
                    $options[] = $option['label'] . ': ' . $option['value'];
                }

                $strOptions = ' (' . implode(', ', $options) . ')';
            }

            $arrProducts[] = specialchars($objItem->getName() . $strOptions);
        }

        $strPrice = number_format(round($objOrder->getTotal(), 2), 2, '.', '');

        $objTemplate = new \Isotope\Template('iso_payment_transferujpl');
        $objTemplate->setData($this->arrData);

        $objTemplate->id = $this->id;
        $objTemplate->order_id = $objOrder->id;
        $objTemplate->amount = $strPrice;
        $objTemplate->products = implode(', ', $arrProducts);
        $objTemplate->hash = md5($this->transferujpl_id . $strPrice . $this->transferujpl_code);
        $objTemplate->postsaleUrl = \Environment::get('base') . 'system/modules/isotope/postsale.php?mod=pay&id=' . $this->id;
        $objTemplate->successUrl = \Environment::get('base') . $objModule->generateUrlForStep('complete', $objOrder);
        $objTemplate->errorUrl = \Environment::get('base') . $objModule->generateUrlForStep('failed');
        $objTemplate->language = $GLOBALS['TL_LANGUAGE'];
        $objTemplate->address = $objOrder->getBillingAddress();
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][0];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][1];
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_transferujpl'][2]);

        return $objTemplate->parse();
    }


    /**
     * Return information or advanced features in the backend
     * @param integer
     * @return string
     */
    public function backendInterface($orderId)
    {
        if (($objOrder = Order::findByPk($orderId)) === null)
        {
            return parent::backendInterface($orderId);
        }

        $arrPayment = deserialize($objOrder->payment_data, true);

        if (!is_array($arrPayment['POSTSALE']) || empty($arrPayment['POSTSALE']))
        {
            return parent::backendInterface($orderId);
        }

        $arrPayment = array_pop($arrPayment['POSTSALE']);
        ksort($arrPayment);
        $i = 0;

        $strBuffer = '
<div id="tl_buttons">
<a href="'.ampersand(str_replace('&key=payment', '', \Environment::get('request'))).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">' . $this->name . ' (' . $GLOBALS['TL_LANG']['MODEL']['tl_iso_payment.transferujpl'][0] . ')' . '</h2>

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
