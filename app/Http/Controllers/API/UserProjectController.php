<?php

namespace App\Http\Controllers\Api;

use App\Models\File;

use App\Models\UserProject;
use Illuminate\Http\Request;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatusResource;
use App\Http\Requests\UserProjectRequest;
use App\Http\Resources\UserProjectResource;

class UserProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Request user id and get user experiences
        if(!$request->user_uuid){
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = auth()->user();

        return UserProjectResource::collection($user->projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserProjectRequest $request)
    {
        $user = auth()->user();

        $result = [];

        $projects = collect($request->validated()['projects']);
        
        // Create the projects
        $projects->each(function ($projectData) use ($user, &$result) {
            // Create or update the files
            $project = $user->projects()->updateOrCreate([
                'id' => $projectData['id']
            ], $projectData);

            if (isset($projectData['files']) && is_array($projectData['files'])) {
                // Create the files
                $files = collect($projectData['files'])->map(function ($filePath) use ($project) {
                    return new File(
                        [
                            'filebale_id' => $project->id,
                            'fileble_type' => UserProject::class,
                            'url' => $filePath
                        ]
                    );
                });
                
                $project->files()->delete();
                $project->files()->saveMany($files);
            }

            $result[] = $project;
        });

        // Extract the IDs of the projects that were created or updated
        $resultProjectIds = collect($result)->pluck('id')->all();

        // Retrieve the IDs of the user's current projects
        $currentProjectIds = $user->projects->pluck('id')->all();

        // Determine the IDs of projects to be deleted
        $projectsToDelete = array_diff($currentProjectIds, $resultProjectIds);

        // Delete the projects that are not in the result array
        UserProject::destroy($projectsToDelete);

        return UserProjectResource::collection($result);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UserProjectRequest $request, UserProject $userProject)
    // {
    //     // Check if the project belongs to the user
    //     if ($userProject->user_uuid !== auth()->id()) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }

    //     $userProject->update([
    //         'title' => $request->title,
    //         'url'   => $request->url,
    //     ]);

    //     // Check for files
    //     if ($request->has('files')) {
    //         $userProject->files()->delete();
    //         $userProject->files()->createMany($request->files);
    //     }

    //     return UserProjectResource::make($userProject);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserProject $userProject)
    {
        // Check if the project belongs to the user
        if ($userProject->user_uuid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userProject->delete();

        return new StatusResource(true);
    }
}
