<?php

namespace BDPay\LaravelBDPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use BDPay\LaravelBDPay\Models\BDPayTransaction;
use BDPay\LaravelBDPay\Exceptions\BDPayException;

class WebhookController
{
    /**
     * Handle payment callback webhook.
     */
    public function paymentCallback(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('BDPay Payment Callback Received', $data);
            
            $transaction = $this->findOrCreateTransaction($data, 'payment');
            $this->updateTransactionStatus($transaction, $data);
            
            return response()->json(['status' => 'success']);
            
        } catch (BDPayException $e) {
            Log::error('BDPay Payment Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle disbursement callback webhook.
     */
    public function disbursementCallback(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            Log::info('BDPay Disbursement Callback Received', $data);
            
            $transaction = $this->findOrCreateTransaction($data, 'disbursement');
            $this->updateTransactionStatus($transaction, $data);
            
            return response()->json(['status' => 'success']);
            
        } catch (BDPayException $e) {
            Log::error('BDPay Disbursement Callback Error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Find or create transaction record.
     */
    protected function findOrCreateTransaction(array $data, string $type): BDPayTransaction
    {
        $orderId = $data['order_id'] ?? null;
        
        if (!$orderId) {
            throw new BDPayException('Missing order_id in webhook data');
        }

        $transaction = BDPayTransaction::where('order_id', $orderId)
            ->where('type', $type)
            ->first();

        if (!$transaction) {
            $transaction = BDPayTransaction::create([
                'order_id' => $orderId,
                'transaction_id' => $data['transaction_id'] ?? null,
                'type' => $type,
                'status' => 'pending',
                'amount' => $this->parseAmount($data['amount'] ?? 0),
                'currency' => $data['currency'] ?? 'IDR',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'recipient_name' => $data['recipient_name'] ?? null,
                'recipient_account' => $data['recipient_account'] ?? null,
                'bank_code' => $data['bank_code'] ?? null,
                'description' => $data['description'] ?? null,
                'request_data' => $data,
                'response_data' => $data,
            ]);
        }

        return $transaction;
    }

    /**
     * Update transaction status based on webhook data.
     */
    protected function updateTransactionStatus(BDPayTransaction $transaction, array $data): void
    {
        $status = $this->mapWebhookStatus($data['status'] ?? 'pending');
        
        $updateData = [
            'status' => $status,
            'response_data' => $data,
        ];

        if ($status === 'success' && !$transaction->paid_at) {
            $updateData['paid_at'] = now();
        }

        if (isset($data['va_number'])) {
            $updateData['va_number'] = $data['va_number'];
        }

        if (isset($data['payment_link'])) {
            $updateData['payment_link'] = $data['payment_link'];
        }

        $transaction->update($updateData);
    }

    /**
     * Map webhook status to internal status.
     */
    protected function mapWebhookStatus(string $webhookStatus): string
    {
        return match (strtolower($webhookStatus)) {
            'success', 'paid', 'completed' => 'success',
            'pending', 'processing' => 'pending',
            'failed', 'error', 'rejected' => 'failed',
            'expired', 'timeout' => 'expired',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Parse amount from webhook data.
     */
    protected function parseAmount($amount): float
    {
        if (is_numeric($amount)) {
            return (float) $amount;
        }

        return 0.0;
    }
}
