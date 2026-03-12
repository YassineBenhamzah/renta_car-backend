<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $year = $request->query('year');
        $month = $request->query('month');
        $day = $request->query('day');

        // Initial queries
        $rentalsQuery = Rental::query();
        $carsQuery = Car::query();
        $usersQuery = User::query();

        // Apply Filters
        if ($from && $to) {
            $rentalsQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            $carsQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            $usersQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        } elseif ($year) {
            $rentalsQuery->whereYear('created_at', $year);
            $carsQuery->whereYear('created_at', $year);
            $usersQuery->whereYear('created_at', $year);

            if ($month) {
                $rentalsQuery->whereMonth('created_at', $month);
                $carsQuery->whereMonth('created_at', $month);
                $usersQuery->whereMonth('created_at', $month);
            }
            if ($day) {
                $rentalsQuery->whereDay('created_at', $day);
                $carsQuery->whereDay('created_at', $day);
                $usersQuery->whereDay('created_at', $day);
            }
        }

        // 1. Monthly Revenue (for the selected year, or current year if none)
        $revenueYear = $year ?: date('Y');
        $monthlyRevenue = Rental::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_price) as total')
        )
            ->whereYear('created_at', $revenueYear)
            ->where('status', '!=', 'cancelled') // Exclude cancelled
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 2. Top Rented Cars
        $topCars = Rental::select('car_id', DB::raw('count(*) as total'))
            ->with('car:id,brand,model') // Load car details
            ->groupBy('car_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->car->brand . ' ' . $item->car->model,
                    'count' => $item->total
                ];
            });

        // 3. Rental Status Breakdown
        $rentalStatus = Rental::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // 4. Most Profitable Cars
        $profitableCars = Rental::select('car_id', DB::raw('SUM(total_price) as revenue'))
            ->with('car:id,brand,model')
            ->groupBy('car_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->car->brand . ' ' . $item->car->model,
                    'revenue' => $item->revenue
                ];
            });

        return response()->json([
            'total_cars' => $carsQuery->sum('quantity'),
            'total_rentals' => $rentalsQuery->count(),
            'total_revenue' => $rentalsQuery->sum('total_price'),
            'total_users' => $usersQuery->count(),
            'analytics' => [
                'monthly_revenue' => $monthlyRevenue,
                'top_cars' => $topCars,
                'rental_status' => $rentalStatus,
                'profitable_cars' => $profitableCars
            ],
            'filters' => [
                'from' => $from,
                'to' => $to,
                'year' => $year,
                'month' => $month,
                'day' => $day
            ]
        ]);
    }
}
