<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $firebase =
            new FirebaseNotificationService();

        $payments = Payment::with([
            'tenant.user',
            'owner'
        ])->where('status', 'pending')->get();

        foreach ($payments as $payment) {
            if (!$payment->due_date) {
                continue;
            }

            $dueDate =
                Carbon::parse($payment->due_date);

            $daysLeft =
                $today->diffInDays(
                    $dueDate,
                    false
                );

            $tenantUser =
                $payment->tenant->user;

            if (!$tenantUser || !$tenantUser->fcm_token) {
                continue;
            }

            if ($daysLeft == 7) {
                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Pengingat Pembayaran',
                    'Tagihan pembayaran akan jatuh tempo dalam 7 hari.'
                );
            }

            if ($daysLeft == 3) {
                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Pengingat Pembayaran',
                    'Tagihan pembayaran akan jatuh tempo dalam 3 hari.'
                );
            }

            if ($daysLeft == 2) {
                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Pengingat Pembayaran',
                    'Tagihan pembayaran akan jatuh tempo dalam 2 hari.'
                );
            }

            if ($daysLeft == 1) {
                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Pengingat Pembayaran',
                    'Tagihan pembayaran akan jatuh tempo besok.'
                );
            }

            if ($daysLeft == 0) {
                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Jatuh Tempo Pembayaran',
                    'Tagihan pembayaran jatuh tempo hari ini.'
                );
            }

            if ($daysLeft < 0) {
                $owner = $payment->owner;

                if ($owner && $owner->fcm_token) {
                    $firebase->sendNotification(
                        $owner->fcm_token,
                        'Pembayaran Terlambat',
                        'Terdapat pembayaran penyewa yang telah melewati jatuh tempo.'
                    );
                }

                $firebase->sendNotification(
                    $tenantUser->fcm_token,
                    'Pembayaran Terlambat',
                    'Tagihan pembayaran Anda telah melewati jatuh tempo.'
                );
            }
        }

        $this->info('Payment reminders sent successfully.');
    }
}
