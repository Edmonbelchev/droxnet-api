<?php

namespace App\Http\Controllers\API;

use App\Models\Proposal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\ProposalRequest;
use App\Http\Resources\StatusResource;
use App\Http\Resources\ProposalCollection;

class ProposalController extends Controller
{
    private static array $relations = [
        'job',
        'user'
    ];

    public function index() {
        $user = auth()->user();

        $perPage = request()->query('per_page', 15);

        $result = $user->proposals();

        return ProposalCollection::make($result->with(self::$relations)->paginate($perPage));
    }

    public function show(Proposal $proposal) {
        Gate::authorize('show', $proposal);
        
        return ProposalCollection::make($proposal->with(self::$relations));
    }

    public function store(ProposalRequest $request) {
        Gate::authorize('create', Proposal::class);

        $user = auth()->user();

        $proposal = $user->proposals()->create($request->validated());

        return ProposalCollection::make($proposal);
    }

    public function update(ProposalRequest $request, Proposal $proposal) {
        Gate::authorize('update', $proposal);

        $proposal->update($request->validated());

        return ProposalCollection::make($proposal);
    }

    public function destroy(Proposal $proposal) {
        $proposal->delete();

        return new StatusResource(true);
    }
}
