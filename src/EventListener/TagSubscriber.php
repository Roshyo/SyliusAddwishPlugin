<?php

declare(strict_types=1);

namespace Setono\SyliusAddwishPlugin\EventListener;

use Setono\TagBagBundle\HttpFoundation\Session\Tag\TagBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class TagSubscriber implements EventSubscriberInterface
{
    /**
     * @var TagBagInterface
     */
    protected $tagBag;

    public function __construct(TagBagInterface $tagBag)
    {
        $this->tagBag = $tagBag;
    }
}
