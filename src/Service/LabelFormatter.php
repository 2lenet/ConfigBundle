<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Service;

/**
 * Turns a configuration code (group or label) into a readable label when no translation exists.
 */
class LabelFormatter
{
    protected const WORD_SEPARATOR = ' ';
    protected const UNDERSCORE = '_';
    protected const DASH_BETWEEN_WORDS = '/(?<=[\p{L}\p{N}])-(?=[\p{L}\p{N}])/u';
    protected const CAMEL_CASE_BOUNDARY = '/(?<=\p{Ll})(?=\p{Lu})/u';
    protected const SPACES = '/\s+/u';
    protected const LETTER = '/\p{L}/u';

    public function humanize(string $code): string
    {
        $spaced = str_replace(self::UNDERSCORE, self::WORD_SEPARATOR, $code);
        $spaced = (string) preg_replace(self::DASH_BETWEEN_WORDS, self::WORD_SEPARATOR, $spaced);
        $spaced = (string) preg_replace(self::CAMEL_CASE_BOUNDARY, self::WORD_SEPARATOR, $spaced);
        $spaced = trim((string) preg_replace(self::SPACES, self::WORD_SEPARATOR, $spaced));

        if ($spaced === '') {
            return '';
        }

        $words = [];
        foreach (explode(self::WORD_SEPARATOR, $spaced) as $position => $word) {
            $words[] = $this->formatWord($word, $position === 0);
        }

        return implode(self::WORD_SEPARATOR, $words);
    }

    protected function formatWord(string $word, bool $isFirst): string
    {
        if ($this->isAcronym($word)) {
            return $word;
        }

        $lowered = mb_strtolower($word);

        if (!$isFirst) {
            return $lowered;
        }

        return mb_strtoupper(mb_substr($lowered, 0, 1)) . mb_substr($lowered, 1);
    }

    protected function isAcronym(string $word): bool
    {
        return mb_strlen($word) > 1
            && preg_match(self::LETTER, $word) === 1
            && mb_strtoupper($word) === $word;
    }
}
