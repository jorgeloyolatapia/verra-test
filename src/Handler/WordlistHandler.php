<?php

declare(strict_types=1);

namespace App\Handler;

final class WordlistHandler
{
    private const int WORD_LENGTH = 6;

    public function handle(string $inputFile): array
    {
        $words = [];
        $candidates = [];
        foreach ($this->readLines($inputFile) as $word) {
            $words[$word] = true;

            if (strlen($word) === self::WORD_LENGTH) {
                $candidates[] = $word;
            }
        }

        sort($candidates);

        $result = [];
        foreach ($candidates as $word) {
            for ($i = 1; $i < self::WORD_LENGTH; ++$i) {
                $prefix = substr($word, 0, $i);
                $suffix = substr($word, $i);

                if (isset($words[$prefix], $words[$suffix])) {
                    $result[] = "$prefix + $suffix => $word";
                }
            }
        }

        return $result;
    }

    private function readLines(string $inputFile): \Generator
    {
        if (!is_file($inputFile) || !is_readable($inputFile)) {
            throw new \RuntimeException(\sprintf('Word list "%s" does not exist or is not readable.', $inputFile));
        }

        $handle = fopen($inputFile, 'rb');
        if ($handle === false) {
            throw new \RuntimeException(\sprintf('Could not open word list "%s".', $inputFile));
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $word = trim($line);
                if ($word !== '') {
                    yield $word;
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
