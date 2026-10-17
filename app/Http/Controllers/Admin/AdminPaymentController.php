<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        return Inertia::render('Admin/Payments/Index', [
            'payments' => Payment::query()->with('user:id,name,username,email')->when(in_array($status, ['pending', 'completed', 'failed', 'refunded'], true), fn ($q) => $q->where('status', $status))->latest()->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }
}
