<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Campaign;
use App\Models\SeatInfo;

class MaindashboardController extends Controller
{
    function maindasboard(Request $request)
    {
        if (Auth::check()) {
            if (isset($request->all()['seat_id'])) {
                $seat_id = $request->all()['seat_id'];
                session(['seat_id' => $request->all()['seat_id']]);
            } else {
                if (session()->has('seat_id')) {
                    $seat_id = session('seat_id');
                } else {
                    return redirect(route('dashobardz'));
                }
            }
            // $user_id = Auth::user()->id;
            $seat = SeatInfo::where('id', $seat_id)->first();
            if ($seat->account_id != NULL) {
                $campaigns = Campaign::where('seat_id', $seat_id)->get();
                $data = [
                    'title' => 'Account Dashboard',
                    'campaigns' => $campaigns,
                ];
                return view('main-dashboard', $data);
            } else {
                session(['add_account' => true]);
                return redirect(route('dash-settings'));
            }
        } else {
            return redirect(url('/'));
        }
    }
}
