<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Service;

use Lle\ConfigBundle\Service\LabelFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LabelFormatterTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function codeProvider(): array
    {
        return [
            'snake case' => ['opening_hours', 'Opening hours'],
            'camel case' => ['enableAuthorization', 'Enable authorization'],
            'pascal case' => ['AlloggiatiWeb', 'Alloggiati web'],
            'acronym kept' => ['MerchantID', 'Merchant ID'],
            'acronym alone' => ['UMID', 'UMID'],
            'words with spaces' => ['Swiss QR Bill', 'Swiss QR bill'],
            'kebab case' => ['Wordline-cloud-terminal', 'Wordline cloud terminal'],
            'dash between spaces kept' => ['from (0 - 23)', 'From (0 - 23)'],
            'lower case' => ['keycafe', 'Keycafe'],
            'accented' => ['éditeur_principal', 'Éditeur principal'],
        ];
    }

    #[DataProvider('codeProvider')]
    public function testCodeIsTurnedIntoAReadableLabel(string $code, string $expected): void
    {
        $formatter = new LabelFormatter();

        self::assertSame($expected, $formatter->humanize($code));
    }

    public function testEmptyCodeStaysEmpty(): void
    {
        $formatter = new LabelFormatter();

        self::assertSame('', $formatter->humanize(''));
    }
}
