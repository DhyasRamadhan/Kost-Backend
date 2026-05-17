<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RentalContract;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class DetectPotentialVacancies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacancies:detect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect potential vacant rooms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $firebase = new FirebaseNotificationService();

        $contracts = RentalContract::with([
            'payments',
            'room',
            'owner'
        ])->where('status', 'active')->get();

        foreach ($contracts as $contract) {
            $daysLeft =
                $today->diffInDays(
                    Carbon::parse(
                        $contract->end_date
                    ),
                    false
                );

            if ($daysLeft > 7) {
                continue;
            }

            $hasOverduePayment =
                $contract->payments
                    ->where('status', 'pending')
                    ->where('due_date', '<', $today)
                    ->isNotEmpty();

            if (!$hasOverduePayment) {
                continue;
            }

            $owner = $contract->owner;

            if (!$owner || !$owner->fcm_token) {
                continue;
            }

            $firebase->sendNotification(
                $owner->fcm_token,
                'Potensi Kamar Kosong',
                'Kamar ' .
                $contract->room->room_number .
                ' terdeteksi berpotensi kosong.'
            );
        }

        $this->info(
            'Potential vacancy detection completed.'
        );
    }
}
