<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Models\File;
use App\Http\Requests\JobRequest;
use App\Http\Resources\JobResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\JobCollection;
use App\Http\Resources\StatusResource;
use App\Http\Requests\JobSearchRequest;

class JobController extends Controller
{
    private static array $relations = [
        'user',
        'user.companyDetail',
        'skills',
        'files'
    ];
    /**
     * Display a listing of the resource.
     */
    public function index(JobSearchRequest $request)
    {
        $perPage = request()->query('per_page', 15);

        $result = Job::query()->orderByDesc('created_at');

        if ($request->has('countries')) {
            $result->whereIn('country', $request->countries);
        }

        if ($request->has('skills')) {
            $result->whereHas('skills', function ($query) use ($request) {
                $query->whereIn('skills.id', $request->skills);
            });
        }

        if ($request->has('budget')) {
            $result->whereBetween('budget', [
                $request->input('budget.start'),
                $request->input('budget.end'),
            ]);
        }

        if ($request->has('budget_type') && in_array($request->budget_type, ['hourly', 'fixed'])) {
            $result->where('budget_type', $request->budget_type);
        }

        if($request->has('duration')) {
            $result->whereIn('duration', $request->duration);
        }

        return JobCollection::make($result->with(self::$relations)->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobRequest $request): JobResource
    {
        Gate::authorize('create', Job::class);

        $user = auth()->user();

        $result = $user->jobs()->create($request->validated());

        // Sync the skills
        $result->skills()->sync($request->skills);

        $files = [];

        if ($request->input('files')) {
            $files = collect($request->input('files'))->map(function ($filePath) {
                return new File(
                    [
                        'url' => $filePath
                    ]
                );
            });

            // Attach the files
            $result->files()->saveMany($files);
        }

        return JobResource::make($result->fresh(self::$relations));
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job): JobResource
    {
        return JobResource::make($job->load(self::$relations));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobRequest $request, Job $job): JobResource
    {
        Gate::authorize('update', $job);

        $job->update($request->validated());

        // Sync the skills
        $job->skills()->sync($request->skills);

        $files = [];

        if ($request->input('files')) {
            $files = collect($request->input('files'))->map(function ($filePath) {
                return new File(
                    [
                        'url' => $filePath
                    ]
                );
            });

            // Attach the files
            $job->files()->delete();
            $job->files()->saveMany($files);
        } else if (empty($request->input('files'))) {
            $job->files()->delete();
        }

        return JobResource::make($job->fresh(self::$relations));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job): StatusResource
    {
        // Check if authenticated user is the owner of the job
        if (auth()->id() !== $job->user_uuid) {
            abort(403, 'Unauthorized');
        }

        $job->delete();

        return new StatusResource(true);
    }
}
