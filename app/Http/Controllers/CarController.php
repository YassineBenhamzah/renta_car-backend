<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    // 1. LIST CARS (Public)
    public function index(Request $request)
    {
        $query = Car::query();

        // 1. Filter by Date Range (if provided)
        if ($request->has('start_date') && $request->has('end_date')) {
            $start = $request->start_date;
            $end = $request->end_date;

            // Exclude cars that are fully booked for the requested period
            $query->whereRaw("
                (quantity - (
                    SELECT COUNT(*) FROM rentals 
                    WHERE rentals.car_id = cars.id 
                    AND rentals.status IN ('approved', 'active', 'pending')
                    AND (
                        (start_date >= ? AND start_date < ?) OR
                        (end_date > ? AND end_date <= ?) OR
                        (start_date < ? AND end_date > ?)
                    )
                )) > 0
            ", [$start, $end, $start, $end, $start, $end]);
        }

        // 2. Filter by Status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } elseif (!$request->has('all')) {
            // By default, show available (operational) cars.
            // We will filter out "Rented" ones later if needed, or just mark them.
            $query->where('status', 'available');
        }

        // 3. Count rentals overlapping with TODAY to determine current status
        $query->withCount(['rentals as current_rentals_count' => function ($q) {
            $q->whereIn('status', ['approved', 'active', 'pending'])
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now());
        }]);

        // 4. Get the latest end_date of any active rental (for "Rented until X" display)
        $query->withMax(['rentals as rented_until' => function ($q) {
            $q->whereIn('status', ['approved', 'active', 'pending']);
        }], 'end_date');

        $cars = $query->get();

        // 5. Set real-time availability status
        $cars->transform(function ($car) {
            if ($car->current_rentals_count >= $car->quantity) {
                $car->availability = 'rented';     // Currently rented RIGHT NOW
            } else {
                $car->availability = 'available';  // Available RIGHT NOW
            }
            return $car;
        });

        return $cars;
    }
    // 2. SHOW ONE CAR (Public)
    public function show($id)
    {
        return Car::findOrFail($id);
    }
    // 3. ADD CAR (Agent/Admin only)
    public function store(Request $request)
    {
        $fields = $request->validate([
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'registration_number' => 'required|string|unique:cars',
            'price_per_day' => 'required|numeric',
            'color' => 'required|string',
            'status' => 'in:available,rented,maintenance',
            'details' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate Image
            'transmission' => 'nullable|string',
            'fuel_type' => 'nullable|string',
        ]);
        // Handle Image Upload
        if ($request->hasFile('image')) {
            // Save file to "storage/app/public/cars"
            $path = $request->file('image')->store('cars', 'public');
            // Save full URL to database
            $fields['image'] = env('APP_URL') . '/storage/' . $path;
        }
        $fields['user_id'] = $request->user()->id;
        $car = Car::create($fields);
        return response()->json($car, 201);
    }
    // 4. UPDATE CAR (Agent/Admin only)
    public function update(Request $request, $id)
    {
        $car = Car::findOrFail($id);

        $fields = $request->validate([
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'registration_number' => 'required|string|unique:cars,registration_number,' . $id,
            'price_per_day' => 'required|numeric',
            'color' => 'required|string',
            'status' => 'in:available,rented,maintenance',
            'details' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'transmission' => 'nullable|string',
            'fuel_type' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('cars', 'public');
            $fields['image'] = env('APP_URL') . '/storage/' . $path;
        }

        $car->update($fields);
        return $car;
    }
    // 5. DELETE CAR (Agent/Admin only)
    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->delete();
        return response()->json(['message' => 'Car deleted']);
    }

    // 6. CHECK AVAILABILITY CALENDAR (Public)
    public function checkAvailability($id)
    {
        $car = Car::findOrFail($id);
        $quantity = $car->quantity ?? 1;

        // Get all active rentals for this car from today onwards
        $rentals = \App\Models\Rental::where('car_id', $id)
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->where('end_date', '>=', now())
            ->get(['start_date', 'end_date']);

        // Sweep Line Algorithm to find fully booked ranges
        $events = [];
        foreach ($rentals as $rental) {
            $events[] = ['date' => $rental->start_date, 'type' => 1];
            // End date is inclusive for the rental, so availability frees up the NEXT day?
            // Usually if I rent 10-12, I drop off on 12th.
            // If drop off is at specific time, it matters.
            // Assuming daily rentals: 
            // If I book 10-12, I occupy 10, 11, 12?
            // "end_date" usually means the last day of possession.
            // So I occupy until 12th 23:59.
            // So availability returns on 13th.
            // So +1 day for the "release" event.
            $events[] = ['date' => date('Y-m-d', strtotime($rental->end_date . ' +1 day')), 'type' => -1];
        }

        // Sort events: by date, then type (processed +1 before -1? No, we want to know current load)
        // If on same day someone returns and someone picks up?
        // If Type -1 (return) happens before Type 1 (pickup)?
        // If I have 1 car. User A returns 12th. User B picks up 12th.
        // If return is morning and pickup is afternoon -> OK.
        // We assume daily granularity implies "Next Day" availability if strict.
        // But users often want same-day turnover.
        // If our logic blocked overlap strictly, then 12th is blocked.
        // Let's stick to: End Date is blocked. Start Date is blocked.
        // So User A (10-12) blocks 10, 11, 12.
        // User B cannot start 12. Can start 13.
        // So "release" event is 13th (End + 1 day).

        usort($events, function ($a, $b) {
            if ($a['date'] == $b['date']) return $a['type'] - $b['type']; // Process returns (-1) before pickups (1)? 
            // If I return on 13th (start of day), and pickup on 13th.
            // If we process -1 first, count drops, then +1 adds.
            // This allows same-day turnover (13th is free).
            // This is generous. Let's do it.
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $fullyBookedRanges = [];
        $currentCount = 0;
        $rangeStart = null;

        foreach ($events as $event) {
            $prevCount = $currentCount;
            $currentCount += $event['type'];

            // Transition to Full
            if ($prevCount < $quantity && $currentCount >= $quantity) {
                $rangeStart = $event['date'];
            }
            // Transition from Full to Available
            if ($prevCount >= $quantity && $currentCount < $quantity) {
                if ($rangeStart && $event['date'] > $rangeStart) {
                    // The range is [rangeStart, event['date'] - 1 day]
                    // Actually event['date'] is the day it BECAME available.
                    // So the booked range ends on event['date'] - 1 day.
                    // Example: Start 10th. End+1 is 13th. 
                    // Range is 10, 11, 12.
                    // Correct.
                    $fullyBookedRanges[] = [
                        'start' => $rangeStart,
                        'end' => date('Y-m-d', strtotime($event['date'] . ' -1 day'))
                    ];
                }
                $rangeStart = null;
            }
        }

        return response()->json($fullyBookedRanges);
    }
}
