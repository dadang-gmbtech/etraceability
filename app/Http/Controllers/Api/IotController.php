<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SoilMeasurement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IotController extends Controller
{
    /**
     * Terima data sensor dari perangkat IoT.
     *
     * Header wajib: X-Device-Token: {api_token}
     *
     * Body JSON:
     * {
     *   "ph":         7.20,
     *   "kelembaban": 70.00,
     *   "suhu":       28.00,
     *   "nitrogen":   22.00,
     *   "fosfor":     48.00,
     *   "kalium":     27.00
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'pesan'  => 'Token perangkat tidak valid atau perangkat tidak aktif.',
            ], 401);
        }

        $data = $request->validate([
            'ph'         => 'nullable|numeric|between:0,14',
            'kelembaban' => 'nullable|numeric|between:0,100',
            'suhu'       => 'nullable|numeric|between:-50,100',
            'nitrogen'   => 'nullable|numeric|min:0',
            'fosfor'     => 'nullable|numeric|min:0',
            'kalium'     => 'nullable|numeric|min:0',
        ]);

        $measurement = SoilMeasurement::create([
            'device_id'   => $device->id,
            'ph_level'    => $data['ph']         ?? null,
            'moisture'    => $data['kelembaban']  ?? null,
            'temperature' => $data['suhu']        ?? null,
            'nitrogen'    => $data['nitrogen']    ?? null,
            'phosphorus'  => $data['fosfor']      ?? null,
            'potassium'   => $data['kalium']      ?? null,
        ]);

        return response()->json([
            'status'      => 'ok',
            'id'          => $measurement->id,
            'perangkat'   => $device->name,
            'lahan'       => $device->lahan?->nama_lahan,
            'diterima_at' => $measurement->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * Cek koneksi ke server.
     */
    public function ping(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);

        return response()->json([
            'status'     => 'ok',
            'server_at'  => now()->toIso8601String(),
            'perangkat'  => $device?->name ?? 'tidak dikenali',
            'aktif'      => $device?->status === 'active',
        ]);
    }

    private function resolveDevice(Request $request): ?Device
    {
        $token = $request->header('X-Device-Token')
            ?? $request->bearerToken()
            ?? $request->input('token');

        if (!$token) return null;

        return Device::with('lahan')
            ->where('api_token', $token)
            ->where('status', 'active')
            ->first();
    }
}
