<?php

declare(strict_types=1);

namespace Handler;

use App\Handler\WordlistHandler;
use PHPUnit\Framework\TestCase;

final class WordlistHandlerTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'wordlist');
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testFindsSixLetterWordsMadeOfTwoOtherWords(): void
    {
        $result = $this->handle(['al', 'bums', 'albums', 'bar', 'ely', 'barely', 'other']);

        self::assertSame(['al + bums => albums', 'bar + ely => barely'], $result);
    }

    public function testBothHalvesMustBeInTheList(): void
    {
        self::assertSame([], $this->handle(['albums', 'al']));
    }

    public function testIgnoresWordsThatAreNotSixLettersLong(): void
    {
        self::assertSame([], $this->handle(['al', 'bum', 'album', 'albumss']));
    }

    public function testReportsEveryValidSplitOfAWord(): void
    {
        $result = $this->handle(['han', 'son', 'hans', 'on', 'hanson']);

        self::assertSame(['han + son => hanson', 'hans + on => hanson'], $result);
    }

    private function handle(array $words): array
    {
        file_put_contents($this->file, implode("\n", $words));

        return new WordlistHandler()->handle($this->file);
    }
}
