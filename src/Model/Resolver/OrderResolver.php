<?php
/**
 * Magento GraphQl Helper
 *
 * See LICENSE for license details.
 *
 * @license MIT
 * @link https://github.com/dongpd1994/magento-graphql-helper
 */

declare(strict_types=1);

namespace Nri\MagentoGraphqlHelper\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use ScandiPWA\QuoteGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Sales\Model\OrderRepository;

/**
 * Orders data resolver
 */
class OrderResolver implements ResolverInterface
{
  /**
   * @var CollectionFactoryInterface
   */
   protected $collectionFactory;
   
  /**
   * @var CheckCustomerAccount
   */
  protected $checkCustomerAccount;

  /**
   * @var OrderRepository
   */
  protected $orderRepository;

  /**
   * @param CollectionFactoryInterface $collectionFactory
   * @param CheckCustomerAccount $checkCustomerAccount
   * @param OrderRepository $orderRepository
   */
  public function __construct(
    CollectionFactoryInterface $collectionFactory,
    CheckCustomerAccount $checkCustomerAccount,
    OrderRepository $orderRepository
  ) {
	$this->collectionFactory = $collectionFactory;
    $this->checkCustomerAccount = $checkCustomerAccount;
    $this->orderRepository = $orderRepository;
  }

  /**
   * @inheritdoc
   */
  public function resolve(
    Field $field,
    $context,
    ResolveInfo $info,
    array $value = null,
    array $args = null
  ) {

    $customerId = $context->getUserId();
    $this->checkCustomerAccount->execute($customerId, $context->getUserType());

    $orderId = $args['id'];
    $order = $this->orderRepository->get($orderId);

    if ($customerId != $order->getCustomerId()) {
      throw new GraphQlNoSuchEntityException(__('Customer ID is invalid.'));
    }

    $base_info = [
      'tax_amount' => $order->getTaxAmount(),
      'discount_amount' => $order->getDiscountAmount()
    ];

    return [
      'more_order_info' => $base_info
    ];
  }
}
