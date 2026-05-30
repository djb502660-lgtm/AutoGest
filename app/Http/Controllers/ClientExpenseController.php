<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientExpenseController extends Controller
{
    public function index(Request $request)
    {
        $vehicleIds = $request->user()->vehicles()->pluck('id');
        $since = now()->subMonths(12);

        $maintenances = Maintenance::whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'completado')
            ->where('performed_at', '>=', $since)
            ->get();

        $total = $maintenances->sum('cost');

        $categories = [
            'Cambio de aceite' => ['color' => '#38bdf8', 'amount' => 0],
            'Frenos' => ['color' => '#fb7185', 'amount' => 0],
            'Revisiones' => ['color' => '#34d399', 'amount' => 0],
            'Otros' => ['color' => '#a78bfa', 'amount' => 0],
        ];

        foreach ($maintenances as $m) {
            $desc = Str::lower($m->description);
            if (Str::contains($desc, ['aceite', 'oil'])) {
                $categories['Cambio de aceite']['amount'] += $m->cost;
            } elseif (Str::contains($desc, ['freno', 'pastilla', 'disco'])) {
                $categories['Frenos']['amount'] += $m->cost;
            } elseif ($m->type === 'preventivo' || Str::contains($desc, ['revisión', 'revision', 'general'])) {
                $categories['Revisiones']['amount'] += $m->cost;
            } else {
                $categories['Otros']['amount'] += $m->cost;
            }
        }

        $recentExpenses = Maintenance::with('vehicle')
            ->whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'completado')
            ->where('performed_at', '>=', $since)
            ->orderByDesc('performed_at')
            ->take(10)
            ->get();

        $donutGradient = $this->buildDonutGradient($categories, $total);

        return view('client.expenses.index', compact('total', 'categories', 'recentExpenses', 'donutGradient'));
    }

    private function buildDonutGradient(array $categories, float $total): string
    {
        if ($total <= 0) {
            return 'conic-gradient(#334155 0deg 360deg)';
        }

        $stops = [];
        $angle = 0;

        foreach ($categories as $cat) {
            if ($cat['amount'] <= 0) {
                continue;
            }
            $deg = ($cat['amount'] / $total) * 360;
            $stops[] = "{$cat['color']} {$angle}deg ".($angle + $deg).'deg';
            $angle += $deg;
        }

        return 'conic-gradient('.implode(', ', $stops ?: ['#334155 0deg 360deg']).')';
    }
}
