<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Tests\DependencyInjection;

use JK\MoneyBundle\DependencyInjection\JKMoneyExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

final class JKMoneyExtensionTest extends TestCase
{
    public function testInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $container = new ContainerBuilder();
        $container->setParameter('kernel.default_locale', 'xx');

        $loader = new JKMoneyExtension();
        $config = [];
        $loader->load([$config], $container);
    }

    public function testLoadFormConfiguration(): void
    {
        if (false === interface_exists(\Twig\Extension\ExtensionInterface::class)) {
            $this->markTestSkipped('Package `twig/twig` is not available.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.default_locale', 'cs');

        $loader = new JKMoneyExtension();
        $config = [];
        $loader->load([$config], $container);
        $this->assertTrue($container->hasDefinition(\JK\MoneyBundle\Form\Type\MoneyType::class));
    }

    public function testLoadTwigConfiguration(): void
    {
        if (false === interface_exists(\Symfony\Component\Form\FormInterface::class)) {
            $this->markTestSkipped('Package `symfony/form` is not available.');
        }

        $container = new ContainerBuilder();
        $container->setParameter('kernel.default_locale', 'cs');

        $loader = new JKMoneyExtension();
        $config = [];
        $loader->load([$config], $container);
        $this->assertTrue($container->hasDefinition(\JK\MoneyBundle\Twig\MoneyExtension::class));
    }

    public function testParameterNotFoundException(): void
    {
        $this->expectException(ParameterNotFoundException::class);
        $container = new ContainerBuilder();
        $loader = new JKMoneyExtension();
        $config = [];
        $loader->load([$config], $container);
    }
}
