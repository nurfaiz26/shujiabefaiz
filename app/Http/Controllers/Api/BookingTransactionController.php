<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\BookingTransactionResource;
use App\Http\Requests\BookingTransactionRequest;
use App\Http\Resources\Api\BookingTransactionApiResource;
use App\Models\BookingTransaction;
use App\Models\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingTransactionController extends Controller
{
    public function store (BookingTransactionRequest $request)
    {
        try {
            $validatedData = $request->validate();

            if ($request->hasFile('proof')) {
                $filePath = $request->file('proof')->store('proofs', 'public');

                $validatedData['proof'] = $filePath;
            }

            $serviceIds = $request->input('service_ids');

            if (empty($serviceIds)) {
                return response()->json(['message' => 'No services selected'], 400);
            }

            $services = HomeService::whereIn('id', $serviceIds)->get();

            if ($services->isEmpty()) {
                return response()->json(['message' => 'Invalid services'], 400);
            }

            $totalPrice = $services->sum['price'];
            $tax = 0.11 * $totalPrice;
            $grandTotal = $totalPrice + $tax;

            $validatedData['scheduled_at'] = Carbon::tomorrow()->toDateString();

            $validatedData['total_amount'] = $grandTotal;
            $validatedData['total_tax_amount'] = $tax;
            $validatedData['sub_total'] = $totalPrice;
            $validatedData['is_paid'] = false;
            $validatedData['booking_trx_id'] = BookingTransaction::generateUniqueTrxId();

            $bookingTransaction = BookingTransaction::create($validatedData);

            if (!$bookingTransaction) {
                return response()->json(['message' => 'Booking transaction failed to create'], 500);
            }

            foreach ($services as $service) {
                $bookingTransaction->transactionDetails->create([
                    'home_service_id' => $service->id,
                    'price' => $service->price,
                ]);
            }

            // return response()->json(['bookingTransaction' => $bookingTransaction], 200);
            return new BookingTransactionApiResource($bookingTransaction->load['transactionDetails']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function booking_details()
    {
        request()->validate([
            'email' => 'required|string',
            'booking_trx_id' => 'required|string'
        ]);

        $booking = BookingTransaction::where('email', request()->email)
        ->where('booking_trx_id', request()->booking_trx_id)
        ->with([
            'transactionDetails',
            'transactionDetails.homeService',
        ])
        ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 500);
        }

        return new BookingTransactionApiResource($booking);
    }
}
