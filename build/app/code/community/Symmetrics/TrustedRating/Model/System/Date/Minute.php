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
 * @author    Eric Reiche <er@symmetrics.de>
 * @copyright 2010 Symmetrics Gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
 
/**
 * Symmetrics_TrustedRating_Model_System_Date
 *
 * @category  Symmetrics
 * @package   Symmetrics_TrustedRating
 * @author    symmetrics gmbh <info@symmetrics.de>
 * @author    Eric Reiche <er@symmetrics.de>
 * @copyright 2010 Symmetrics Gmbh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.symmetrics.de/
 */
class Symmetrics_TrustedRating_Model_System_Date_Minute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * return option array
     * 
     * @return array
     */
    public function getOptionArray()
    {
        return $this->getAllOptions();
    }
    
    /**
     * return option array
     * 
     * @return array
     */
    public function getAllOptions()
    {
        for ($option=0;$option<=59;$option++) {
            $options[$option] = $option;
        }
        return $options;
    }
    
    /**
     * return option array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}