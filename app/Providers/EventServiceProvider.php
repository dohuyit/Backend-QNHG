<?php

namespace App\Providers;

use App\Events\Reservations\ReservationCreated;
use App\Events\Reservations\ReservationStatusUpdated;
use App\Listeners\Reservations\SendReservationNotification;
use App\Listeners\Reservations\UpdateReservationCounters;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\Orders\OrderCreated;
use App\Listeners\Orders\SendOrderNotification;
use App\Events\Orders\OrderUpdated;
use App\Listeners\Orders\SendOrderUpdatedNotification;
use App\Events\Orders\OrderItemUpdated;
use App\Listeners\Orders\SendOrderItemUpdatedNotification;
use App\Events\Orders\OrderItemCreated;
use App\Listeners\Orders\SendOrderItemCreatedNotification;
use App\Events\Orders\OrderItemDeleted;
use App\Listeners\Orders\SendOrderItemDeletedNotification;
use App\Events\Tables\TableStatusUpdated;
use App\Listeners\Tables\SendTableStatusUpdatedNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Đăng ký events cho đơn đặt bàn
        ReservationCreated::class => [
            SendReservationNotification::class,
            UpdateReservationCounters::class,
        ],

        ReservationStatusUpdated::class => [
            UpdateReservationCounters::class,
        ],

        // Đăng ký events cho đơn hàng
        OrderCreated::class => [
            SendOrderNotification::class,
        ],
        OrderUpdated::class => [
            SendOrderUpdatedNotification::class,
        ],
        OrderItemUpdated::class => [
            SendOrderItemUpdatedNotification::class,
        ],
        OrderItemCreated::class => [
            SendOrderItemCreatedNotification::class,
        ],
        OrderItemDeleted::class => [
            SendOrderItemDeletedNotification::class,
        ],
        TableStatusUpdated::class => [
            SendTableStatusUpdatedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
