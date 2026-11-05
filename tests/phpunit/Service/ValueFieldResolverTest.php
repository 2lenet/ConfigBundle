<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Service;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Service\ValueFieldResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValueFieldResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valueTypeProvider(): iterable
    {
        yield 'bool' => [ConfigInterface::BOOL, 'valueBool'];
        yield 'string' => [ConfigInterface::STRING, 'valueString'];
        yield 'password' => [ConfigInterface::PASSWORD, 'valueString'];
        yield 'text' => [ConfigInterface::TEXT, 'valueText'];
        yield 'int' => [ConfigInterface::INT, 'valueInt'];
        yield 'float' => [ConfigInterface::FLOAT, 'valueFloat'];
    }

    #[DataProvider('valueTypeProvider')]
    public function testEachValueTypeIsStoredInItsField(string $valueType, string $expectedField): void
    {
        $resolver = new ValueFieldResolver();

        self::assertSame($expectedField, $resolver->getFieldFor($valueType));
    }

    public function testAnUnknownOrMissingValueTypeHasNoField(): void
    {
        $resolver = new ValueFieldResolver();

        self::assertNull($resolver->getFieldFor('Unknown'));
        self::assertNull($resolver->getFieldFor(null));
    }

    public function testValueFieldsAreListedOnce(): void
    {
        $resolver = new ValueFieldResolver();

        self::assertSame(
            ['valueBool', 'valueString', 'valueText', 'valueInt', 'valueFloat'],
            $resolver->getValueFields(),
        );
    }
}
