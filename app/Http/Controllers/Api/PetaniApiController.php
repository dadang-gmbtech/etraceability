<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lahan;
use App\Models\SetoranGula;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PetaniApiController extends Controller
{
    /**
     * POST /api/v1/petani/login
     * Android LoginResponse: { token, petani_id, nama, kode_petani }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (! $user->isPetani()) {
            return response()->json(['message' => 'Akun ini bukan akun petani.'], 403);
        }

        if ($user->status !== 'approved') {
            return response()->json(['message' => 'Akun belum disetujui oleh admin.'], 403);
        }

        if (empty($user->api_token)) {
            $user->api_token = \Illuminate\Support\Str::random(48);
            $user->save();
        }

        return response()->json([
            'token'       => $user->api_token,
            'petani_id'   => $user->petani_id,
            'nama'        => $user->petani?->nama ?? $user->name,
            'kode_petani' => $user->petani?->kode_petani ?? '',
        ]);
    }

    /**
     * GET /api/v1/petani/profil
     * Android ApiResponse<Profil>: { data: { id, kode_petani, nama, no_hp, desa, kecamatan, kabupaten, aktif } }
     */
    public function profil(Request $request): JsonResponse
    {
        $petani = $request->user()->petani;

        return response()->json([
            'data' => [
                'id'          => $petani?->id,
                'kode_petani' => $petani?->kode_petani,
                'nama'        => $petani?->nama,
                'no_hp'       => $petani?->no_hp,
                'desa'        => $petani?->desa,
                'kecamatan'   => $petani?->kecamatan,
                'kabupaten'   => $petani?->kabupaten,
                'aktif'       => (bool) ($petani?->aktif ?? false),
            ],
        ]);
    }

    /**
     * GET /api/v1/petani/lahan
     * Android ApiResponse<List<Lahan>>: { data: [{ id, kode_lahan, nama_lahan, pohon_di_deres, kelapa_buah, luas, desa }] }
     */
    public function lahan(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;

        $lahans = Lahan::where('petani_id', $petaniId)
            ->orderBy('kode_lahan')
            ->get();

        return response()->json([
            'data' => $lahans->map(fn ($l) => [
                'id'           => $l->id,
                'kode_lahan'   => $l->kode_lahan,
                'nama_lahan'   => $l->pemilik ?? $l->blok_lahan,
                'pohon_di_deres' => (int) $l->pohon_di_deres,
                'kelapa_buah'  => $l->kelapa_buah !== null ? (int) $l->kelapa_buah : null,
                'luas'         => $l->luas_lahan !== null ? (float) $l->luas_lahan : null,
                'desa'         => $l->desa,
            ]),
        ]);
    }

    /**
     * GET /api/v1/petani/setoran?page=1&per_page=20&bulan=2026-08
     * Android ApiResponse<SetoranPaginated>:
     *   { data: { data: [...], current_page, last_page, total } }
     */
    public function setoran(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;
        $perPage  = min((int) $request->get('per_page', 20), 100);

        $query = SetoranGula::where('petani_id', $petaniId)
            ->orderByDesc('tanggal_setor');

        if ($bulan = $request->get('bulan')) {
            $query->whereRaw("TO_CHAR(tanggal_setor, 'YYYY-MM') = ?", [$bulan]);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => [
                'data'         => collect($paginated->items())->map(fn ($s) => [
                    'id'                  => $s->id,
                    'tanggal_setor'       => $s->tanggal_setor?->format('Y-m-d'),
                    'jenis_produk'        => $s->jenis_produk,
                    'berat_kg'            => (float) $s->berat_kg,
                    'total_harga'         => (float) $s->total_harga,
                    'hari_akumulasi'      => $s->hari_akumulasi !== null ? (int) $s->hari_akumulasi : null,
                    'is_anomali'          => (bool) $s->is_anomali,
                    'keterangan_anomali'  => $s->keterangan_anomali,
                ]),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/petani/rekap?tahun=2026
     * Android ApiResponse<RekapResponse>:
     *   { data: { bulanan: [{ bulan, jumlah, total_kg, total_harga, anomali }],
     *             total: { total_kg, total_harga, jumlah_setor, jumlah_anomali } } }
     */
    public function rekap(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;
        $tahun    = $request->get('tahun', now()->year);

        $rekapBulanan = SetoranGula::where('petani_id', $petaniId)
            ->whereYear('tanggal_setor', $tahun)
            ->selectRaw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan, COUNT(*) as jumlah, SUM(berat_kg) as total_kg, SUM(total_harga) as total_harga, SUM(CASE WHEN is_anomali THEN 1 ELSE 0 END) as anomali")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $totalAll = SetoranGula::where('petani_id', $petaniId)
            ->selectRaw("SUM(berat_kg) as total_kg, SUM(total_harga) as total_harga, COUNT(*) as jumlah_setor, SUM(CASE WHEN is_anomali THEN 1 ELSE 0 END) as jumlah_anomali")
            ->first();

        return response()->json([
            'data' => [
                'bulanan' => $rekapBulanan->map(fn ($r) => [
                    'bulan'       => $r->bulan,
                    'jumlah'      => (int) $r->jumlah,
                    'total_kg'    => (float) $r->total_kg,
                    'total_harga' => (float) $r->total_harga,
                    'anomali'     => (int) $r->anomali,
                ]),
                'total' => [
                    'total_kg'       => (float) ($totalAll->total_kg ?? 0),
                    'total_harga'    => (float) ($totalAll->total_harga ?? 0),
                    'jumlah_setor'   => (int)   ($totalAll->jumlah_setor ?? 0),
                    'jumlah_anomali' => (int)   ($totalAll->jumlah_anomali ?? 0),
                ],
            ],
        ]);
    }
}
