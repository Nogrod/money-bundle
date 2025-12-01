<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(\JK\MoneyBundle\Form\Type\MoneyType::class)
        ->private()
        ->args([''])
        ->tag('form.type');

    $services->set(\JK\MoneyBundle\Twig\MoneyExtension::class)
        ->private()
        ->args([''])
        ->tag('twig.extension');
};
