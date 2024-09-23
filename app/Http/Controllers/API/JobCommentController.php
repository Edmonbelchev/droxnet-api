<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Models\JobComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\StatusResource;
use App\Http\Requests\JobCommentRequest;
use App\Http\Resources\JobCommentResource;

class JobCommentController extends Controller
{
    public function index(Job $job, Request $request)
    {
        Gate::authorize('manageComments', $job);

        $comments = $job->comments()->orderByDesc('created_at')->paginate($request->per_page ?? 10);

        return JobCommentResource::collection($comments);
    }

    public function store(Job $job, JobCommentRequest $request)
    {
        Gate::authorize('manageComments', $job);

        $user = auth()->user();

        $job->comments()->create([
            'user_uuid' => $user->uuid,
            'comment'   => $request->comment,
        ]);

        return JobCommentResource::make($job->comments()->latest()->first());
    }

    public function update(JobComment $jobComment, JobCommentRequest $request)
    {
        Gate::authorize('update', $jobComment);

        $jobComment->update([
            'comment' => $request->comment,
        ]);

        return JobCommentResource::make($jobComment);
    }

    public function destroy(JobComment $jobComment)
    {
        Gate::authorize('delete', $jobComment);

        $jobComment->delete();

        return StatusResource::make(true);
    }
}
