<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(): Response
    {
        $tenant = auth()->user()->tenant;
        $tenant->load('plan');

        $usage = $this->subscriptionService->getUsageStats($tenant);

        return Inertia::render('School/Subscription/Index', [
            'tenant' => $tenant,
            'plan' => $tenant->plan,
            'usage' => [
                'students' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'students'),
                'staff' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'staff'),
                'storage' => $this->subscriptionService->checkSubscriptionLimits($tenant, 'storage'),
            ],
            'payments' => SubscriptionPayment::where('tenant_id', $tenant->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function upgrade(): Response
    {
        $tenant = auth()->user()->tenant;
        $plans = $this->subscriptionService->getAvailablePlans();
        $addons = $this->subscriptionService->getAddonModules();

        return Inertia::render('School/Subscription/Upgrade', [
            'currentPlan' => $tenant->plan,
            'plans' => $plans,
            'addons' => $addons,
            'currentAddons' => $tenant->addon_modules ?? [],
        ]);
    }

    public function previewUpgrade(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'addons' => 'nullable|array',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $pricing = $this->subscriptionService->calculatePrice(
            $request->plan_id,
            $request->addons ?? [],
            $request->billing_cycle
        );

        return response()->json($pricing);
    }

    public function processPayment(Request $request): RedirectResponse|Response
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'addons' => 'nullable|array',
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method' => 'required|in:mpesa,card,bank',
            'phone' => 'required_if:payment_method,mpesa|nullable|string',
        ]);

        $pricing = $this->subscriptionService->calculatePrice(
            $validated['plan_id'],
            $validated['addons'] ?? [],
            $validated['billing_cycle']
        );

        $invoiceNumber = SubscriptionPayment::generateInvoiceNumber();

        $payment = SubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $validated['plan_id'],
            'amount' => $pricing['total'],
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
            'invoice_number' => $invoiceNumber,
            'metadata' => [
                'billing_cycle' => $validated['billing_cycle'],
                'addons' => $validated['addons'] ?? [],
                'pricing_breakdown' => $pricing,
            ],
        ]);

        $result = match ($validated['payment_method']) {
            'mpesa' => $this->paymentService->processMpesaPayment(
                $validated['phone'],
                $pricing['total'],
                $invoiceNumber
            ),
            'card' => $this->paymentService->processCardPayment([], $pricing['total']),
            'bank' => $this->paymentService->processBankTransfer($invoiceNumber, $pricing['total']),
            default => ['success' => false, 'error' => 'Invalid payment method'],
        };

        if ($result['success'] && $validated['payment_method'] === 'bank') {
            return Inertia::render('School/Subscription/BankTransfer', [
                'bankDetails' => $result['bank_details'],
                'invoiceNumber' => $invoiceNumber,
                'amount' => $pricing['total'],
            ]);
        }

        if ($result['success']) {
            return redirect()->route('school.subscription.index')
                ->with('success', 'Payment initiated. Your subscription will be activated upon confirmation.');
        }

        return back()->with('error', $result['error'] ?? 'Payment failed. Please try again.');
    }

    public function invoices(): Response
    {
        $tenant = auth()->user()->tenant;

        $payments = SubscriptionPayment::where('tenant_id', $tenant->id)
            ->with('plan')
            ->latest()
            ->paginate(10);

        return Inertia::render('School/Subscription/Invoices', [
            'payments' => $payments,
        ]);
    }

    public function downloadInvoice(SubscriptionPayment $payment): \Symfony\Component\HttpFoundation\Response
    {
        abort_if($payment->tenant_id !== auth()->user()->tenant_id, 403);

        $payment->load(['tenant', 'plan']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'payment' => $payment,
        ]);

        return $pdf->download("invoice-{$payment->invoice_number}.pdf");
    }
}
