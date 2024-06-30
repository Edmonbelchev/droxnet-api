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

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request()->query('per_page', 15);

        $result = Job::query();

        return JobCollection::make($result->paginate($perPage));
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
            $files = collect($request->input('files'))->map(function ($filePath) use ($result) {
                return new File(
                    [
                        'url' => $filePath
                    ]
                );
            });

            // Attach the files
            $result->files()->saveMany($files);
        }

        return JobResource::make($result->fresh(['skills', 'files']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job): JobResource
    {
        return JobResource::make($job);
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
            $files = collect($request->input('files'))->map(function ($filePath) use ($job) {
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

        return JobResource::make($job->fresh(['skills', 'files']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job): StatusResource
    {
        // Check if authenticated user is the owner of the job
        if (auth()->id() !== $job->user_id) {
            abort(403, 'Unauthorized');
        }


        $job->delete();

        return new StatusResource(true);
    }
}
