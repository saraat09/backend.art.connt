<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentController extends Controller
{
    /**
     * POST /api/payments/create-intent
     * Crée un PaymentIntent Stripe et retourne le client_secret
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'amount'      => 'required|integer|min:1', // en MAD ; sera converti en centimes pour Stripe
            'artisan_id'  => 'required|exists:users,id',
            'description' => 'nullable|string|max:255',
        ]);

        $stripeSecret = config('services.stripe.secret');

        if (!$stripeSecret || str_contains($stripeSecret, 'VOTRE_CLE')) {
            return response()->json([
                'error' => 'Stripe n\'est pas configuré. Veuillez ajouter votre clé STRIPE_SECRET dans le fichier .env.',
            ], 503);
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);

            $user = JWTAuth::user();

            $intent = \Stripe\PaymentIntent::create([
                'amount'   => $request->amount * 100,
                'currency' => 'mad',
                'metadata' => [
                    'client_id'   => $user->id,
                    'client_name' => $user->name,
                    'artisan_id'  => $request->artisan_id,
                    'description' => $request->description ?? 'Acompte artisan',
                ],
                'description' => $request->description ?? 'Acompte artisan — ArtisanConnect',
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_id'    => $intent->id,
                'amount'        => $request->amount,
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du paiement.'], 500);
        }
    }

    /**
     * POST /api/payments/webhook
     * Webhook Stripe (pour les confirmations de paiement)
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            if ($webhookSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                $event = \Stripe\Event::constructFrom(json_decode($payload, true));
            }

            if ($event->type === 'payment_intent.succeeded') {
                // Ici vous pouvez enregistrer le paiement en BDD si nécessaire
                \Log::info('Paiement réussi', ['id' => $event->data->object->id]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['received' => true]);
    }
}
