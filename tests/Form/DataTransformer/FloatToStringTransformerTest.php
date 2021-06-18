<?php

namespace Tests\App\Form\DataTransformer;

use App\Form\DataTransformer\FloatToStringTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FloatToStringTransformerTest extends TestCase
{
    /**
     * @dataProvider floatToString
     */
    public function testTransform($float, $string)
    {
        $this->assertSame($string, (new FloatToStringTransformer())->transform($float));
    }

    /**
     * @dataProvider floatToString
     */
    public function testReverseTransform($float, $string)
    {
        $this->assertSame($float, (new FloatToStringTransformer())->reverseTransform($string));
    }

    public function floatToString(): array
    {
        return [
            [0.1, '0.10'],
            [3.14, '3.14'],
            [(float) 1000, '1000.00'],
        ];
    }

    public function testTransformWithNothing()
    {
        $this->assertSame('', (new FloatToStringTransformer())->transform(null));
    }

    public function testReverseTransformWithNothing()
    {
        $this->assertSame(0.0, (new FloatToStringTransformer())->reverseTransform(null));
    }

    public function testTransformWithNoFloatValue()
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a float.');
        (new FloatToStringTransformer())->transform('42');
    }

    public function testReverseTransformWithNoStringValue()
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        (new FloatToStringTransformer())->reverseTransform(42);
    }
}
