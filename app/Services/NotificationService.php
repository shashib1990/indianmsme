<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Send notification to Admin(s)
     */
    public static function notifyAdmin(string $title, string $message, ?string $link = null): Notification
    {
        return Notification::create([
            'recipient_type' => 'admin',
            'recipient_id'   => null,
            'title'          => Str::limit($title, 245, '...'),
            'message'        => $message,
            'link'           => $link,
            'is_read'        => false,
        ]);
    }

    /**
     * Send notification to a specific Seller
     */
    public static function notifySeller($sellerId, string $title, string $message, ?string $link = null): ?Notification
    {
        if (!$sellerId) return null;
        return Notification::create([
            'recipient_type' => 'seller',
            'recipient_id'   => (int) $sellerId,
            'title'          => Str::limit($title, 245, '...'),
            'message'        => $message,
            'link'           => $link,
            'is_read'        => false,
        ]);
    }

    /**
     * Send notification to a specific DDC
     */
    public static function notifyDdc($ddcId, string $title, string $message, ?string $link = null): ?Notification
    {
        if (!$ddcId) return null;
        return Notification::create([
            'recipient_type' => 'ddc',
            'recipient_id'   => (int) $ddcId,
            'title'          => Str::limit($title, 245, '...'),
            'message'        => $message,
            'link'           => $link,
            'is_read'        => false,
        ]);
    }

    /**
     * Send notification to a specific ADC
     */
    public static function notifyAdc($adcId, string $title, string $message, ?string $link = null): ?Notification
    {
        if (!$adcId) return null;
        return Notification::create([
            'recipient_type' => 'adc',
            'recipient_id'   => (int) $adcId,
            'title'          => Str::limit($title, 245, '...'),
            'message'        => $message,
            'link'           => $link,
            'is_read'        => false,
        ]);
    }

    /**
     * Send notification to an Authorized Pickup Store
     */
    public static function notifyPickupStore($storeId, string $title, string $message, ?string $link = null): ?Notification
    {
        if (!$storeId) return null;
        return Notification::create([
            'recipient_type' => 'pickup_store',
            'recipient_id'   => (int) $storeId,
            'title'          => Str::limit($title, 245, '...'),
            'message'        => $message,
            'link'           => $link,
            'is_read'        => false,
        ]);
    }
}
