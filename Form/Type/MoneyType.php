<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Form\Type;

use JK\MoneyBundle\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Locale;
use Money\Currencies;
use Money\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
class MoneyType extends AbstractType
{
    /** @var Currency * */
    protected Currency $currency;

    protected static array $patterns = [];

    /**
     * @param string $currencyCode ISO currency code
     */
    public function __construct(string $currencyCode)
    {
        $this->currency = new Currency($currencyCode);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addViewTransformer(
                new MoneyToLocalizedStringTransformer(
                    $options['currency'],
                    $options['scale'],
                    $options['grouping'],
                    $options['currencies']
                )
            )
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['money_pattern'] = self::getPattern($options['currency']->getCode());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['currency' => $this->currency, 'scale' => 2, 'grouping' => false, 'compound' => false, 'currencies' => null]);
        $resolver->setNormalizer('currency', function (OptionsResolver $resolver, $currency): Currency {
            if (!$currency instanceof Currency) {
                @trigger_error('Passing a currency as string is deprecated since 1.1 and will be removed in 2.0. Please pass a '.Currency::class.' instance instead.', \E_USER_DEPRECATED);
                $currency = new Currency($currency);
            }

            return $currency;
        });
        $resolver->setAllowedTypes('currency', ['string', Currency::class]);
        $resolver->setAllowedTypes('currencies', ['null', Currencies::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'money';
    }

    /**
     * Returns the pattern for this locale.
     *
     * The pattern contains the placeholder "{{ widget }}" where the HTML tag should
     * be inserted
     *
     * @copyright Fabien Potencier <fabien@symfony.com>
     *
     * @see \Symfony\Component\Form\Extension\Core\Type\MoneyType::getPattern
     */
    protected static function getPattern($currency)
    {
        if (!$currency) {
            return '{{ widget }}';
        }

        $locale = \Locale::getDefault();

        if (!isset(self::$patterns[$locale])) {
            self::$patterns[$locale] = [];
        }

        if (!isset(self::$patterns[$locale][$currency])) {
            $format = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
            $pattern = $format->formatCurrency(123, $currency);

            // the spacings between currency symbol and number are ignored, because
            // a single space leads to better readability in combination with input
            // fields

            // the regex also considers non-break spaces (0xC2 or 0xA0 in UTF-8)

            preg_match('/^([^\s\xc2\xa0]*)[\s\xc2\xa0]*123(?:[,.]0+)?[\s\xc2\xa0]*([^\s\xc2\xa0]*)$/u', $pattern, $matches);

            if (isset($matches[1]) && ('' !== $matches[1] && '0' !== $matches[1])) {
                self::$patterns[$locale][$currency] = $matches[1].' {{ widget }}';
            } elseif (isset($matches[2]) && ('' !== $matches[2] && '0' !== $matches[2])) {
                self::$patterns[$locale][$currency] = '{{ widget }} '.$matches[2];
            } else {
                self::$patterns[$locale][$currency] = '{{ widget }}';
            }
        }

        return self::$patterns[$locale][$currency];
    }
}
