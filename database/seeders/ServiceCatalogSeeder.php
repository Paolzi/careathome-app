<?php

namespace Database\Seeders;

use App\Models\ServiceCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Perawatan Luka Diabetes & Dekubitus',
                'slug' => 'perawatan-luka-diabetes-dekubitus',
                'category' => 'Perawatan Khusus',
                'category_badge' => 'Perawatan Khusus',
                'badge_color' => 'bg-rose-50 text-rose-700 border-rose-200',
                'image_emoji' => '🩹',
                'estimated_duration' => '60 - 90 Menit',
                'estimated_duration_minutes' => 75,
                'price' => 180000,
                'description' => 'Pembersihan antiseptik steril, debridement nekrotik ringan, dan pembalutan modern wound dressing anti-mikroba untuk regenerasi optimal jaringan luka diabetes, dekubitus, atau luka pasca operasi.',
                'indications' => 'Luka ulkus diabetikum derajat 1-2, luka tekan/dekubitus stadium awal hingga sedang, luka jahitan pasca operasi terencana tanpa pendarahan aktif.',
                'contraindications' => 'Luka dengan pendarahan arteri masif, syok septik, infeksi nekrosis fasitis yang membutuhkan tindakan operasi gawat darurat (UGD/ICU).',
                'inclusions' => [
                    'Debridement & pembersihan luka aseptik steril standar RS',
                    'Pembalutan modern wound dressing anti-bakteri',
                    'Pemeriksaan tanda infeksi & monitoring vital signs lengkap',
                    'Dokumentasi E-Report & foto progres luka berkala',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pemasangan & Penggantian Kateter Urine',
                'slug' => 'pemasangan-penggantian-kateter-urine',
                'category' => 'Prosedur Medis',
                'category_badge' => 'Prosedur Medis',
                'badge_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'image_emoji' => '🩺',
                'estimated_duration' => '45 - 60 Menit',
                'estimated_duration_minutes' => 50,
                'price' => 160000,
                'description' => 'Prosedur insersi dan penggantian Foley Catheter dengan teknik aseptik tinggi oleh nakes bersertifikat untuk memastikan drainase lancar serta mencegah risiko infeksi saluran kemih (ISK).',
                'indications' => 'Retensi urin kronis pada lansia/pasien tirah baring, monitoring diuresis ketat pasca opname.',
                'contraindications' => 'Ruptur uretra, striktur uretra akut, hematuria masif dengan bekuan darah besar.',
                'inclusions' => [
                    'Penggantian kateter & urine bag steril baru',
                    'Teknik fiksasi steril dan aman untuk mobilitas',
                    'Pemeriksaan volume residu & karakteristik urin',
                    'Edukasi higienitas perawatan harian bagi keluarga',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pemberian Terapi Infus & Injeksi Terjadwal',
                'slug' => 'pemberian-terapi-infus-injeksi-terjadwal',
                'category' => 'Terapi & Injeksi',
                'category_badge' => 'Terapi Cairan',
                'badge_color' => 'bg-teal-50 text-teal-700 border-teal-200',
                'image_emoji' => '💉',
                'estimated_duration' => '45 - 60 Menit',
                'estimated_duration_minutes' => 50,
                'price' => 140000,
                'description' => 'Pemasangan abocath intravena, rehidrasi cairan fisiologis, atau injeksi vitamin dan obat berkala sesuai instruksi dan resep dokter spesialis penanggung jawab.',
                'indications' => 'Terapi rehidrasi cairan pada pemulihan demam/diare, pemberian antibiotik/analgetik terjadwal resep dokter.',
                'contraindications' => 'Pemberian obat keras sitotoksik (kemoterapi) tanpa pengawasan dokter onkologi, alergi anafilaksis berat tanpa obat emergensi.',
                'inclusions' => [
                    'Pemasangan infus steril vena perifer mikro/makro',
                    'Pengaturan tetesan & evaluasi respon tubuh',
                    'Injeksi intramuskular (IM) atau intravena (IV) aman',
                    'Monitoring tanda vital pra & pasca tindakan',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Terapi Inhalasi Nebulizer & Saluran Napas',
                'slug' => 'terapi-inhalasi-nebulizer-saluran-napas',
                'category' => 'Terapi & Injeksi',
                'category_badge' => 'Pernapasan',
                'badge_color' => 'bg-sky-50 text-sky-700 border-sky-200',
                'image_emoji' => '🫁',
                'estimated_duration' => '30 - 45 Menit',
                'estimated_duration_minutes' => 35,
                'price' => 125000,
                'description' => 'Pemberian terapi uap bronkodilator untuk mengencerkan lendir dahak dan melonggarkan jalan napas bagi penderita asma, PPOK, atau lansia dengan batuk berlendir.',
                'indications' => 'Serangan asma ringan-sedang, PPOK eksaserbasi ringan terkontrol, hipersekresi mukus bronkial.',
                'contraindications' => 'Gagal napas akut (distress napas berat), hemoptisis aktif (batuk darah), pneumotoraks tension.',
                'inclusions' => [
                    'Nebulisasi obat dengan perangkat higienis steril',
                    'Fisioterapi dada ringan & clapping postural',
                    'Pengecekan saturasi oksigen (SpO2) akurat',
                    'Edukasi teknik latihan napas dalam efektif',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pemasangan & Penggantian Selang NGT (Makan)',
                'slug' => 'pemasangan-penggantian-selang-ngt-makan',
                'category' => 'Prosedur Medis',
                'category_badge' => 'Prosedur Medis',
                'badge_color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'image_emoji' => '🥣',
                'estimated_duration' => '45 - 60 Menit',
                'estimated_duration_minutes' => 50,
                'price' => 175000,
                'description' => 'Insersi selang enteral lambung dengan pengujian keabsahan posisi lambung (auskultasi & aspirasi cairan) untuk pasien disfagia, lansia, atau pascastroke.',
                'indications' => 'Disfagia pasca stroke, penurunan nafsu makan parah pada pasien paliatif, kebutuhan nutrisi enteral cair.',
                'contraindications' => 'Fraktur basis cranii, stenosis esofagus berat, perdarahan saluran cerna bagian atas aktif.',
                'inclusions' => [
                    'Pemasangan selang NGT steril & fiksasi aman',
                    'Uji auskultasi kepastian posisi lambung',
                    'Pembilasan dan sanitasi selang enteral',
                    'Bimbingan takaran & jadwal nutrisi keluarga',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pemeriksaan Tanda Vital & Skrining Rutin',
                'slug' => 'pemeriksaan-tanda-vital-skrining-rutin',
                'category' => 'Pemeriksaan Rutin',
                'category_badge' => 'Skrining Rutin',
                'badge_color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'image_emoji' => '❤️‍🩹',
                'estimated_duration' => '30 - 45 Menit',
                'estimated_duration_minutes' => 35,
                'price' => 95000,
                'description' => 'Pengecekan komprehensif kondisi fisiologis meliputi tekanan darah digital, denyut nadi, laju respirasi, saturasi oksigen, serta cek cepat gula darah kapiler dan asam urat.',
                'indications' => 'Pemeriksaan kesehatan berkala di rumah, evaluasi pasca opname, skrining komorbid diabetes/hipertensi.',
                'contraindications' => 'Kondisi kegawatdaruratan kardiovaskular akut (nyeri dada menjalar, tidak sadarkan diri).',
                'inclusions' => [
                    'Pemeriksaan tensimeter digital terkalibrasi',
                    'Skrining cepat gula darah kapiler & kolesterol',
                    'Pencatatan grafik tren vital di E-Report',
                    'Konsultasi rujukan & rekomendasi gaya hidup',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Fisioterapi & Mobilisasi Pasca Stroke',
                'slug' => 'fisioterapi-mobilisasi-pasca-stroke',
                'category' => 'Rehabilitasi & Lansia',
                'category_badge' => 'Rehabilitasi',
                'badge_color' => 'bg-purple-50 text-purple-700 border-purple-200',
                'image_emoji' => '🚶‍♂️',
                'estimated_duration' => '60 Menit',
                'estimated_duration_minutes' => 60,
                'price' => 170000,
                'description' => 'Program latihan gerak fungsional Range of Motion (ROM) pasif/aktif di tempat tidur untuk melatih kelenturan sendi, mencegah kekakuan, dan menstimulasi pemulihan motorik.',
                'indications' => 'Hemiparesis/kelemahan fisik pasca stroke, atrofi otot akibat tirah baring lama, kontraktur sendi awal.',
                'contraindications' => 'Fraktur tulang yang belum difiksasi, trombosis vena dalam akut (DVT), hipertensi krisis tidak terkontrol.',
                'inclusions' => [
                    'Latihan mobilisasi sendi bertahap (ROM)',
                    'Teknik alih baring miring berkala (anti-dekubitus)',
                    'Stimulasi gerak fungsional & latihan duduk',
                    'Panduan teknik transfer pasien yang aman bagi keluarga',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan Bayi Baru Lahir & Ibu Nifas',
                'slug' => 'perawatan-bayi-baru-lahir-ibu-nifas',
                'category' => 'Perawatan Khusus',
                'category_badge' => 'Ibu & Anak',
                'badge_color' => 'bg-pink-50 text-pink-700 border-pink-200',
                'image_emoji' => '👶',
                'estimated_duration' => '45 - 60 Menit',
                'estimated_duration_minutes' => 50,
                'price' => 165000,
                'description' => 'Asuhan keperawatan neonatal maternitas ramah dan steril meliputi memandikan bayi baru lahir, perawatan tali pusar, pemeriksaan luka nifas, dan panduan laktasi ibu.',
                'indications' => 'Bayi baru lahir cukup bulan sehat, ibu masa nifas 0-40 hari pasca persalinan normal maupun caesar.',
                'contraindications' => 'Bayi dengan ikterus berat/sianosis (harus ke RS), infeksi tali pusar bernanah dengan sepsis.',
                'inclusions' => [
                    'Memandikan bayi & perawatan tali pusar aseptik',
                    'Pijat relaksasi lembut untuk stimulasi bayi',
                    'Pemeriksaan luka jahitan melahirkan / caesar',
                    'Konseling laktasi & posisi menyusui nyaman',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pendampingan Home Care Lansia (Geriatri)',
                'slug' => 'pendampingan-home-care-lansia-geriatri',
                'category' => 'Rehabilitasi & Lansia',
                'category_badge' => 'Home Care Lansia',
                'badge_color' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'image_emoji' => '👵',
                'estimated_duration' => '2 - 4 Jam / Sesi',
                'estimated_duration_minutes' => 180,
                'price' => 220000,
                'description' => 'Pendampingan asuhan keperawatan penuh kasih untuk lansia dengan keterbatasan fisik, pemantauan minum obat terjadwal, bantuan kebersihan diri, dan pendampingan harian.',
                'indications' => 'Lansia dengan demensia ringan, pasca rawat inap, kelemahan gerak fisik, kebutuhan pendampingan sehari-hari.',
                'contraindications' => 'Lansia dalam kondisi penurunan kesadaran akut, agitasi psikotik berat tanpa pengawasan psikiater.',
                'inclusions' => [
                    'Bantuan personal hygiene & mandi aman di rumah',
                    'Pengawasan kepatuhan minum obat terjadwal',
                    'Pemantauan asupan cairan & nutrisi harian',
                    'Pendampingan komunikasi terapeutik & stimulasi ringan',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            ServiceCatalog::updateOrCreate(
                ['slug' => $service['slug']],
                $service
            );
        }
    }
}
