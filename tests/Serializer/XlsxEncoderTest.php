<?php

namespace Tests\App\Serializer;

use App\Serializer\XlsxEncoder;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PHPUnit\Framework\TestCase;

class XlsxEncoderTest extends TestCase
{
    /**
     * @var XlsxEncoder
     */
    private $encoder;
    private $headers = [
        'first_name' => 'Prénom',
        'last_name_initial' => 'Nom',
        'age' => 'Age',
        'postal_code' => 'Code postal',
        'city_name' => 'Ville',
        'registered_at' => "Date d'adhesion",
    ];
    private $xlsx = [
        0 => [
            'Prénom',
            'Nom',
            'Age',
            'Code postal',
            'Ville',
            "Date d'adhesion",
        ],
        1 => [
            'Michel',
            'D.',
            '44',
            '77000',
            'Melun',
            '2019-02-14',
        ],
        2 => [
            'Carl',
            'M.',
            '66',
            '77300',
            'Fontainebleau',
            '2018-11-13',
        ],
        3 => [
            "Jean_Pierre-André dît 'JPA'",
            'D.',
            '22',
            '75008',
            'Paris 8e',
            '2018-03-08',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->encoder = new XlsxEncoder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->encoder = null;
    }

    public function testSupportEncoding()
    {
        $this->assertTrue($this->encoder->supportsEncoding('xlsx'));
        $this->assertFalse($this->encoder->supportsEncoding('csv'));
    }

    public function testEncodeInvalidArray()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->encoder->encode(['this is not an array of object values arrays'], 'xlsx');
    }

    public function testEncodeEmptyArray()
    {
        $lines = $this->transformToArray($this->encoder->encode([], 'xlsx'));
        $this->assertCount(1, $lines);
        $this->assertSame([null], $lines[0]);

        $lines = $this->transformToArray($this->encoder->encode([[]], 'xlsx'));
        $this->assertCount(1, $lines);
        $this->assertSame([null], $lines[0]);
    }

    public function testEncode()
    {
        $value = [
            0 => [
                'first_name' => 'Michel',
                'last_name_initial' => 'D.',
                'age' => 44,
                'postal_code' => '77000',
                'city_name' => 'Melun',
                'registered_at' => '2019-02-14',
            ],
        ];

        $lines = $this->transformToArray($this->encoder->encode($value, 'xlsx'));
        $this->assertCount(2, $lines);
        $this->assertEquals(array_keys($this->headers), $lines[0]);
        $this->assertEquals($this->xlsx[1], $lines[1]);
    }

    public function testEncodeCollectionWithHeaders()
    {
        $value = [
            0 => [
                'first_name' => 'Michel',
                'last_name_initial' => 'D.',
                'age' => 44,
                'postal_code' => '77000',
                'city_name' => 'Melun',
                'registered_at' => '2019-02-14',
            ],
            1 => [
                'first_name' => 'Carl',
                'last_name_initial' => 'M.',
                'age' => 66,
                'postal_code' => '77300',
                'city_name' => 'Fontainebleau',
                'registered_at' => '2018-11-13',
            ],
            2 => [
                'first_name' => "Jean_Pierre-André dît 'JPA'",
                'last_name_initial' => 'D.',
                'age' => 22,
                'postal_code' => '75008',
                'city_name' => 'Paris 8e',
                'registered_at' => '2018-03-08',
            ],
        ];

        $lines = $this->transformToArray($this->encoder->encode($value, 'xlsx', ['export_file_headers' => $this->headers]));
        $this->assertCount(4, $lines);
        $this->assertEquals($this->xlsx, $lines);
    }

    private function transformToArray(string $encodedData): array
    {
        $tmpHandle = tmpfile();
        fwrite($tmpHandle, $encodedData);
        $metaData = stream_get_meta_data($tmpHandle);
        $tmpFilename = $metaData['uri'];

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFilename);

        return $spreadsheet->getActiveSheet()->toArray();
    }
}
