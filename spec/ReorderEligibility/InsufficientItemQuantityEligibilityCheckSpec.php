<?php

declare(strict_types=1);

namespace spec\Sylius\CustomerReorderPlugin\ReorderEligibility;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\CustomerReorderPlugin\ReorderEligibility\InsufficientItemQuantityEligibilityCheck;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ReorderEligibilityChecker;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ReorderEligibilityConstraintMessageFormatterInterface;

final class InsufficientItemQuantityEligibilityCheckSpec extends ObjectBehavior
{
    function let(ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter)
    {
        $this->beConstructedWith($reorderEligibilityConstraintMessageFormatter);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(InsufficientItemQuantityEligibilityCheck::class);
    }

    function it_implements_reorder_eligibility_checker()
    {
        $this->shouldImplement(ReorderEligibilityChecker::class);
    }

    function it_returns_empty_array_when_prices_are_the_same(
        OrderInterface $order,
        OrderInterface $reorder,
        OrderItemInterface $firstOrderItem,
        OrderItemInterface $secondOrderItem
    ) {
        $order->getItems()->willReturn(new ArrayCollection([
            $firstOrderItem->getWrappedObject(),
            $secondOrderItem->getWrappedObject()
        ]));

        $reorder->getItems()->willReturn(new ArrayCollection([
            $firstOrderItem->getWrappedObject(),
            $secondOrderItem->getWrappedObject()
        ]));

        $firstOrderItem->getVariantName()->willReturn('test_variant_name_01');
        $firstOrderItem->getQuantity()->willReturn(100);

        $secondOrderItem->getVariantName()->willReturn('test_variant_name_02');
        $secondOrderItem->getQuantity()->willReturn(100);

        $this->check($order, $reorder)->shouldReturn([]);
    }

    function it_returns_empty_array_when_reorder_has_no_items(
        OrderInterface $order,
        OrderInterface $reorder,
        OrderItemInterface $firstOrderItem,
        OrderItemInterface $secondOrderItem
    ) {
        $order->getItems()->willReturn(new ArrayCollection([
            $firstOrderItem->getWrappedObject(),
            $secondOrderItem->getWrappedObject()
        ]));

        $reorder->getItems()->willReturn(new ArrayCollection([]));

        $firstOrderItem->getVariantName()->willReturn('test_variant_name_01');
        $firstOrderItem->getQuantity()->willReturn(10);

        $secondOrderItem->getVariantName()->willReturn('test_variant_name_02');
        $secondOrderItem->getQuantity()->willReturn(10);

        $this->check($order, $reorder)->shouldReturn([]);
    }

    function it_returns_flash_message_when_reorder_items_quantity_differ(
        OrderInterface $order,
        OrderInterface $reorder,
        OrderItemInterface $firstOrderItem,
        OrderItemInterface $secondOrderItem,
        ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter
    ) {
        $order->getItems()->willReturn(new ArrayCollection([
            $firstOrderItem->getWrappedObject(),
            $secondOrderItem->getWrappedObject()
        ]));

        $reorder->getItems()->willReturn(new ArrayCollection([
            $firstOrderItem->getWrappedObject(),
            $secondOrderItem->getWrappedObject()
        ]));

        $firstOrderItem->getVariantName()->willReturn('test_variant_name_01');
        $firstOrderItem->getQuantity()->willReturn(10, 5);

        $secondOrderItem->getVariantName()->willReturn('test_variant_name_02');
        $secondOrderItem->getQuantity()->willReturn(10, 5);

        $reorderEligibilityConstraintMessageFormatter->format(['test_variant_name_01', 'test_variant_name_02'])
            ->willReturn('test_variant_name_01, test_variant_name_02');

        $this->check($order, $reorder)->shouldReturn([
            'type' => 'info',
            'message' => 'sylius.reorder.insufficient_quantity',
            'parameters' => [
                '%order_items%' => 'test_variant_name_01, test_variant_name_02'
            ]
        ]);
    }

}