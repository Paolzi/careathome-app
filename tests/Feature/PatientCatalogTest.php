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
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ServiceCatalogSeeder::class);

        $this->patient = User::firstOrCreate(
            ['email' => 'pasien_test@careathome.com'],
            [
                'name' => 'Pasien Tester',
                'password' => bcrypt('password'),
            ]
        );
        $this->patient->syncRoles(['pasien']);
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

    /**
     * Test: Halaman katalog memuat elemen UI fitur Bandingkan Layanan.
     */
    public function test_catalog_page_contains_compare_feature_elements(): void
    {
        $response = $this->actingAs($this->patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('Bandingkan Layanan')
            ->assertSee('Bandingkan Sekarang')
            ->assertSee('Perbandingan Layanan')
            ->assertSee('Pesan yang Termurah');
    }

    /**
     * Test: State Alpine.js untuk compare services diinisialisasi.
     */
    public function test_catalog_page_initializes_compare_state(): void
    {
        $response = $this->actingAs($this->patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('compareList')
            ->assertSee('compareModalOpen')
            ->assertSee('toggleCompare')
            ->assertSee('isInCompare');
    }

    /**
     * Test: Modal perbandingan memiliki kolom-kolom atribut yang benar.
     */
    public function test_compare_modal_has_correct_attribute_columns(): void
    {
        $response = $this->actingAs($this->patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('Plafon Tarif')
            ->assertSee('Termurah')
            ->assertSee('Tercepat')
            ->assertSee('Kontra')
            ->assertSee('Inklusi');
    }

    /**
     * Test: API Katalog mendukung filter harga minimum dan maksimum.
     */
    public function test_api_catalog_filters_by_price_range(): void
    {
        $response = $this->getJson('/api/v1/katalog?min_price=100000&max_price=200000');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $item) {
            $this->assertGreaterThanOrEqual(100000, $item['price']);
            $this->assertLessThanOrEqual(200000, $item['price']);
        }
    }

    /**
     * Test: API Katalog mengembalikan layanan terurut berdasarkan harga termurah.
     */
    public function test_api_catalog_sorts_by_cheapest_price(): void
    {
        $response = $this->getJson('/api/v1/katalog?sort_by=termurah');

        $response->assertStatus(200);
        $data = $response->json('data');

        if (count($data) >= 2) {
            for ($i = 1; $i < count($data); $i++) {
                $this->assertGreaterThanOrEqual($data[$i - 1]['price'], $data[$i]['price']);
            }
        }
    }

    /**
     * Test Role Access: Pasien tidak memiliki akses ke Portal Perawat (403 Forbidden).
     */
    public function test_patient_cannot_access_nurse_portal(): void
    {
        $response = $this->actingAs($this->patient)->get('/perawat/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test Role Access: Perawat tidak memiliki akses ke Portal Pasien (403 Forbidden).
     */
    public function test_nurse_cannot_access_patient_portal(): void
    {
        $nurse = User::create([
            'name' => 'Nurse Portal Test',
            'email' => 'nurse_portal_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
        $nurse->syncRoles(['perawat']);

        $response = $this->actingAs($nurse)->get('/pasien/katalog');
        $response->assertStatus(403);
    }

    /**
     * Test: API perawat terdekat mengembalikan koordinat presisi dan jarak distance_km yang valid.
     */
    public function test_nearby_nurses_api_returns_coordinates_and_distance(): void
    {
        $nurseUser = User::create([
            'name' => 'Ns. Barok Test',
            'email' => 'barok_test_' . uniqid() . '@careathome.com',
            'password' => bcrypt('password'),
        ]);
        $nurseUser->syncRoles(['perawat']);

        $profile = \App\Models\NurseProfile::create([
            'user_id' => $nurseUser->id,
            'str_number' => '12345678-STR',
            'verification_status' => 'verified',
            'is_online' => true,
            'is_available' => true,
            'latitude' => -7.318389,
            'longitude' => 112.730744,
            'location_updated_at' => now(),
        ]);

        $service = ServiceCatalog::first();
        if ($service) {
            $profile->services()->attach($service->id);
        }

        // Cari dengan koordinat pasien yang sama persis (0 km)
        $response = $this->getJson('/api/v1/perawat/terdekat?latitude=-7.318389&longitude=112.730744');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $nurses = $response->json('data');
        $this->assertNotEmpty($nurses);

        $found = collect($nurses)->firstWhere('nurse_id', $nurseUser->id);
        $this->assertNotNull($found);
        $this->assertEquals(0, $found['distance_km']);
        $this->assertEquals(-7.318389, $found['latitude']);
        $this->assertEquals(112.730744, $found['longitude']);
    }

    /**
     * Test: Halaman katalog memuat elemen UI pemilihan layanan dari perawat (Multi-service Selection).
     */
    public function test_catalog_page_contains_nurse_service_selection_elements(): void
    {
        $response = $this->actingAs($this->patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('nurseServicePickerOpen')
            ->assertSee('pickerNurse')
            ->assertSee('openNurseServicePicker')
            ->assertSee('selectNurseWithSpecificService');
    }
}
