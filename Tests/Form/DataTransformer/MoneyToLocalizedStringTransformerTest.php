<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JK\MoneyBundle\Tests\Form\DataTransformer;

use JK\MoneyBundle\Form\DataTransformer\MoneyToLocalizedStringTransformer;
use Money\Currencies\BitcoinCurrencies;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;

final class MoneyToLocalizedStringTransformerTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['cs_CZ', new Currency('CZK'), 2, false, 1599, '15,99', null],
            ['cs_CZ', new Currency('CZK'), 2, false, 999999, '9999,99', null],
            ['cs_CZ', new Currency('CZK'), 2, true, 999999, '9 999,99', null],
            ['cs_CZ', new Currency('CZK'), 1, false, 999990, '9999,9', null],
            ['en_US', new Currency('USD'), 2, true, 999999, '9,999.99', null],
            ['en_US', new Currency('USD'), 2, true, 999999, '9,999.99', null],
            ['en_US', new Currency('XBT'), 8, true, 999999, '0.00999999', new BitcoinCurrencies()],
            ['en_US', new Currency('XBT'), 8, true, 1, '0.00000001', new BitcoinCurrencies()],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDataReverseTransform(string $locale, Money\Currency $currency, int $scale, bool $grouping, int $input, string $output, ?Money\Currencies\BitcoinCurrencies $currencies): void
    {
        IntlTestHelper::requireFullIntl($this, false);

        \Locale::setDefault($locale);
        $transformer = new MoneyToLocalizedStringTransformer($currency, $scale, $grouping, $currencies);

        $input = new Money($input, $currency);

        $this->assertSame($input, $transformer->reverseTransform($output));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDataTransform(string $locale, Money\Currency $currency, int $scale, bool $grouping, int $input, string $output, ?Money\Currencies\BitcoinCurrencies $currencies): void
    {
        IntlTestHelper::requireFullIntl($this, false);

        \Locale::setDefault($locale);
        $transformer = new MoneyToLocalizedStringTransformer($currency, $scale, $grouping, $currencies);

        $input = new Money($input, $currency);

        $this->assertSame($output, $transformer->transform($input));
    }
}
