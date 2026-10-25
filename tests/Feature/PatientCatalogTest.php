<?php

namespace Tests\Feature;

use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientCatalogTest extends TestCase
{
    protected User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = User::firstOrCreate(
            ['email' => 'pasien_test@careathome.com'],
            [
                'name' => 'Pasien Tester',
                'password' => bcrypt('password'),
            ]
        );
    }

    /**
     * Test SCRUM-41: API Master Katalog dapat diakses dan mengembalikan data yang valid.
     */
    public function test_api_catalog_returns_success_and_valid_json_structure(): void
    {
        $response = $this->getJson('/api/v1/katalog');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'category',
                        'description',
                        'estimated_duration',
                        'price',
                        'formatted_price',
                    ],
                ],
                'meta' => [
                    'total',
                    'categories',
                    'price_stats',
                    'filters_applied',
                ],
            ]);
    }

    /**
     * Test SCRUM-41: API Master Katalog filter pencarian kata kunci.
     */
    public function test_api_catalog_filters_by_search_keyword(): void
    {
        $response = $this->getJson('/api/v1/katalog?search=diabetes');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $matched = str_contains(strtolower($item['name']), 'diabetes') ||
                       str_contains(strtolower($item['description']), 'diabetes');
            $this->assertTrue($matched);
        }
    }

    /**
     * Test SCRUM-41: API Detail Layanan Medis.
     */
    public function test_api_catalog_detail_returns_service_details(): void
    {
        $service = ServiceCatalog::active()->first();
        $this->assertNotNull($service, 'Seeder service catalog harus tersedia');

        $response = $this->getJson('/api/v1/katalog/' . $service->slug);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $service->id)
            ->assertJsonPath('data.name', $service->name);
    }

    /**
     * Test SCRUM-40: Halaman katalog pasien memerlukan login (auth middleware).
     */
    public function test_patient_catalog_web_requires_authentication(): void
    {
        $response = $this->get('/pasien/katalog');
        $response->assertRedirect('/login');
    }

    /**
     * Test SCRUM-40: Pasien yang terautentikasi dapat membuka katalog layanan.
     */
    public function test_authenticated_patient_can_access_catalog_page(): void
    {
        $response = $this->actingAs($this->patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('Katalog Asuhan Keperawatan Rumah')
            ->assertSee('Plafon Tarif Medis');
    }
}
