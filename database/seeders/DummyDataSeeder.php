<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Package;
use App\Models\Photo;
use App\Models\PhotoSession;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama (kecuali users admin & settings)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Photo::truncate();
        PhotoSession::truncate();
        Transaction::truncate();
        Template::truncate();
        Package::truncate();
        Branch::truncate();
        User::where('role', '!=', 'admin')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Branches ──────────────────────────────────────────────────────────
        $branchPhotos = collect(glob(storage_path('app/public/branches/*.{jpg,jpeg,png}'), GLOB_BRACE))
            ->map(fn ($f) => 'branches/'.basename($f))
            ->values();

        $branches = collect([
            ['name' => 'Philo Jakarta Selatan', 'code' => 'PL-JKS', 'address' => 'Jl. Kemang Raya No. 12, Jakarta Selatan', 'phone' => '02178901234', 'is_active' => true,  'photo' => $branchPhotos->get(0)],
            ['name' => 'Philo Bandung',          'code' => 'PL-BDG', 'address' => 'Jl. Braga No. 45, Bandung',               'phone' => '02298765432', 'is_active' => true,  'photo' => $branchPhotos->get(1)],
            ['name' => 'Philo Surabaya',         'code' => 'PL-SBY', 'address' => 'Jl. Tunjungan No. 88, Surabaya',          'phone' => '03155667788', 'is_active' => true,  'photo' => $branchPhotos->get(2)],
            ['name' => 'Philo Yogyakarta',       'code' => 'PL-YGY', 'address' => 'Jl. Malioboro No. 21, Yogyakarta',        'phone' => '02741122334', 'is_active' => false, 'photo' => $branchPhotos->get(3)],
        ])->map(fn ($d) => Branch::create($d));

        // ── Packages ──────────────────────────────────────────────────────────
        $packages = collect([
            ['name' => 'Paket Strip',    'description' => '2 foto cetak ukuran 2x6 inci, cocok untuk kenangan berdua.',          'photo_count' => 2, 'print_size' => 'strip', 'price' => 20000, 'is_active' => true],
            ['name' => 'Paket A4',       'description' => '4 foto dalam grid cantik ukuran A4, pas untuk kelompok.',             'photo_count' => 4, 'print_size' => 'A4',    'price' => 35000, 'is_active' => true],
            ['name' => 'Paket A3 Grand', 'description' => '6 foto lengkap ukuran A3, hasil terbaik untuk momen spesial.',        'photo_count' => 6, 'print_size' => 'A3',    'price' => 50000, 'is_active' => true],
        ])->map(fn ($d) => Package::create($d));

        // ── Templates ─────────────────────────────────────────────────────────
        $thumbnails = collect(glob(storage_path('app/public/templates/thumbnails/*.{png,jpg,jpeg}'), GLOB_BRACE))
            ->map(fn ($f) => 'templates/thumbnails/'.basename($f))
            ->values();

        $templates = collect([
            [
                'name' => 'Classic White', 'thumbnail_path' => $thumbnails->get(0), 'frame_path' => 'templates/classic-white.png',
                'print_size' => 'strip', 'photo_slots' => 2, 'is_active' => true,
                'slot_positions' => [
                    ['x' => 30,  'y' => 30, 'width' => 560, 'height' => 740],
                    ['x' => 610, 'y' => 30, 'width' => 560, 'height' => 740],
                ],
            ],
            [
                'name' => 'Dark Aesthetic', 'thumbnail_path' => $thumbnails->get(1), 'frame_path' => 'templates/dark-aesthetic.png',
                'print_size' => 'A4', 'photo_slots' => 4, 'is_active' => true,
                'slot_positions' => [
                    ['x' => 30,  'y' => 30,  'width' => 565, 'height' => 370],
                    ['x' => 605, 'y' => 30,  'width' => 565, 'height' => 370],
                    ['x' => 30,  'y' => 410, 'width' => 565, 'height' => 370],
                    ['x' => 605, 'y' => 410, 'width' => 565, 'height' => 370],
                ],
            ],
            [
                'name' => 'Vintage Film', 'thumbnail_path' => $thumbnails->get(2), 'frame_path' => 'templates/vintage-film.png',
                'print_size' => 'A3', 'photo_slots' => 6, 'is_active' => true,
                'slot_positions' => [
                    ['x' => 30,  'y' => 30,  'width' => 370, 'height' => 370],
                    ['x' => 415, 'y' => 30,  'width' => 370, 'height' => 370],
                    ['x' => 800, 'y' => 30,  'width' => 370, 'height' => 370],
                    ['x' => 30,  'y' => 415, 'width' => 370, 'height' => 370],
                    ['x' => 415, 'y' => 415, 'width' => 370, 'height' => 370],
                    ['x' => 800, 'y' => 415, 'width' => 370, 'height' => 370],
                ],
            ],
            [
                'name' => 'Pastel Dream', 'thumbnail_path' => $thumbnails->get(3), 'frame_path' => 'templates/pastel-dream.png',
                'print_size' => 'A4', 'photo_slots' => 4, 'is_active' => true,
                'slot_positions' => [
                    ['x' => 40,  'y' => 40,  'width' => 540, 'height' => 350],
                    ['x' => 620, 'y' => 40,  'width' => 540, 'height' => 350],
                    ['x' => 40,  'y' => 420, 'width' => 540, 'height' => 350],
                    ['x' => 620, 'y' => 420, 'width' => 540, 'height' => 350],
                ],
            ],
        ])->map(fn ($d) => Template::create($d));

        // ── User cabang ───────────────────────────────────────────────────────
        $jks = $branches->firstWhere('code', 'PL-JKS');
        User::create([
            'name' => 'Operator Jakarta Selatan',
            'email' => 'operator.jks@philo.id',
            'password' => bcrypt('password'),
            'role' => 'cabang',
            'phone' => '08122334455',
            'branch_id' => $jks->id,
        ]);

        // ── Transactions → Sessions → Photos ──────────────────────────────────
        $activeBranches = $branches->where('is_active', true)->values();

        // Distribusi status per cabang
        $distribution = [
            ['status' => 'paid',    'count' => 55],
            ['status' => 'expired', 'count' => 18],
            ['status' => 'pending', 'count' => 8],
            ['status' => 'failed',  'count' => 4],
        ];

        foreach ($activeBranches as $branch) {
            foreach ($distribution as ['status' => $status, 'count' => $total]) {
                for ($i = 0; $i < $total; $i++) {
                    $package = $packages->random();
                    $createdAt = Carbon::now()->subDays(rand(0, 60))->subMinutes(rand(0, 1440));

                    $txData = [
                        'order_id' => 'PHILO-'.strtoupper(Str::random(6)).'-'.$createdAt->format('YmdHis'),
                        'branch_id' => $branch->id,
                        'package_id' => $package->id,
                        'amount' => $package->price,
                        'payment_method' => 'qris',
                        'status' => $status,
                        'qris_string' => Str::random(32),
                        'expired_at' => $createdAt->copy()->addMinutes(15),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];

                    if ($status === 'paid') {
                        $txData['paid_at'] = $createdAt->copy()->addMinutes(rand(1, 10));
                        $txData['duitku_reference'] = 'DK'.strtoupper(Str::random(12));
                    }

                    $transaction = Transaction::create($txData);

                    if ($status !== 'paid') {
                        continue;
                    }

                    // 80% sesi completed, 20% masih capturing
                    $isCompleted = rand(1, 10) <= 8;
                    $template = rand(1, 10) > 2 ? $templates->random() : null;
                    $paidAt = $txData['paid_at'];
                    $completedAt = $isCompleted
                        ? $paidAt->copy()->addMinutes(rand(5, 20))
                        : null;

                    $session = PhotoSession::create([
                        'transaction_id' => $transaction->id,
                        'branch_id' => $branch->id,
                        'template_id' => $template?->id,
                        'status' => $isCompleted ? 'completed' : 'capturing',
                        'final_image_path' => $isCompleted ? 'final/dummy_'.$transaction->id.'.jpg' : null,
                        'completed_at' => $completedAt,
                        'created_at' => $paidAt,
                        'updated_at' => $completedAt ?? $paidAt,
                    ]);

                    if (! $isCompleted) {
                        continue;
                    }

                    for ($p = 1; $p <= $package->photo_count; $p++) {
                        Photo::create([
                            'photo_session_id' => $session->id,
                            'original_path' => 'photos/dummy_'.$session->id.'_'.$p.'.jpg',
                            'order' => $p,
                            'created_at' => $completedAt,
                            'updated_at' => $completedAt,
                        ]);
                    }
                }
            }
        }
    }
}
