<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the MultiSafepay plugin
 * to newer versions in the future. If you wish to customize the plugin for your
 * needs please document your changes and make backups before your update.
 *
 * @category    MultiSafepay
 * @package     Connect
 * @author      Ruud Jonk <techsupport@multisafepay.com>
 * @copyright   Copyright (c) 2015 MultiSafepay, Inc. (http://www.multisafepay.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN 
 * ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace MultiSafepay\Connect\Model\Observers;

use Magento\Framework\Event\ObserverInterface;

class Shipment implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $paymentMethod = $this->_objectManager->create('MultiSafepay\Connect\Model\Connect');
        $event = $observer->getEvent();
        $shipment = $event->getShipment();
        $order = $shipment->getOrder();
        $payment = $order->getPayment()->getMethodInstance();

        if (!in_array($payment->getCode(), $this->_objectManager->create('MultiSafepay\Connect\Helper\Data')->gateways)) {
            return $this;
        }

        $shipped = $paymentMethod->shipOrder($order);
        if ($shipped['success']) {
            $this->_messageManager->addSuccess(__('Your shipment has been processed. Your transaction has also been updated at MultiSafepay'));
        } elseif ($shipped['error']) {
            $this->_messageManager->addError(__('Your shipment has been processed, but the transaction could not be updated at MultiSafepay. If needed you need to update your transaction manually using MultiSafepay Control'));
        }
        return $this;
    }

}
