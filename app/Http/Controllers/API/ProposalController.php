<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Proposal;
use App\Models\Conversation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\ProposalRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ProposalResource;
use App\Http\Resources\ProposalCollection;
use App\Http\Requests\ProposalSearchRequest;
use App\Http\Requests\ProposalStatusRequest;

class ProposalController extends Controller
{
    private static array $relations = [
        'job',
        'user',
        'files'
    ];

    public function index(ProposalSearchRequest $request)
    {
        $user = auth()->user();

        $perPage = $request->query('per_page', 15);

        if ($user->role === "employer") {
            $result = Proposal::query()->whereHas('job', function ($query) use ($user) {
                $query->where('user_uuid', $user->uuid);
            });
        } else {
            $result = $user->proposals();
        }

        if ($request->has('job_id')) {
            $result->where('job_id', $request->job_id);
        }

        return ProposalCollection::make($result->with(self::$relations)->orderByDesc('created_at')->paginate($perPage));
    }

    public function show(Proposal $proposal)
    {
        Gate::authorize('show', $proposal);

        return ProposalResource::make($proposal->fresh(self::$relations));
    }

    public function store(ProposalRequest $request)
    {
        Gate::authorize('create', Proposal::class);

        $user = auth()->user();

        $proposal = $user->proposals()->create($request->validated());

        if ($request->input('files')) {
            $files = collect($request->input('files'))->map(function ($filePath) {
                return new File(
                    [
                        'url' => $filePath
                    ]
                );
            });

            // Attach the files
            $proposal->files()->saveMany($files);
        }

        return ProposalResource::make($proposal);
    }

    public function update(ProposalRequest $request, Proposal $proposal)
    {
        // "message": "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '9c7706f5-6956-4e77-a0e0-b6cfe5ff91ba-9c773eaf-1133-4fa6-9e4f-...' for key 'conversations_freelancer_uuid_employer_uuid_unique' (Connection: mysql, SQL: insert into `conversations` (`employer_uuid`, `freelancer_uuid`, `updated_at`, `created_at`) values (9c773eaf-1133-4fa6-9e4f-2c711ac7a6d9, 9c7706f5-6956-4e77-a0e0-b6cfe5ff91ba, 2025-01-18 13:18:59, 2025-01-18 13:18:59))",

        Gate::authorize('update', $proposal);

        $proposal->update($request->validated());

        if ($request->input('files')) {
            $files = collect($request->input('files'))->map(function ($filePath) {
                return new File(
                    [
                        'url' => $filePath
                    ]
                );
            });

            // Delete the old files
            $proposal->files()->delete();
            // Attach the files
            $proposal->files()->saveMany($files);
        }

        return ProposalResource::make($proposal->fresh(self::$relations));
    }

    public function updateStatus(ProposalStatusRequest $request, Proposal $proposal)
    {
        Gate::authorize('updateStatus', $proposal);

        $proposal->update($request->validated());

        $proposal->job->update([
            'status' => 'ongoing'
        ]);

        if($proposal->status === 'accepted') {
            Conversation::firstOrCreate(
                [
                    'employer_uuid'    => $proposal->job->user->uuid,
                    'freelancer_uuid'=> $proposal->user->uuid
                ]
            );

            // TODO: Send notification to the user
            // $proposal->job->user->notify(new ProposalAcceptedNotification($proposal));
        }

        return new StatusResource(true);
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return new StatusResource(true);
    }
}
