<?php

namespace Tests\App\ValueObject;

use App\ValueObject\SHA1;
use PHPUnit\Framework\TestCase;

class SHA1Test extends TestCase
{
    /**
     * @dataProvider provideInvalidHash
     */
    public function testCreateSHA1FailsWithInvalidSHA1Hash(string $hash)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/The given hash ".+" does not have a valid SHA-1 format\./');
        SHA1::fromString($hash);
    }

    public function provideInvalidHash(): array
    {
        return [
            'invalid SHA1 hash' => ['2zzz8079f27fd1fab76b28420f5ff2ccbe57a283'],
            'MD5 hash' => ['2ddd8079f27fd1fab76b28420f5ff2cc'],
            'string' => ['hash'],
        ];
    }

    public function testHashesAreEqual()
    {
        $hash1 = SHA1::fromString('0e59dcce13aff851a7462f18c491faba653dcb1a');
        $hash2 = SHA1::fromString('0e59dcce13aff851a7462f18c491faba653dcb1a', true);

        $this->assertTrue($hash1->equals($hash2));
        $this->assertTrue($hash2->equals($hash1));

        $hash3 = SHA1::fromString('0E59DCCE13AFF851A7462F18C491FABA653DCB1A');
        $hash4 = SHA1::fromString('0E59DCCE13AFF851A7462F18C491FABA653DCB1A', true);

        $this->assertTrue($hash1->equals($hash3));
        $this->assertTrue($hash3->equals($hash1));
        $this->assertTrue($hash4->equals($hash1));
        $this->assertFalse($hash4->equals($hash1, true));

        $hash5 = SHA1::fromString('9379c404398bf4f1000113a84c0ef758ed87089d');

        $this->assertTrue($hash5->equals($hash5));
        $this->assertFalse($hash5->equals($hash1));
        $this->assertFalse($hash5->equals($hash2));
        $this->assertFalse($hash5->equals($hash3));
        $this->assertFalse($hash5->equals($hash4));
    }

    /**
     * @dataProvider provideHash
     */
    public function testCreateFromString(string $expectedHash, string $initialHash, bool $preserveCase)
    {
        $sha1 = SHA1::fromString($initialHash, $preserveCase);

        $this->assertSame($expectedHash, $sha1->getHash());
        $this->assertSame($expectedHash, (string) $sha1);
    }

    public function provideHash(): array
    {
        return [
            ['3c5340eca1e0a1ac201e4ae648ba11f2ddddddd9', '3c5340eca1e0a1ac201e4ae648ba11f2ddddddd9', true],
            ['3C5340ECA1E0A1AC201E4AE648BA11F2CCC12345', '3C5340ECA1E0A1AC201E4AE648BA11F2CCC12345', true],
            ['3c5340eca1e0a1ac201e4ae648ba11f2b789cd4e', '3C5340ECA1E0A1AC201E4AE648BA11F2B789CD4E', false],
        ];
    }
}
