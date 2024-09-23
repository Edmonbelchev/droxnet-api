<?php

namespace App\Http\Controllers\API;

use App\Models\Report;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ReportResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ReportCollection;
use App\Http\Requests\ReportSearchRequest;

class ReportController extends Controller
{
    public function index(ReportSearchRequest $request)
    {
        $user = auth()->user();

        $perPage = $request->per_page ?? 10;

        $reports = Report::query()->where('user_uuid', $user->uuid);

        if ($request->has('status')) {
            $reports->where('status', $request->status);
        }

        if ($request->has('type')) {
            $reports->where('reportable_type', $request->type);
        }

        return new ReportCollection($reports->paginate($perPage));
    }

    public function show(Report $report)
    {
        return new ReportResource($report);
    }

    public function store(ReportRequest $request)
    {
        $user = auth()->user();

        Report::create([
            'user_uuid'       => $user->uuid,
            'reportable_id'   => $request->reportable_id,
            'reportable_type' => $request->reportable_type,
            'reason'          => $request->reason,
            'description'     => $request->description,
        ]);

        return new StatusResource(true);
    }

    public function destroy(Report $report)
    {
        $report->delete();

        return new StatusResource(true);
    }
}
