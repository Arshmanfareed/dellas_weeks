<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    function invoice()
    {
<<<<<<< HEAD
        $data = [
            'title' => 'Invoices'
        ];
        return view('invoice', $data);
=======
        if (Auth::check()) {
            $data = [
                'title' => 'Invoices'
            ];
            return view('invoice', $data);
        } else {
            return redirect(url('/'));
        }
>>>>>>> seat_work
    }
}
