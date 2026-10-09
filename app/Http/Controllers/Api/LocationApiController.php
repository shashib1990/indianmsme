<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\District;
use App\Models\Pincode;
use Illuminate\Http\Request;

class LocationApiController extends Controller
{
    /**
     * Get all active states (for searchable select)
     */
    public function states(Request $request)
    {
        $query = State::active()->orderBy('name');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        return response()->json(
            $query->select('id', 'name', 'code')->limit(100)->get()
        );
    }

    /**
     * Get districts by state (for dependent searchable select)
     */
    public function districts(Request $request)
    {
        $query = District::orderBy('name');

        if ($request->filled('state_id')) {
            $stateParam = $request->state_id;
            if (is_numeric($stateParam)) {
                $query->where('state_id', $stateParam);
            } else {
                $st = State::where('name', $stateParam)->first();
                if ($st) {
                    $query->where('state_id', $st->id);
                }
            }
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        return response()->json(
            $query->select('id', 'state_id', 'name')->limit(500)->get()
        );
    }

    /**
     * Get pincodes by district (for dependent searchable select)
     */
    public function pincodes(Request $request)
    {
        $query = Pincode::active()->orderBy('pincode');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('q')) {
            $query->where('pincode', 'like', '%' . $request->q . '%');
        }

        return response()->json(
            $query->select('id', 'state_id', 'district_id', 'pincode')->limit(300)->get()
        );
    }
}
