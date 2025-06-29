<?php

// app/Http/Controllers/Webhooks/StripeController.php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('stripe-signature');

        if (! $signature) {
            Log::warning('Stripe webhook received without signature');

            return response('Missing signature', 400);
        }

        // Verify webhook signature
        if (! $this->paymentService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Stripe webhook signature verification failed');

            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (! $event) {
            Log::error('Invalid JSON in Stripe webhook');

            return response('Invalid JSON', 400);
        }

        try {
            $this->paymentService->handleWebhook($event);

            Log::info('Stripe webhook processed successfully', [
                'event_id' => $event['id'],
                'event_type' => $event['type'],
            ]);

            return response('Webhook handled', 200);

        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'event_id' => $event['id'] ?? 'unknown',
                'event_type' => $event['type'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return response('Webhook processing failed', 500);
        }
    }
}
