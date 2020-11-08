<?php

namespace TheCocktail\Bundle\MegaMenuBundle\EventSubscriber;

use Sulu\Component\Webspace\Analyzer\Attributes\RequestAttributes;
use Sulu\Component\Webspace\Webspace;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TheCocktail\Bundle\MegaMenuBundle\Builder\MenuBuilder;
use Twig\Environment;

class MenuBuilderSubscriber implements EventSubscriberInterface
{
    private MenuBuilder $builder;
    private Environment $twig;
    private array $megamenus;

    public function __construct(
        MenuBuilder $builder,
        Environment $twig,
        array $megamenus
    ) {
        $this->builder = $builder;
        $this->twig = $twig;
        $this->megamenus = $megamenus;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest() || $event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        $request = $event->getRequest();

        if (!$attributes = $request->attributes->get('_sulu')) {
            return;
        }

        /** @var RequestAttributes $attributes */
        if (!$webspace = $attributes->getAttribute('webspace')) {
            return;
        }

        $data = [];
        foreach ($this->megamenus as $resourceKey => $menu) {
            if (true === $menu['twig_global']) {
                /** @var Webspace|null $webspace */
                $data[$resourceKey] = $this->builder->build($webspace->getKey(), $resourceKey, $request->getLocale());
            }
        }
        $this->twig->addGlobal('sulu_megamenu', $data);
    }
}
