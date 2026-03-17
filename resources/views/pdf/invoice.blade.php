<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $payment->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: bold; color: #1e40af; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .total-row td { font-weight: bold; background: #f9fafb; }
        .footer { margin-top: 30px; text-align: center; color: #9ca3af; font-size: 10px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 999px; font-size: 10px; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        @if($payment->tenant->logo)
            <img src="{{ Storage::disk('public')->url($payment->tenant->logo) }}" alt="Logo" style="height:60px; margin-bottom:10px;">
        @endif
        <div class="school-name">{{ $payment->tenant->name }}</div>
        <div>{{ $payment->tenant->address }}</div>
        <div>{{ $payment->tenant->phone }}</div>
        <h2 style="margin-top:15px; font-size:16px; color:#374151;">SUBSCRIPTION INVOICE</h2>
    </div>

    <table>
        <tr>
            <td><strong>Invoice No:</strong> {{ $payment->invoice_number }}</td>
            <td><strong>Date:</strong> {{ $payment->created_at->format('d M Y') }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong>
                <span class="badge {{ $payment->isCompleted() ? 'badge-green' : 'badge-red' }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </td>
            <td><strong>Payment Method:</strong> {{ strtoupper($payment->payment_method) }}</td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $payment->plan->name ?? 'Subscription' }} Plan<br>
                    <small style="color:#6b7280;">
                        Billing Cycle: {{ ucfirst($payment->metadata['billing_cycle'] ?? 'monthly') }}
                        @if(!empty($payment->metadata['addons']))
                            | Add-ons: {{ implode(', ', $payment->metadata['addons']) }}
                        @endif
                    </small>
                </td>
                <td style="text-align:right;">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Total Amount</td>
                <td style="text-align:right;">KES {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($payment->transaction_id)
    <br>
    <table>
        <tr>
            <td><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</td>
        </tr>
        @if($payment->paid_at)
        <tr>
            <td><strong>Paid At:</strong> {{ $payment->paid_at->format('d M Y H:i') }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="footer">
        <p>Feeyangu School Management System | feeyangu.com</p>
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
