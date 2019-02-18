<?php

declare(strict_types=1);

namespace Setono\SyliusAddwishPlugin\EventListener;

use Setono\SyliusAddwishPlugin\Resolver\ItemCodeResolverInterface;
use Setono\TagBagBundle\Factory\TwigTagFactory;
use Setono\TagBagBundle\HttpFoundation\Session\Tag\TagBagInterface;
use Setono\TagBagBundle\Tag\TagInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;

final class CartUpdatedSubscriber extends TagSubscriber
{
    /**
     * @var TwigTagFactory
     */
    private $twigTagFactory;

    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @var ItemCodeResolverInterface
     */
    private $itemCodeResolver;

    /**
     * @param TagBagInterface $tagBag
     * @param TwigTagFactory $twigTagFactory
     * @param ItemCodeResolverInterface $itemCodeResolver
     */
    public function __construct(
        TagBagInterface $tagBag,
        TwigTagFactory $twigTagFactory,
        CartContextInterface $cartContext,
        ItemCodeResolverInterface $itemCodeResolver
    ) {
        parent::__construct($tagBag);

        $this->twigTagFactory = $twigTagFactory;
        $this->cartContext = $cartContext;
        $this->itemCodeResolver = $itemCodeResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order_item.post_add' => [
                'addScript',
            ],
            'sylius.order_item.post_remove' => [
                'addScript',
            ],
            'sylius.order.post_update' => [
                'addScript',
            ],
        ];
    }

    public function addScript(): void
    {
        $cart = $this->cartContext->getCart();
        if (!$cart instanceof OrderInterface) {
            return;
        }

        if (BaseOrderInterface::STATE_CART !== $cart->getState()) {
            return;
        }

        // If cart is empty - we have CartClearedSubscriber for this
        if ($cart->getItems()->count() <= 0) {
            return;
        }

        $tag = $this->twigTagFactory->create('@SetonoSyliusAddwishPlugin/Tag/cart_updated.js.twig', TagInterface::TYPE_SCRIPT, [
            'cart' => $cart,
        ]);

        $this->tagBag->add($tag, TagBagInterface::SECTION_BODY_BEGIN);
    }
}
