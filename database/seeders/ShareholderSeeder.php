<?php

namespace Database\Seeders;

use App\Models\Shareholder;
use App\Models\ShareHolding;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShareholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yogaUser = User::where('email', 'yoga@cionetwork.id')->first();

        // 1. Pemegang Saham: Yoga Pratama (1 Profil Pemilik -> Punya 2 Saham)
        $yoga = Shareholder::firstOrCreate(
            ['id_card_number' => '3201123456780001'],
            [
                'user_id' => $yogaUser ? $yogaUser->id : null,
                'name' => 'Yoga Pratama',
                'email' => 'yoga@cionetwork.id',
                'phone' => '081298765432',
                'address' => 'Jl. Boulevard No. 10, Jakarta Selatan',
                'notes' => 'Pendiri & Pemegang Saham Utama',
                'status' => 'active',
            ]
        );

        // Saham 1 Yoga: CIO Network Core (10.000 lembar @ Rp 10.000 = Rp 100.000.000)
        $holding1 = ShareHolding::firstOrCreate(
            ['shareholder_id' => $yoga->id, 'share_code' => 'CIO-CORE-01'],
            [
                'entity_name' => 'CIO Network Core',
                'total_shares' => 10000,
                'nominal_value_per_share' => 10000,
                'total_investment' => 100000000,
                'percentage_share' => 55.56,
                'certificate_number' => 'CERT/CIO/2026/001',
                'acquisition_date' => '2026-01-15',
                'status' => 'active',
            ]
        );

        ShareTransaction::firstOrCreate(
            ['share_holding_id' => $holding1->id, 'reference_no' => 'TRX-INIT-001'],
            [
                'transaction_type' => 'initial',
                'shares_amount' => 10000,
                'price_per_share' => 10000,
                'total_amount' => 100000000,
                'transaction_date' => '2026-01-15',
                'notes' => 'Penyetoran modal saham pendiri',
            ]
        );

        // Saham 2 Yoga: CIO SaaS Solution (5.000 lembar @ Rp 10.000 = Rp 50.000.000)
        $holding2 = ShareHolding::firstOrCreate(
            ['shareholder_id' => $yoga->id, 'share_code' => 'CIO-SAAS-02'],
            [
                'entity_name' => 'CIO SaaS Solution',
                'total_shares' => 5000,
                'nominal_value_per_share' => 10000,
                'total_investment' => 50000000,
                'percentage_share' => 27.78,
                'certificate_number' => 'CERT/CIO/2026/002',
                'acquisition_date' => '2026-02-01',
                'status' => 'active',
            ]
        );

        ShareTransaction::firstOrCreate(
            ['share_holding_id' => $holding2->id, 'reference_no' => 'TRX-INIT-002'],
            [
                'transaction_type' => 'initial',
                'shares_amount' => 5000,
                'price_per_share' => 10000,
                'total_amount' => 50000000,
                'transaction_date' => '2026-02-01',
                'notes' => 'Alokasi saham unit produk SaaS',
            ]
        );

        // 2. Pemegang Saham: Yogi Hermawan (1 Profil Pemilik -> 1 Saham)
        $yogi = Shareholder::firstOrCreate(
            ['id_card_number' => '3201123456780002'],
            [
                'name' => 'Yogi Hermawan',
                'email' => 'yogi@cionetwork.id',
                'phone' => '081311223344',
                'address' => 'Jl. Kemang Raya No. 45, Jakarta Selatan',
                'notes' => 'Direktur Operasional',
                'status' => 'active',
            ]
        );

        $holding3 = ShareHolding::firstOrCreate(
            ['shareholder_id' => $yogi->id, 'share_code' => 'CIO-CORE-01'],
            [
                'entity_name' => 'CIO Network Core',
                'total_shares' => 2000,
                'nominal_value_per_share' => 10000,
                'total_investment' => 20000000,
                'percentage_share' => 11.11,
                'certificate_number' => 'CERT/CIO/2026/003',
                'acquisition_date' => '2026-01-15',
                'status' => 'active',
            ]
        );

        ShareTransaction::firstOrCreate(
            ['share_holding_id' => $holding3->id, 'reference_no' => 'TRX-INIT-003'],
            [
                'transaction_type' => 'initial',
                'shares_amount' => 2000,
                'price_per_share' => 10000,
                'total_amount' => 20000000,
                'transaction_date' => '2026-01-15',
                'notes' => 'Penyetoran modal saham pendiri',
            ]
        );

        // 3. Pemegang Saham: Fadil Muhammad (1 Profil Pemilik -> 1 Saham)
        $fadil = Shareholder::firstOrCreate(
            ['id_card_number' => '3201123456780003'],
            [
                'name' => 'Fadil Muhammad',
                'email' => 'fadil@cionetwork.id',
                'phone' => '081599887766',
                'address' => 'Jl. Cilandak No. 12, Jakarta Selatan',
                'notes' => 'Komisaris',
                'status' => 'active',
            ]
        );

        $holding4 = ShareHolding::firstOrCreate(
            ['shareholder_id' => $fadil->id, 'share_code' => 'CIO-CORE-01'],
            [
                'entity_name' => 'CIO Network Core',
                'total_shares' => 1000,
                'nominal_value_per_share' => 10000,
                'total_investment' => 10000000,
                'percentage_share' => 5.55,
                'certificate_number' => 'CERT/CIO/2026/004',
                'acquisition_date' => '2026-01-15',
                'status' => 'active',
            ]
        );

        ShareTransaction::firstOrCreate(
            ['share_holding_id' => $holding4->id, 'reference_no' => 'TRX-INIT-004'],
            [
                'transaction_type' => 'initial',
                'shares_amount' => 1000,
                'price_per_share' => 10000,
                'total_amount' => 10000000,
                'transaction_date' => '2026-01-15',
                'notes' => 'Penyetoran modal saham pendiri',
            ]
        );
    }
}
