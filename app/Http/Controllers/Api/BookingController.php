<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingSlot;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $bookings = $user->bookings()->with('slots')->get();

        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slots' => 'required|array|min:1',
            'slots.*.start_time' => 'required|date',
            'slots.*.end_time' => 'required|date|after:slots.*.start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slots = $request->slots;
        if ($this->hasOverlappingSlots($slots)) {
            return response()->json(['error' => 'Slots in request overlap'], 422);
        }

        if ($this->hasConflictingSlots($slots)) {
            return response()->json(['error' => 'One or more slots conflict with existing bookings'], 422);
        }

        $booking = Booking::create(['user_id' => auth()->id()]);

        foreach ($slots as $slot) {
            $booking->slots()->create([
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }

        return response()->json($booking->load('slots'), 201);
    }

    public function updateSlot(Request $request, Booking $booking, BookingSlot $slot)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($slot->booking_id !== $booking->id) {
            return response()->json(['error' => 'Slot does not belong to this booking'], 422);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $conflict = BookingSlot::where('id', '!=', $slot->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                    });
            })->exists();

        if ($conflict) {
            return response()->json(['error' => 'Slot conflicts with existing booking'], 422);
        }

        $slot->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json($slot);
    }

    public function addSlot(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $conflict = BookingSlot::where(function ($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->where('end_time', '>', $request->end_time);
                });
        })->exists();

        if ($conflict) {
            return response()->json(['error' => 'Slot conflicts with existing booking'], 422);
        }

        $slot = $booking->slots()->create([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json($slot, 201);
    }

    private function hasOverlappingSlots(array $slots): bool
    {
        foreach ($slots as $i => $slot1) {
            foreach ($slots as $j => $slot2) {
                if ($i >= $j) {
                    continue;
                }

                $start1 = strtotime($slot1['start_time']);
                $end1 = strtotime($slot1['end_time']);
                $start2 = strtotime($slot2['start_time']);
                $end2 = strtotime($slot2['end_time']);

                if ($start1 < $end2 && $end1 > $start2) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasConflictingSlots(array $slots): bool
    {
        foreach ($slots as $slot) {
            $conflict = BookingSlot::where(function ($query) use ($slot) {
                $query->whereBetween('start_time', [$slot['start_time'], $slot['end_time']])
                    ->orWhereBetween('end_time', [$slot['start_time'], $slot['end_time']])
                    ->orWhere(function ($q) use ($slot) {
                        $q->where('start_time', '<', $slot['start_time'])
                            ->where('end_time', '>', $slot['end_time']);
                    });
            })->exists();

            if ($conflict) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($booking->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $booking->delete();

        return response()->json(null, 204);
    }

    public function show(Booking $booking)
    {
        return response()->json($booking->load('slots'));
    }
}
