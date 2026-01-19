<?php

namespace App\Http\Controllers;

use App\Models\Enums\ReportReason;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            return response()->json(['error' => 'Cannot report yourself'], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = Report::create([
            'reporter_id' => $currentUser->id,
            'reported_user_id' => $user->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User reported successfully',
            'report_id' => $report->id,
        ], 201);
    }
}
