<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUuidsForUsers extends Command
{
    protected $signature = 'users:generate-uuids';
    protected $description = 'Generate UUIDs for users who don\'t have one';

    public function handle()
    {
        $users = User::whereNull('uuid')->get();

        $bar = $this->output->createProgressBar(count($users));

        $users->each(function ($user) use ($bar) {
            $user->uuid = Str::uuid();
            $user->save();
            $bar->advance();
        });

        $bar->finish();

        $this->info("\nUUIDs generated successfully.");
    }
}