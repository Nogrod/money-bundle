<?php

declare(strict_types=1);

namespace JK\MoneyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Intl\Languages;

/**
 * This class contains the configuration information for the bundle.
 *
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
class Configuration implements ConfigurationInterface
{
    /** @var string * */
    private string $currencyCode;

    /**
     * @param string $locale Locale for currency code
     */
    public function __construct(string $locale)
    {
        $locales = class_exists(\ResourceBundle::class)
            ? \ResourceBundle::getLocales('')
            : Languages::getLanguageCodes();

        if (false === \in_array($locale, $locales, true)) {
            throw new InvalidConfigurationException(sprintf("Locale '%s' is not valid.", $locale));
        }

        if (2 === \mb_strlen($locale)) {
            // Default US dollars
            $locale .= '_US';
        }

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $this->currencyCode = $formatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('jk_money');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('jk_money');
        }

        $rootNode
            ->children()
                ->scalarNode('currency')->defaultValue($this->currencyCode)->end()
            ->end();

        return $treeBuilder;
    }
}
