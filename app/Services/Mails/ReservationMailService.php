<?php

namespace App\Services\Mails;

use App\Mail\ReservationConfirmationMail;
use Illuminate\Support\Facades\Mail;

class ReservationMailService
{
    public function sendClientConfirmMail(array $reservation)
    {
        $reservationEmail = $reservation['customer_email'] ?? null;
        if ($reservationEmail) {
            $emailContent = new ReservationConfirmationMail($reservation);
            Mail::to($reservationEmail)->send($emailContent);
        }
    }
}