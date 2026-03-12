<?php

namespace Aroon\EgyptianNationalId\Tests;

use PHPUnit\Framework\TestCase;
use Aroon\EgyptianNationalId\EgyptianNationalId;
use Aroon\EgyptianNationalId\EgyptianNationalIdEngine;

class EgyptianNationalIdEngineTest extends TestCase
{
    private array $dataset = [];

    protected function setUp(): void
    {
        // 5 valid IDs, 2 invalid IDs
        $this->dataset = [
            EgyptianNationalId::generate(['gender' => 'male', 'governorate' => '12']), // Male, Dakahlia
            EgyptianNationalId::generate(['gender' => 'female', 'governorate' => '12']), // Female, Dakahlia
            EgyptianNationalId::generate(['gender' => 'male', 'governorate' => '01', 'year' => 2015]), // Male, Cairo, Not Adult
            EgyptianNationalId::generate(['gender' => 'female', 'governorate' => '02']), // Female, Alexandria
            EgyptianNationalId::generate(['gender' => 'female', 'governorate' => '02', 'year' => 1990]), // Female, Alexandria, Adult
            'invalid_id_1',
            '12345678901234', // Invalid checksum/century mostly
        ];
    }

    public function test_engine_filters_invalid_ids()
    {
        $engine = EgyptianNationalIdEngine::make($this->dataset);
        $this->assertCount(5, $engine->get());
    }

    public function test_engine_stats()
    {
        $engine = EgyptianNationalIdEngine::make($this->dataset);
        $stats = $engine->stats();
        
        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(2, $stats['males']);
        $this->assertEquals(3, $stats['females']);
        
        // At least one adult (from 1990) and one non-adult (2015)
        $this->assertGreaterThan(0, $stats['adults']);
        
        $this->assertArrayHasKey('Dakahlia', $stats['governorates']);
        $this->assertArrayHasKey('Cairo', $stats['governorates']);
        $this->assertArrayHasKey('Alexandria', $stats['governorates']);
    }

    public function test_engine_filter()
    {
        $engine = EgyptianNationalIdEngine::make($this->dataset);
        $males = $engine->filter(fn($id) => $id->isMale());
        
        $this->assertCount(2, $males);
    }

    public function test_engine_map_with_analysis()
    {
        $data = [
            ['id' => 1, 'n_id' => EgyptianNationalId::generate(['gender' => 'male'])],
            ['id' => 2, 'n_id' => '123'], // Invalid
        ];

        $engine = new EgyptianNationalIdEngine([]);
        $result = $engine->mapWithAnalysis($data, 'n_id');

        $this->assertIsArray($result[0]['analysis']);
        $this->assertEquals('male', $result[0]['analysis']['gender']);
        
        $this->assertNull($result[1]['analysis']);
    }
}
