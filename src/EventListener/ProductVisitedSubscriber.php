<?php

declare(strict_types=1);

namespace Setono\SyliusAddwishPlugin\EventListener;

use Setono\SyliusAddwishPlugin\Tag\Tags;
use Setono\TagBagBundle\Tag\TagInterface;
use Setono\TagBagBundle\Tag\TwigTag;
use Setono\TagBagBundle\TagBag\TagBagInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Product\Model\ProductInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\FirewallMapInterface;

final class ProductVisitedSubscriber extends TagSubscriber
{
    /** @var RequestStack|null */
    private $requestStack;

    /** @var FirewallMapInterface|null */
    private $firewallMap;

    public function __construct(
        TagBagInterface $tagBag,
        RequestStack $requestStack = null, // todo should not be nullable in v2
        FirewallMapInterface $firewallMap = null // todo should not be nullable in v2
    ) {
        parent::__construct($tagBag);

        $this->requestStack = $requestStack;
        $this->firewallMap = $firewallMap;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.product.show' => [
                'addScript',
            ],
        ];
    }

    public function addScript(ResourceControllerEvent $event): void
    {
        if (null === $this->requestStack || null === $this->firewallMap) {
            return;
        }

        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $config = $this->getFirewallConfig($this->requestStack->getCurrentRequest());
        if (null === $config) {
            return;
        }

        // only track event when in shop
        if ($config->getContext() !== 'shop') {
            return;
        }

        $this->tagBag->add(new TwigTag(
            '@SetonoSyliusAddwishPlugin/Tag/product_visited.html.twig',
            TagInterface::TYPE_HTML,
            Tags::TAG_PRODUCT_VISITED,
            ['product' => $product]
        ), TagBagInterface::SECTION_BODY_END);
    }

    private function getFirewallConfig(?Request $request): ?FirewallConfig
    {
        if (!$this->firewallMap instanceof FirewallMap) {
            return null;
        }

        if (null === $request) {
            return null;
        }

        return $this->firewallMap->getFirewallConfig($request);
    }
}
