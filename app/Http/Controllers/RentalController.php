<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RentalController extends Controller
{
    // 1. MAKE A REQUEST (User)
    public function store(Request $request)
    {
        $fields = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'cin_recto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'cin_verso' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'permis_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = $request->user();

        // Update documents if provided
        if ($request->hasFile('cin_recto')) {
            $user->cin_recto_path = $request->file('cin_recto')->store('documents', 'local');
        }
        if ($request->hasFile('cin_verso')) {
            $user->cin_verso_path = $request->file('cin_verso')->store('documents', 'local');
        }
        if ($request->hasFile('permis_image')) {
            $user->permis_path = $request->file('permis_image')->store('documents', 'local');
        }

        if ($user->isDirty()) {
            $user->save();
        }

        // 1. Get the Car
        $car = Car::findOrFail($fields['car_id']);

        // Check Availability (Quantity)
        $start = $fields['start_date'];
        $end = $fields['end_date'];

        $activeRentals = Rental::where('car_id', $car->id)
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_date', '>=', $start)
                        ->where('start_date', '<', $end);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('end_date', '>', $start)
                        ->where('end_date', '<=', $end);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_date', '<', $start)
                        ->where('end_date', '>', $end);
                });
            })->count();

        if ($activeRentals >= $car->quantity) {
            return response()->json(['message' => 'Car is fully booked for these dates (Quantity exceeded).'], 422);
        }

        // 2. Calculate Days
        $startTs = strtotime($fields['start_date']);
        $endTs = strtotime($fields['end_date']);
        $days = ($endTs - $startTs) / (60 * 60 * 24);

        // 3. Create Rental (Calculate Price Here)
        $rental = Rental::create([
            'user_id' => $user->id,
            'car_id' => $fields['car_id'],
            'start_date' => $fields['start_date'],
            'end_date' => $fields['end_date'],
            'days' => $days,
            'total_price' => $days * $car->price_per_day,
            'status' => 'pending'
        ]);

        // Notify Admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewBooking($rental));
        }

        return response()->json($rental, 201);
    }
    // 2. MY REQUESTS (User)
    public function myRentals(Request $request)
    {
        return Rental::where('user_id', $request->user()->id)->with('car')->get();
    }
    // 3. ALL REQUESTS (Agent/Admin)
    public function index()
    {
        return Rental::with(['user', 'car'])->orderBy('created_at', 'desc')->get();
    }
    // 4. APPROVE/REJECT/ACTIVATE/COMPLETE (Agent/Admin)
    public function updateStatus(Request $request, $id)
    {
        $fields = $request->validate([
            'status' => 'required|in:approved,rejected,active,completed,canceled,returned'
        ]);

        $rental = Rental::findOrFail($id);

        // Map 'returned' to 'completed' (same DB value, clearer intent)
        $status = $fields['status'] === 'returned' ? 'completed' : $fields['status'];

        $updateData = [
            'status' => $status,
        ];

        // If approving, set the approver
        if ($status === 'approved' && !$rental->approver_id) {
            $updateData['approver_id'] = $request->user()->id;
        }

        $rental->update($updateData);

        // Notify User
        $rental->user->notify(new \App\Notifications\RentalStatusChanged($rental, $status));

        return $rental;
    }

    // 5. UPLOAD PAYMENT (User)
    public function uploadPayment(Request $request, $id)
    {
        $fields = $request->validate([
            'payment_method' => 'required|in:cheque,espece,banque',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Only required if banque?
        ]);

        $rental = Rental::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        $path = null;
        if ($request->hasFile('payment_proof')) {
            // Store on 'local' disk (private) instead of 'public'
            $path = $request->file('payment_proof')->store('payments', 'local');
        }

        $rental->update([
            'payment_method' => $fields['payment_method'],
            'payment_proof' => $path, // Save relative path ONLY
            'payment_status' => 'completed', // Or 'verification_pending'
            'status' => 'active' // If payment is done, maybe set to active immediately if auto-approve?
            // Actually, admin should verify receipt. Let's keep status 'approved' but payment_status 'completed'
        ]);

        // Notify Admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PaymentSubmitted($rental));
        }

        return $rental;
    }

    // 6. ON-SITE BOOKING (Agent/Admin)
    public function storeOnSite(Request $request)
    {
        $fields = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'cin' => 'required|string',
            'permis' => 'required|string',
            'address' => 'nullable|string',
            'payment_method' => 'required|in:espece,cheque,banque',
            'payment_reference' => 'nullable|string|max:255',
            'cin_recto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'cin_verso' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'permis_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // 1. Find or create user
        $user = User::where('email', $fields['email'])
            ->orWhere('cin', $fields['cin'])
            ->first();

        if (!$user) {
            $user = User::create([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make(Str::random(12)), // Random password for walk-in
                'phone' => $fields['phone'],
                'cin' => $fields['cin'],
                'permis' => $fields['permis'],
                'address' => $fields['address'],
                'role' => 'user'
            ]);
        }

        // Update documents if provided
        if ($request->hasFile('cin_recto')) {
            $user->cin_recto_path = $request->file('cin_recto')->store('documents', 'local');
        }
        if ($request->hasFile('cin_verso')) {
            $user->cin_verso_path = $request->file('cin_verso')->store('documents', 'local');
        }
        if ($request->hasFile('permis_image')) {
            $user->permis_path = $request->file('permis_image')->store('documents', 'local');
        }
        if ($user->isDirty()) {
            $user->save();
        }

        // 2. Get Car & Calculate Days
        $car = Car::findOrFail($fields['car_id']);

        // Check Availability (Quantity) OnSite
        $start = $fields['start_date'];
        $end = $fields['end_date'];

        $activeRentals = Rental::where('car_id', $car->id)
            ->whereIn('status', ['approved', 'active', 'pending'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_date', '>=', $start)
                        ->where('start_date', '<', $end);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('end_date', '>', $start)
                        ->where('end_date', '<=', $end);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_date', '<', $start)
                        ->where('end_date', '>', $end);
                });
            })->count();

        if ($activeRentals >= $car->quantity) {
            return response()->json(['message' => 'Car is fully booked for these dates (Quantity exceeded).'], 422);
        }

        $startTs = strtotime($fields['start_date']);
        $endTs = strtotime($fields['end_date']);
        $days = ($endTs - $startTs) / (60 * 60 * 24);

        // 3. Create Rental directly as 'approved' or 'active'
        $rental = Rental::create([
            'user_id' => $user->id,
            'car_id' => $fields['car_id'],
            'start_date' => $fields['start_date'],
            'end_date' => $fields['end_date'],
            'days' => $days,
            'total_price' => $days * $car->price_per_day,
            'status' => 'active', // Since agent is doing it, it's verified
            'approver_id' => $request->user()->id,
            'payment_status' => 'completed',
            'payment_method' => $fields['payment_method'],
            'payment_reference' => $fields['payment_reference'] ?? null
        ]);

        return response()->json($rental, 201);
    }

    // 7. GET PAYMENT PROOF (Authorized Only)
    public function getPaymentProof($id)
    {
        $rental = Rental::findOrFail($id);
        $user = auth()->user();

        // Check if user is owner OR admin/agent
        if ($user->id !== $rental->user_id && !in_array($user->role, ['admin', 'agent'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$rental->payment_proof || !Storage::disk('local')->exists($rental->payment_proof)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('local')->response($rental->payment_proof);
    }

    // 8. GET USER DOCUMENT (Authorized Only)
    public function getUserDocument($id, $type)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        // Check if current user is admin/agent OR the owner of the document
        if ($currentUser->id !== $user->id && !in_array($currentUser->role, ['admin', 'agent'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = match ($type) {
            'cin_recto' => $user->cin_recto_path,
            'cin_verso' => $user->cin_verso_path,
            'permis' => $user->permis_path,
            default => null,
        };

        if (!$path || !Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('local')->response($path);
    }

    // 9. DOWNLOAD PDF CONTRACT (Authorized Only)
    public function downloadPdf($id)
    {
        $rental = Rental::with(['user', 'car', 'approver'])->findOrFail($id);
        $user = auth()->user();

        // Check authorization: User must be owner OR admin/agent
        if ($user->id !== $rental->user_id && !in_array($user->role, ['admin', 'agent'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rental_contract', compact('rental'));

        return $pdf->download('rental-contract-' . $rental->id . '.pdf');
    }
}
