<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category  Symmetrics
 * @package   Symmetrics_TrustedRating
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Siegfried Schmitz <ss@symmetrics.de>
 * @author    Yauhen Yakimovich <yy@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */

/**
 * Main model
 *
 * @category  Symmetrics
 * @package   Symmetrics_TrustedRating
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Siegfried Schmitz <ss@symmetrics.de>
 * @author    Yauhen Yakimovich <yy@symmetrics.de>
 * @copyright 2010 symmetrics gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_TrustedRating_Model_Trustedrating extends Mage_Core_Model_Abstract
{
    /**
     * Fixed part of the link for the rating-site for the widget
     *
     * @var string
     */
    const WIDGET_LINK = 'https://www.trustedshops.com/bewertung/widget/widgets/';

    /**
     * Fixed part of the link for the rating-site for the email - widget
     *
     * @var string
     */
    const EMAIL_WIDGET_LINK = 'https://www.trustedshops.com/bewertung/widget/img/';

    /**
     * Fixed part of the registration link
     *
     * @var string
     */
    const REGISTRATION_LINK = 'https://www.trustedshops.com/bewertung/anmeldung.html?';

    /**
     * Fixed part of the widget path
     *
     * @var string
     */
    const IMAGE_LOCAL_PATH = 'media/';

    /**
     * The cacheid to cache the widget
     *
     * @var string
     */
    const CACHEID = 'trustedratingimage';

    /**
     * The cacheid to cache the email widget
     *
     * @var string
     */
    const EMAIL_CACHEID = 'trustedratingemailimage';

    /**
     * Get the trusted rating id from store config
     *
     * @return string
     */
    public function getTsId()
    {
        return Mage::helper('trustedrating')->getTsId();
    }

    /**
     * Get the module status from store config
     *
     * @return string
     */
    public function getIsActive()
    {
        return Mage::helper('trustedrating')->getIsActive();
    }

    /**
     * Get the selected language (for the rating - site) from the store config and returns
     * the link for the widget, which is stored in the module config for each language
     *
     * @param string $type    type
     * @param int    $storeId store id
     *
     * @return string
     */
    public function getRatingLinkData($type, $storeId = null)
    {
        $optionValue = Mage::getStoreConfig('trustedrating/data/trustedrating_ratinglanguage', $storeId);
        $link = Mage::helper('trustedrating')->getConfig($type, $optionValue);

        return $link;
    }

    /**
     * Check if the current language is chosen in the trusted rating config
     *
     * @return boolean
     */
    public function checkLocaleData()
    {
        $storeId = Mage::app()->getStore()->getId();
        $countryCode = substr(Mage::getStoreConfig('general/locale/code', $storeId), 0, 2);

        if (Mage::getStoreConfig('trustedrating/data/trustedrating_ratinglanguage') == $countryCode) {
            return true;
        }

        return false;
    }

    /**
     * Get the rating link
     *
     * @return string
     */
    public function getRatingLink()
    {
        return $this->getRatingLinkData('overviewlanguagelink');
    }

    /**
     * Get the email rating link
     *
     * @return string
     */
    public function getEmailRatingLink()
    {
        return $this->getRatingLinkData('ratinglanguagelink');
    }

    /**
     * Get the link data for the widget-image from cache
     *
     * @return array
     */
    public function getRatingWidgetData()
    {
        $tsId = $this->getTsId();

        if (!Mage::app()->loadCache(self::CACHEID)) {
            $this->cacheImage($tsId);
        }

        return array(
            'tsId' => $tsId,
            'ratingLink' => $this->getRatingLink(),
            'imageLocalPath' => self::IMAGE_LOCAL_PATH
        );
    }

    /**
     * Get the link data for the email-widget-image from cache
     *
     * @return array
     */
    public function getEmailWidgetData()
    {
        $tsId = $this->getTsId();
        $orderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getRealOrderId();
        $buyerEmail = $order->getData('customer_email');

        if (!Mage::app()->loadCache(self::EMAIL_CACHEID)) {
            $this->cacheEmailImage();
        }

        $array = array(
            'tsId' => $tsId,
            'ratingLink' => $this->getEmailRatingLink(),
            'imageLocalPath' => self::IMAGE_LOCAL_PATH,
            'orderId' => $incrementId,
            'buyerEmail' => $buyerEmail,
            'widgetName' => $this->getRatingLinkData('emailratingimage')
        );

        return $array;
    }

    /**
     * Cache the widget images
     *
     * @param string $type type
     * @param string $tsId Trusted Rating Id
     *
     * @return void
     */
    private function _cacheImageData($type, $tsId = null)
    {
        $ioObject = new Varien_Io_File();
        $ioObject->open();

        if ($type == 'emailWidget') {
            $emailWidgetName = $this->getRatingLinkData('emailratingimage');
            $readPath = self::EMAIL_WIDGET_LINK . $emailWidgetName;
            $writePath = self::IMAGE_LOCAL_PATH . $emailWidgetName;
            $cacheId = self::EMAIL_CACHEID;
        } else {
            $readPath = self::WIDGET_LINK . $tsId . '.gif';
            $writePath = self::IMAGE_LOCAL_PATH . $tsId . '.gif';
            $cacheId = self::CACHEID;
        }

        $result = $ioObject->read($readPath);
        $ioObject->write($writePath, $result);
        Mage::app()->saveCache($writePath, $cacheId, array(), 1);
        $ioObject->close();
    }

    /**
     * Cache the email image
     *
     * @return void
     */
    public function cacheEmailImage()
    {
        $this->_cacheImageData('emailWidget');
    }

    /**
     * Cache the widget image
     *
     * @param int $tsId Trusted Rating Id
     *
     * @return void
     */
    public function cacheImage($tsId)
    {
        $this->_cacheImageData('mainWidget', $tsId);
    }

    /**
     * Return registration Link
     *
     * @return string
     */
    public function getRegistrationLink()
    {
        $link = self::REGISTRATION_LINK;
        $link .= 'partnerPackage=' . Mage::helper('trustedrating')->getConfig('soapapi', 'partnerpackage');

        /* if symmetrics_imprint is installed, get data from there */
        if ($data = Mage::getStoreConfig('general/imprint')) {
            $params = array(
                'company' => $data['company_first'],
                'website' => $data['web'],
                'street' => $data['street'],
                'zip' => $data['zip'],
                'city' => $data['city'],
                'buyerEmail' => $data['email'],
            );

            foreach ($params as $key => $param) {
                if ($param) {
                    $link .= '&' . $key . '=' . urlencode($param);
                }
            }
        }

        return $link;
    }
    
    /**
     * Get all shippings which are older than x days and are not in table
     *
     * @return boolean|array
     */
    public function checkShippings()
    {
        if (!$dayInterval = $this->getDayInterval()) {
            return false;
        }

        $dateFrom = $dayInterval['from'];
        $dateTo = $dayInterval['to'];

        $shipments = Mage::getResourceModel('sales/order_shipment_collection');
        if ($sentIds = $this->_getSentIds()) {
            if (!is_null($sentIds)) {
                $shipments->addAttributeToFilter('entity_id', array('nin' => $sentIds));
            }
        }
        $shipments->addAttributeToFilter('created_at', array('from' => $dateFrom, 'to' => $dateTo))
            ->load();

        if (!$shipments) {
            return false;
        }
        return $shipments->getAllIds();
    }
    
    /**
     * Get all IDs from trusted_rating table of customers which already got an email
     *
     * @return array
     */
     private function _getSentIds()
     {
         $mailModel = Mage::getModel('trustedrating/mail');
         $shipmentIds = array();
         $model = $mailModel->getCollection();
         $items = $model->getItems();
         foreach ($items as $item) {
             $shipmentIds[] = $item->getShippmentId();
         }

         return $shipmentIds;
     }
    
    /**
     * Substract the days in the config (3 for default) from the current date for upper limit
     * and get the "include since" date (default: setup date) for lower limit; return both in array
     *
     * @return array
     */
    public function getDayInterval()
    {
        $from = Mage::helper('trustedrating')->getActiveSince();
        $fromString = $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $dayInterval = (float) Mage::getStoreConfig('trustedrating/trustedrating_email/days');
        if (is_null($dayInterval) || $dayInterval < 0) {
            return false;
        }
        
        $intervalSeconds = $dayInterval * 24 * 60 * 60;
        $date = new Zend_Date();
        $timestamp = $date->get();
        
        $diff = $timestamp - $intervalSeconds;

        return array(
            'from' => $fromString,
            'to' => date("Y-m-d H:i:s", $diff)
        );
    }
}
