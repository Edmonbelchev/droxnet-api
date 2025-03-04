<?php

namespace App\Http\Controllers\API;

use App\Models\Job;
use App\Models\File;
use App\Models\User;
use App\Events\NewMessage;
use App\Models\Transaction;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Requests\JobRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\JobResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\JobCollection;
use App\Http\Resources\StatusResource;
use App\Http\Requests\JobSearchRequest;
use App\Http\Requests\JobStatusRequest;

class JobController extends Controller
{
    private static array $relations = [
        'user',
        'user.companyDetail',
        'skills',
        'files'
    ];

    public function __construct(
        private readonly PaymentService $paymentService
    ) {
        // Individual authorization will be handled in each method
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(JobSearchRequest $request)
    {
        $perPage = request()->query('per_page', 15);

        $result = Job::query()->where('status', 'proposal')->orderByDesc('created_at');

        if ($request->has('user_uuid')) {
            $result->whereHas('user', function ($query) use ($request) {
                $query->where('uuid', $request->user_uuid)
                    ->where('role', 'employer');
            });
        }

        if ($request->has('countries')) {
            $result->whereIn('country', $request->countries);
        }

        if ($request->has('languages')) {
            $result->where(function ($query) use ($request) {
                foreach ($request->languages as $language) {
                    $query->orWhereJsonContains('languages', $language);
                }
            });
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

        if ($request->has('duration')) {
            $result->whereIn('duration', $request->duration);
        }

        return JobCollection::make($result->with(self::$relations)->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobRequest $request): JobResource
    {
        Gate::authorize('store', Job::class);

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
        self::$relations[] = 'proposals';
        self::$relations[] = 'proposals.user';
        self::$relations[] = 'acceptedProposals';
        self::$relations[] = 'acceptedProposals.user';

        return JobResource::make($job->load(self::$relations));
    }

    public function JobCounter(Request $request)
    {
        $user = auth()->user();

        $result = Job::where('status', $request->status);

        if($user->role === 'freelancer') {
            $result->whereHas('proposals', function($query) use ($user) {
                $query->where('user_uuid', $user->uuid)
                    ->where('status', 'accepted');
            });
        } else {
            $result->where('user_uuid', $user->uuid);
        }

        $data = [
            'data' => [
                'total' => $result->count()
            ]
        ];

        return response()->json($data);
    }

    public function updateStatus(JobStatusRequest $request, Job $job)
    {
        Gate::authorize('updateStatus', $job);

        $data = $request->validated();

        $freelancer = User::find($job->acceptedProposals[0]->user->uuid)->first();
        $conversation = Conversation::where('employer_uuid', $job->user->uuid)
                                    ->where('freelancer_uuid', $freelancer->uuid)
                                    ->first();
        $user = auth()->user();

        $message = $user->messages()->create([
            'conversation_id' => $conversation->id,
            'message' => json_encode([
                'job' => [
                    'id' => $job->id,
                    'status' => $request->status,
                    'title' => $job->title
                ],
                'type' => 'job'
            ])
        ]);
        
        event(new NewMessage($message));

        // Check if status is being updated to completed
        if ($request->status === 'completed') {
            // Get the accepted proposal
            $acceptedProposal = $job->proposals()->where('status', 'accepted')->first();
            
            if (!$acceptedProposal) {
                return response()->json([
                    'message' => 'No accepted proposal found for this job'
                ], 400);
            }

            try {
                // Get the employer and freelancer
                $employer = $job->user;
                $freelancer = $acceptedProposal->user;

                // Create or get wallets
                $employerWallet = $this->paymentService->createOrGetWallet($employer);
                $freelancerWallet = $this->paymentService->createOrGetWallet($freelancer);

                // Check if employer has sufficient balance
                if ($employerWallet->balance <= $acceptedProposal->price) {
                    return response()->json([
                        'message' => 'Insufficient funds in employer wallet'
                    ], 400);
                }

                // Create a transaction and transfer the funds
                DB::transaction(function () use ($employerWallet, $freelancerWallet, $acceptedProposal, $job) {
                    // Calculate platform fee (10%)
                    $platformFee = $acceptedProposal->price * 0.10;
                    $freelancerAmount = $acceptedProposal->price - $platformFee;

                    // Create transfer to freelancer
                    $transfer = $this->paymentService->getStripeClient()->transfers->create([
                        'amount' => (int)($freelancerAmount * 100),
                        'currency' => $employerWallet->currency,
                        'destination' => $freelancerWallet->stripe_connect_id,
                        'metadata' => [
                            'job_id' => $job->id,
                            'proposal_id' => $acceptedProposal->id
                        ]
                    ]);

                    // Record the transaction
                    Transaction::create([
                        'wallet_id' => $employerWallet->id,
                        'job_id' => $job->id,
                        'type' => Transaction::TYPE_ESCROW_RELEASE,
                        'amount' => $acceptedProposal->price,
                        'currency' => $employerWallet->currency,
                        'status' => Transaction::STATUS_COMPLETED,
                        'stripe_transfer_id' => $transfer->id,
                        'metadata' => [
                            'proposal_id' => $acceptedProposal->id,
                            'platform_fee' => $platformFee,
                            'freelancer_amount' => $freelancerAmount
                        ]
                    ]);

                    // Update wallets
                    $employerWallet->balance -= $acceptedProposal->price;
                    $employerWallet->save();

                    $freelancerWallet->balance += $freelancerAmount;
                    $freelancerWallet->save();
                });

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to process job completion payment',
                    'error' => $e->getMessage()
                ], 400);
            }
        }

        $job->update($data);

        return new StatusResource(true);
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
