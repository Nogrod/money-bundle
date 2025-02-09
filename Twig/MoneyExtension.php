<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Twig;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * This class contains the configuration information for the bundle.
 *
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
class MoneyExtension extends AbstractExtension
{
    final public const FORMAT_CURRENCY = true;

    final public const FORMAT_DECIMAL = false;

    final public const GROUPING_NONE = false;

    final public const GROUPING_USED = true;

    public function __construct(private readonly string $locale)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('money', $this->moneyFilter(...)),
        ];
    }

    /**
     * @param true $groupingUsed
     * @param true $format
     */
    public function moneyFilter(Money $money, int $scale = 2, bool $groupingUsed = self::GROUPING_USED, bool $format = self::FORMAT_CURRENCY): array|string
    {
        $noFormatter = new \NumberFormatter($this->locale, self::FORMAT_CURRENCY === $format ? \NumberFormatter::CURRENCY : \NumberFormatter::DECIMAL);
        $noFormatter->setAttribute(\NumberFormatter::GROUPING_USED, (int) $groupingUsed);
        $noFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $scale);

        $intlFormatter = new IntlMoneyFormatter($noFormatter, new ISOCurrencies());

        // replace non-break spaces with ascii spaces
        return str_replace("\xc2\xa0", "\x20", $intlFormatter->format($money));
    }
}
