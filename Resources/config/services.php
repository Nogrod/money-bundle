<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set(\JK\MoneyBundle\Form\Type\MoneyType::class)
        ->private()
        ->args([''])
        ->tag('form.type');

    $services->set(\JK\MoneyBundle\Twig\MoneyExtension::class)
        ->private()
        ->args([''])
        ->tag('twig.extension');
};
