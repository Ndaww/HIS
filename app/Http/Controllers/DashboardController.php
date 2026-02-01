<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $data = DB::select("
            SELECT
                DATE(a.created_at) AS tanggal,
                SUM(a.kwh - b.kwh) AS total_kwh
            FROM pln_meter_readings a
            JOIN pln_meter_readings b
                ON b.created_at = (
                    SELECT MAX(created_at)
                    FROM pln_meter_readings
                    WHERE created_at < a.created_at
                )
            WHERE MONTH(a.created_at) = ?
            AND YEAR(a.created_at) = ?
            AND (a.kwh - b.kwh) > 0
            GROUP BY DATE(a.created_at)
        ", [$bulan, $tahun]);

        $total = collect($data)->sum('total_kwh');
        $hari  = collect($data)->count();
        $rata  = $hari > 0 ? $total / $hari : 0;

        return response()->json([
            'total' => round($total, 2),
            'rata_rata' => round($rata, 2),
            'jumlah_hari' => $hari
        ]);
    }


    /**
     * Grafik pemakaian kWh (hari ini)
     */
    public function chart(Request $request)
    {
            $bulan = (int) $request->get('bulan', now()->month);
            $tahun = (int) $request->get('tahun', now()->year);

            $data = DB::select("
                SELECT
                    DATE(a.created_at) AS tanggal,
                    SUM(a.kwh - b.kwh) AS total_kwh
                FROM pln_meter_readings a
                JOIN pln_meter_readings b
                    ON b.created_at = (
                        SELECT MAX(created_at)
                        FROM pln_meter_readings
                        WHERE created_at < a.created_at
                    )
                WHERE MONTH(a.created_at) = ?
                AND YEAR(a.created_at) = ?
                AND (a.kwh - b.kwh) > 0
                GROUP BY DATE(a.created_at)
                ORDER BY tanggal
            ", [$bulan, $tahun]);

            return response()->json([
                'labels' => collect($data)->pluck('tanggal')->map(fn ($t) =>
                    \Carbon\Carbon::parse($t)->format('d')
                ),
                'values' => collect($data)->pluck('total_kwh')
            ]);
    }
}
