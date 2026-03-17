<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processMpesaPayment(string $phone, float $amount, string $reference): array
    {
        try {
            $accessToken = $this->getMpesaAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortcode = config('services.mpesa.shortcode');
            $passkey = config('services.mpesa.passkey');
            $password = base64_encode($shortcode . $passkey . $timestamp);
            $callbackUrl = config('services.mpesa.callback_url');

            $response = Http::withToken($accessToken)
                ->post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => (int) $amount,
                    'PartyA' => $this->formatPhone($phone),
                    'PartyB' => $shortcode,
                    'PhoneNumber' => $this->formatPhone($phone),
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $reference,
                    'TransactionDesc' => "Feeyangu subscription payment for {$reference}",
                ]);

            return [
                'success' => $response->successful(),
                'checkout_request_id' => $response->json('CheckoutRequestID'),
                'merchant_request_id' => $response->json('MerchantRequestID'),
                'response_code' => $response->json('ResponseCode'),
                'response_description' => $response->json('ResponseDescription'),
                'customer_message' => $response->json('CustomerMessage'),
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa payment error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Payment processing failed. Please try again.',
            ];
        }
    }

    public function verifyMpesaPayment(string $checkoutRequestId): array
    {
        try {
            $accessToken = $this->getMpesaAccessToken();
            $timestamp = now()->format('YmdHis');
            $shortcode = config('services.mpesa.shortcode');
            $passkey = config('services.mpesa.passkey');
            $password = base64_encode($shortcode . $passkey . $timestamp);

            $response = Http::withToken($accessToken)
                ->post('https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query', [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'CheckoutRequestID' => $checkoutRequestId,
                ]);

            $resultCode = $response->json('ResultCode');

            return [
                'success' => $resultCode === '0',
                'result_code' => $resultCode,
                'result_description' => $response->json('ResultDesc'),
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa verification error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Payment verification failed.',
            ];
        }
    }

    public function processCardPayment(array $cardDetails, float $amount): array
    {
        // Placeholder for card payment integration (Stripe, Flutterwave, etc.)
        return [
            'success' => false,
            'error' => 'Card payment not yet configured.',
        ];
    }

    public function processBankTransfer(string $reference, float $amount): array
    {
        return [
            'success' => true,
            'message' => 'Bank transfer initiated. Your subscription will be activated upon payment confirmation.',
            'bank_details' => [
                'bank_name' => config('services.bank.name', 'KCB Bank Kenya'),
                'account_name' => config('services.bank.account_name', 'Feeyangu Limited'),
                'account_number' => config('services.bank.account_number', ''),
                'branch' => config('services.bank.branch', 'Nairobi'),
                'reference' => $reference,
            ],
        ];
    }

    public function generateInvoice(\App\Models\SubscriptionPayment $payment): string
    {
        return $payment->invoice_number ?? \App\Models\SubscriptionPayment::generateInvoiceNumber();
    }

    private function getMpesaAccessToken(): string
    {
        $consumerKey = config('services.mpesa.consumer_key');
        $consumerSecret = config('services.mpesa.consumer_secret');

        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get('https://sandbox.safaricom.co.ke/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json('access_token');
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
