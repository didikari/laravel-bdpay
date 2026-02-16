<?php

namespace BDPay\LaravelBDPay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use BDPay\LaravelBDPay\Facades\BDPay;
use BDPay\LaravelBDPay\Exceptions\InvalidSignatureException;

class VerifyBDPaySignature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('bdpay.webhook.verify_signature', true)) {
            return $next($request);
        }

        try {
            $signature = $request->header('X-BDPay-Signature');
            
            if (!$signature) {
                \Log::warning('BDPay webhook received without signature header', [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'headers' => $request->headers->all(),
                ]);
                throw new InvalidSignatureException('Missing BDPay signature header');
            }

            $payload = $request->all();
            
            if (!BDPay::verifySignature($signature, $payload)) {
                \Log::warning('BDPay webhook signature verification failed', [
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'signature' => $signature,
                    'payload' => $payload,
                ]);
                throw new InvalidSignatureException('Invalid BDPay signature');
            }

            \Log::info('BDPay webhook signature verified successfully', [
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            return $next($request);

        } catch (InvalidSignatureException $e) {
            \Log::error('BDPay webhook signature verification error', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Webhook signature verification failed',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            \Log::error('BDPay webhook middleware error', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error',
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
