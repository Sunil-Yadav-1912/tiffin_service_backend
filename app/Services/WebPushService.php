<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    public static function getPublicKey(): string
    {
        return env('VAPID_PUBLIC_KEY', 'BJOctl9JH59NyRXgftaFSTpxPSCPhKMpH1PVE-CUkU0Jbf3r3qyKbOR_DE9735r4_fDuk6zmWf-6iS3ipiLsQlQ');
    }

    public static function getPrivateKey(): string
    {
        return env('VAPID_PRIVATE_KEY', 'W7s-_NsVzo6VW4jLW98muw5cYUcfSj5yYHAJmzkGrEs');
    }

    public static function getSubject(): string
    {
        return env('VAPID_SUBJECT', 'mailto:admin@crownoragroup.co.in');
    }

    /**
     * Send push notification to all admin registered devices.
     */
    public static function notifyAdmins(string $title, string $message, array $extraData = []): void
    {
        if (!class_exists('Minishlink\WebPush\WebPush')) {
            Log::warning('WebPush package is not installed. Please run "composer install" on server.');
            return;
        }

        $subscriptions = PushSubscription::all();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $auth = [
            'VAPID' => [
                'subject' => self::getSubject(),
                'publicKey' => self::getPublicKey(),
                'privateKey' => self::getPrivateKey(),
            ],
        ];

        try {
            $webPush = new WebPush($auth);
            $webPush->setDefaultOptions([
                'TTL' => 86400, // 1 day
                'urgency' => 'high',
            ]);

            $payload = json_encode([
                'title' => $title,
                'message' => $message,
                'data' => array_merge([
                    'url' => '/admin/dashboard',
                    'timestamp' => time(),
                ], $extraData),
            ]);

            foreach ($subscriptions as $sub) {
                if (empty($sub->endpoint))
                    continue;

                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if (!$report->isSuccess()) {
                    // If the subscription is expired or unsubscribed, delete it from DB
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('WebPush error: ' . $e->getMessage());
        }
    }
}

