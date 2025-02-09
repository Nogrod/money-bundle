<?php

declare(strict_types=1);

namespace JK\MoneyBundle\Tests\Form\Type;

use JK\MoneyBundle\Form\Type\MoneyType;
use Money\Currency;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Intl\Util\IntlTestHelper;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * Class MoneyTypeTest.
 *
 * @author Jakub Kucharovic <jakub@kucharovic.cz>
 */
final class MoneyTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        // we test against different locales, so we need the full
        // implementation
        IntlTestHelper::requireFullIntl($this, false);

        parent::setUp();
    }

    public function testPassLocalizedMoneyPatternToView(): void
    {
        \Locale::setDefault('cs_CZ');

        $view = $this->factory->create(MoneyType::class)->createView();

        $this->assertSame('{{ widget }} Kč', $view->vars['money_pattern']);
    }

    public function testPassMoneyPatternToView(): void
    {
        \Locale::setDefault('en_US');

        $view = $this->factory->create(MoneyType::class)->createView();

        $this->assertSame('CZK {{ widget }}', $view->vars['money_pattern']);
    }

    public function testPassOverriddenMoneyPatternToView(): void
    {
        $view = $this->factory->create(MoneyType::class, null, ['currency' => new Currency('EUR')])->createView();

        $this->assertSame('€ {{ widget }}', $view->vars['money_pattern']);
    }

    public function testPassWrongTypedCurrencies(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->factory->create(MoneyType::class, null, ['currencies' => ['EUR']]);
    }

    public function testPassWrongTypedCurrency(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->factory->create(MoneyType::class, null, ['currency' => 123]);
    }

    protected function getExtensions()
    {
        return [
            new PreloadedExtension([
                new MoneyType('CZK'),
            ], []),
        ];
    }
}
