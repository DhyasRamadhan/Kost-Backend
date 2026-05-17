<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RentalContract;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class SendContractReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send contract expiration reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $firebase =
            new FirebaseNotificationService();

        $contracts = RentalContract::with([
            'owner',
            'room'
        ])->where('status', 'active')->get();

        foreach ($contracts as $contract) {

            if (!$contract->end_date) {
                continue;
            }

            $endDate =
                Carbon::parse(
                    $contract->end_date
                );

            $daysLeft =
                $today->diffInDays(
                    $endDate,
                    false
                );

            $owner = $contract->owner;

            if (!$owner || !$owner->fcm_token) {
                continue;
            }

            if ($daysLeft == 7) {
                $firebase->sendNotification(
                    $owner->fcm_token,
                    'Kontrak Akan Berakhir',
                    'Kontrak sewa kamar akan berakhir dalam 7 hari.'
                );
            }

            if ($daysLeft == 3) {
                $firebase->sendNotification(
                    $owner->fcm_token,
                    'Kontrak Akan Berakhir',
                    'Kontrak sewa kamar akan berakhir dalam 3 hari.'
                );
            }

            if ($daysLeft == 1) {
                $firebase->sendNotification(
                    $owner->fcm_token,
                    'Kontrak Akan Berakhir',
                    'Kontrak sewa kamar akan berakhir besok.'
                );
            }

            if ($daysLeft == 0) {
                $firebase->sendNotification(
                    $owner->fcm_token,
                    'Kontrak Berakhir Hari Ini',
                    'Kontrak sewa kamar berakhir hari ini.'
                );
            }
        }

        $this->info(
            'Contract reminders sent successfully.'
        );
    }
}
