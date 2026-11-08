<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoCareAtHomeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NurseCatalogApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(DemoCareAtHomeSeeder::class);
    }

    /**
     * Test API perawat terdekat tanpa koordinat (fallback Surabaya default).
     */
    public function test_nearby_nurses_api_returns_active_nurses_without_coordinates(): void
    {
        $response = $this->getJson('/api/v1/perawat/terdekat');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'meta' => [
                    'has_patient_location' => false,
                    'is_fallback_location' => true,
                ],
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Test API perawat terdekat dengan koordinat Surabaya (-7.3144, 112.7304).
     */
    public function test_nearby_nurses_api_returns_nurses_with_surabaya_coordinates(): void
    {
        $response = $this->getJson('/api/v1/perawat/terdekat?latitude=-7.3144&longitude=112.7304');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'meta' => [
                    'has_patient_location' => true,
                    'is_fallback_location' => false,
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals('Dinda', $data[0]['name']);
        $this->assertEquals('verified', $data[0]['verification_status']);
    }

    /**
     * Test API perawat terdekat dengan koordinat jauh (radius fallback gracefully).
     */
    public function test_nearby_nurses_api_expands_radius_when_empty(): void
    {
        $response = $this->getJson('/api/v1/perawat/terdekat?latitude=-8.4095&longitude=115.1889');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'meta' => [
                    'has_patient_location' => true,
                    'radius_expanded' => true,
                ],
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Test query Haversine SQL native sesuai spesifikasi permintaan user.
     */
    public function test_native_haversine_query_builder_returns_nearby_online_verified_nurses(): void
    {
        $userLat = -7.3144;
        $userLng = 112.7304;

        $nakes = \App\Models\NurseProfile::with('user')
            ->select('nurse_profiles.*')
            ->selectRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) AS distance", [$userLat, $userLng, $userLat])
            ->where('is_online', true)
            ->where('status_verifikasi', 'verified')
            ->having('distance', '<=', 10)
            ->orderBy('distance', 'asc')
            ->get();

        $this->assertNotEmpty($nakes);
        $this->assertTrue($nakes->contains(function ($item) {
            return $item->user?->name === 'Dinda';
        }));

        foreach ($nakes as $nurse) {
            $this->assertTrue((bool) $nurse->is_online);
            $this->assertEquals('verified', $nurse->status_verifikasi);
            $this->assertLessThanOrEqual(10.0, (float) $nurse->distance);
        }
    }

    /**
     * Test halaman katalog pasien ter-render dengan sukses.
     */
    public function test_patient_catalog_page_renders_successfully(): void
    {
        $patient = User::where('email', 'pasien@careathome.com')->first();
        if (!$patient) {
            $patient = User::factory()->create();
            $patient->syncRoles(['pasien']);
        }

        $response = $this->actingAs($patient)->get('/pasien/katalog');

        $response->assertStatus(200)
            ->assertSee('Mitra Perawat Aktif')
            ->assertSee('Dinda');
    }

    /**
     * Test halaman dashboard perawat dapat diakses.
     */
    public function test_nurse_dashboard_can_be_rendered(): void
    {
        $nurse = User::where('email', 'dinda@careathome.com')->first();
        if (!$nurse) {
            $nurse = User::role('perawat')->first();
        }

        $response = $this->actingAs($nurse)->get('/perawat/dashboard');
        $response->assertStatus(200);
    }

    public function test_nurse_dashboard_unauthenticated_redirects_to_login(): void
    {
        $response = $this->get('/perawat/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test root relay controller instantiation and method invocation.
     */
    public function test_root_nurse_dashboard_controller_relay(): void
    {
        $relayController = app(\App\Http\Controllers\NurseDashboardController::class);
        $this->assertInstanceOf(\App\Http\Controllers\NurseDashboardController::class, $relayController);

        $nurse = User::where('email', 'dinda@careathome.com')->first() ?? User::role('perawat')->first();
        $this->actingAs($nurse);

        $view = $relayController->index();
        $this->assertNotNull($view);
    }
}
