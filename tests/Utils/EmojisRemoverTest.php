<?php

namespace Tests\App\Utils;

use App\Utils\EmojisRemover;
use PHPUnit\Framework\TestCase;

class EmojisRemoverTest extends TestCase
{
    public function testEmojis()
    {
        $fixtures = file_get_contents(__DIR__.'/../Fixtures/emojis/emojis.txt');
        $this->assertEmpty(trim(EmojisRemover::remove($fixtures)));
    }
}
