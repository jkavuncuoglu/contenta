<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Jobs;

use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Services\OAuthServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to refresh expiring social account tokens.
 *
 * Runs hourly via Laravel scheduler.
 * Finds all accounts with tokens expiring within 1 hour and refreshes them.
 * Prevents token expiration and maintains platform access.
 */
class RefreshSocialAccountTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(OAuthServiceContract $oauth): void
    {
        Log::info('RefreshSocialAccountTokens job started');

        try {
            // Find accounts with tokens expiring within 1 hour
            $accounts = SocialAccount::where('is_active', true)
                ->whereNotNull('token_expires_at')
                ->where('token_expires_at', '<=', now()->addHour())
                ->where('token_expires_at', '>', now()) // Not yet expired
                ->get();

            $refreshedCount = 0;
            $failedCount = 0;

            foreach ($accounts as $account) {
                try {
                    Log::info('Refreshing token for account', [
                        'account_id' => $account->id,
                        'platform' => $account->platform,
                        'expires_at' => $account->token_expires_at,
                    ]);

                    $oauth->refreshToken($account);
                    $refreshedCount++;

                    Log::info('Token refreshed successfully', [
                        'account_id' => $account->id,
                        'platform' => $account->platform,
                    ]);
                } catch (\Exception $e) {
                    $failedCount++;

                    Log::error('Failed to refresh token', [
                        'account_id' => $account->id,
                        'platform' => $account->platform,
                        'error' => $e->getMessage(),
                    ]);

                    // Mark account as potentially having issues
                    // Don't deactivate automatically - user may need to reauthorize
                }
            }

            Log::info('RefreshSocialAccountTokens job completed', [
                'total_accounts' => $accounts->count(),
                'refreshed' => $refreshedCount,
                'failed' => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('RefreshSocialAccountTokens job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RefreshSocialAccountTokens job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
