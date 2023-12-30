<?php declare(strict_types=1);

namespace JK\MoneyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This class that loads and manages bundle configuration.
 *
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
class JKMoneyExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $locale = $container->getParameter('kernel.default_locale');
        $configuration = new Configuration($locale);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (interface_exists(\Symfony\Component\Form\FormInterface::class)) {
            $formType = $container->getDefinition(\JK\MoneyBundle\Form\Type\MoneyType::class);
            $formType->replaceArgument(0, $config['currency']);
        }

        if (interface_exists(\Twig\Extension\ExtensionInterface::class)) {
            $twigExtension = $container->getDefinition(\JK\MoneyBundle\Twig\MoneyExtension::class);
            $twigExtension->replaceArgument(0, $locale);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'JKMoneyBundle' => [
                        'type' => 'xml',
                        'prefix' => 'Money'
                    ]
                ]
            ]
        ]);
    }
}
