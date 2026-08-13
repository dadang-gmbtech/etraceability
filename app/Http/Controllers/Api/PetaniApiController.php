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
     * Body: { email, password }
     * Response: { token, petani: { nama, kode_petani } }
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

        // Generate token jika belum ada (user lama sebelum booted event)
        if (empty($user->api_token)) {
            $user->api_token = \Illuminate\Support\Str::random(48);
            $user->save();
        }

        return response()->json([
            'token'  => $user->api_token,
            'petani' => $user->petani ? [
                'id'          => $user->petani->id,
                'nama'        => $user->petani->nama,
                'kode_petani' => $user->petani->kode_petani,
                'no_hp'       => $user->petani->no_hp,
            ] : null,
        ]);
    }

    /**
     * GET /api/v1/petani/profil
     */
    public function profil(Request $request): JsonResponse
    {
        $user   = $request->user();
        $petani = $user->petani;

        return response()->json([
            'petani' => [
                'id'          => $petani?->id,
                'nama'        => $petani?->nama,
                'kode_petani' => $petani?->kode_petani,
                'no_hp'       => $petani?->no_hp,
                'desa'        => $petani?->desa,
                'kecamatan'   => $petani?->kecamatan,
                'aktif'       => $petani?->aktif,
            ],
        ]);
    }

    /**
     * GET /api/v1/petani/lahan
     */
    public function lahan(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;

        $lahans = Lahan::where('petani_id', $petaniId)
            ->orderBy('kode_lahan')
            ->get(['kode_lahan', 'pemilik', 'blok_lahan', 'desa', 'luas_lahan', 'pohon_di_deres', 'kelapa_buah', 'jenis_geometri']);

        return response()->json([
            'total_lahan' => $lahans->count(),
            'total_pohon' => $lahans->sum('pohon_di_deres'),
            'lahans'      => $lahans,
        ]);
    }

    /**
     * GET /api/v1/petani/setoran?page=1&per_page=20&bulan=2026-08
     */
    public function setoran(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;
        $perPage  = min((int) $request->get('per_page', 20), 100);

        $query = SetoranGula::where('petani_id', $petaniId)
            ->with('batchProduksi:id,trace_id')
            ->orderByDesc('tanggal_setor');

        if ($bulan = $request->get('bulan')) {
            $query->whereRaw("TO_CHAR(tanggal_setor, 'YYYY-MM') = ?", [$bulan]);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => $paginated->items() ? collect($paginated->items())->map(fn ($s) => [
                'id'           => $s->id,
                'tanggal'      => $s->tanggal_setor?->format('Y-m-d'),
                'jenis_produk' => $s->jenis_produk,
                'berat_kg'     => (float) $s->berat_kg,
                'total_harga'  => (float) $s->total_harga,
                'is_anomali'   => (bool) $s->is_anomali,
                'trace_id'     => $s->batchProduksi?->trace_id,
            ]) : [],
            'meta' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/petani/rekap?tahun=2026
     * Rekap per bulan untuk grafik di app Android.
     */
    public function rekap(Request $request): JsonResponse
    {
        $petaniId = $request->user()->petani_id;
        $tahun    = $request->get('tahun', now()->year);

        $rekapBulanan = SetoranGula::where('petani_id', $petaniId)
            ->whereYear('tanggal_setor', $tahun)
            ->selectRaw("TO_CHAR(tanggal_setor, 'YYYY-MM') as bulan, COUNT(*) as jumlah, SUM(berat_kg) as total_kg, SUM(total_harga) as total_harga")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $totalKg   = SetoranGula::where('petani_id', $petaniId)->sum('berat_kg');
        $totalUang = SetoranGula::where('petani_id', $petaniId)->sum('total_harga');
        $jumlah    = SetoranGula::where('petani_id', $petaniId)->count();

        return response()->json([
            'ringkasan' => [
                'total_kg'      => (float) $totalKg,
                'total_uang'    => (float) $totalUang,
                'jumlah_setor'  => $jumlah,
            ],
            'rekap_bulanan' => $rekapBulanan->map(fn ($r) => [
                'bulan'       => $r->bulan,
                'jumlah'      => (int) $r->jumlah,
                'total_kg'    => (float) $r->total_kg,
                'total_harga' => (float) $r->total_harga,
            ]),
        ]);
    }
}
