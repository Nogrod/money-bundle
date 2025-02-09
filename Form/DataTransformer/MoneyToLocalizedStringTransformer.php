<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Form\DataTransformer;

use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\ParserException;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

/**
 * Transforms between a normalized format and a localized money string.
 *
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
class MoneyToLocalizedStringTransformer implements DataTransformerInterface
{
    private readonly Currencies $currencies;

    private readonly DecimalMoneyFormatter $decimalMoneyFormatter;

    private readonly DecimalMoneyParser $decimalMoneyParser;

    private readonly NumberToLocalizedStringTransformer $numberToLocalizedStringTransformer;

    public function __construct(private readonly Currency $currency, int $scale = null, ?bool $grouping = false, Currencies $currencies = null)
    {
        $this->currencies = $currencies ?: new ISOCurrencies();
        $this->numberToLocalizedStringTransformer = new NumberToLocalizedStringTransformer($scale, $grouping);
        $this->decimalMoneyFormatter = new DecimalMoneyFormatter($this->currencies);
        $this->decimalMoneyParser = new DecimalMoneyParser($this->currencies);
    }

    /**
     * Transforms a localized money string into a normalized format.
     *
     * @param string $value Localized money string
     *
     * @throws TransformationFailedException if the given value is not a string
     *                                       or if the value can not be transformed
     *
     * @return Money Money object
     */
    public function reverseTransform(mixed $value): Money
    {
        $value = $this->numberToLocalizedStringTransformer->reverseTransform($value);
        try {
            return $this->decimalMoneyParser->parse(sprintf('%.53f', $value), $this->currency);
        } catch (ParserException $parserException) {
            throw new TransformationFailedException($parserException->getMessage());
        }
    }

    /**
     * Transforms a normalized format into a localized money string.
     *
     * @param Money $value Money object
     *
     * @throws TransformationFailedException if the given value is not numeric or
     *                                       if the value can not be transformed
     *
     * @return string Localized money string
     */
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        return $this->numberToLocalizedStringTransformer->transform($this->decimalMoneyFormatter->format($value));
    }
}
