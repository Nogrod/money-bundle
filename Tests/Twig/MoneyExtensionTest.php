<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Tests\Twig;

use JK\MoneyBundle\Twig\MoneyExtension;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;
use Twig\TwigFilter;

final class MoneyExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        IntlTestHelper::requireFullIntl($this, false);
        parent::setUp();
    }

    public function dataProvider(): array
    {
        return [
            ['cs_CZ', 'CZK', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_CURRENCY, 1599, '15,99 Kč'],
            ['cs_CZ', 'CZK', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_DECIMAL, 1599, '15,99'],
            ['cs_CZ', 'EUR', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_CURRENCY, 1599, '15,99 €'],
            ['en_US', 'EUR', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_CURRENCY, 1599, '€15.99'],
            ['en_US', 'EUR', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_CURRENCY, 151599, '€1,515.99'],
            ['cs_CZ', 'CZK', 2, MoneyExtension::GROUPING_USED, MoneyExtension::FORMAT_CURRENCY, 151599, '1 515,99 Kč'],
            ['cs_CZ', 'CZK', 2, MoneyExtension::GROUPING_NONE, MoneyExtension::FORMAT_CURRENCY, 151599, '1515,99 Kč'],
            ['cs_CZ', 'CZK', 1, MoneyExtension::GROUPING_NONE, MoneyExtension::FORMAT_CURRENCY, 151590, '1515,9 Kč'],
            ['cs_CZ', 'CZK', 0, MoneyExtension::GROUPING_NONE, MoneyExtension::FORMAT_DECIMAL, 151590, '1516'],
        ];
    }

    public function testGetFilters(): void
    {
        $extension = new MoneyExtension('cs_CZ');

        $this->assertSame(
            [new TwigFilter('money', $extension->moneyFilter(...))],
            $extension->getFilters()
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testMoneyFilter(string $locale, string $currency, int $scale, bool $grouping, bool $format, int $input, string $output): void
    {
        \Locale::setDefault($locale);
        $extension = new MoneyExtension($locale);
        $input = new Money($input, new Currency($currency));
        $this->assertSame(
            $extension->moneyFilter($input, $scale, $grouping, $format),
            $output
        );
    }
}
