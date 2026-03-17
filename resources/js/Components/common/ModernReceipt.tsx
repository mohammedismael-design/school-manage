import React, { forwardRef } from 'react';
import QRCode from 'qrcode';

interface ReceiptItem {
    description: string;
    amount: number;
}

interface ModernReceiptProps {
    receiptNumber: string;
    date: string;
    studentName: string;
    studentClass: string;
    admissionNumber: string;
    schoolName: string;
    schoolAddress: string;
    schoolPhone: string;
    schoolLogo?: string;
    items: ReceiptItem[];
    totalAmount: number;
    paidAmount: number;
    balance: number;
    paymentMethod: string;
    transactionId?: string;
    mpesaReceipt?: string;
    qrCodeData?: string;
    onDownload?: () => void;
    onPrint?: () => void;
}

function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function numberToWords(amount: number): string {
    // Simple implementation for common amounts
    const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    function convertHundreds(num: number): string {
        if (num === 0) return '';
        if (num < 20) return ones[num];
        if (num < 100) return `${tens[Math.floor(num / 10)]}${num % 10 ? ' ' + ones[num % 10] : ''}`;
        return `${ones[Math.floor(num / 100)]} Hundred${num % 100 ? ' ' + convertHundreds(num % 100) : ''}`;
    }

    const intAmount = Math.floor(amount);
    if (intAmount === 0) return 'Zero KES Only';

    let result = '';
    if (intAmount >= 1000000) {
        result += `${convertHundreds(Math.floor(intAmount / 1000000))} Million `;
    }
    if (intAmount >= 1000) {
        result += `${convertHundreds(Math.floor((intAmount % 1000000) / 1000))} Thousand `;
    }
    result += convertHundreds(intAmount % 1000);

    return `${result.trim()} KES Only`;
}

export const ModernReceipt = forwardRef<HTMLDivElement, ModernReceiptProps>(
    (
        {
            receiptNumber,
            date,
            studentName,
            studentClass,
            admissionNumber,
            schoolName,
            schoolAddress,
            schoolPhone,
            schoolLogo,
            items,
            totalAmount,
            paidAmount,
            balance,
            paymentMethod,
            transactionId,
            mpesaReceipt,
            qrCodeData,
            onDownload,
            onPrint,
        },
        ref,
    ) => {
        return (
            <div ref={ref} className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-lg">
                {/* Header */}
                <div className="mb-6 text-center">
                    {schoolLogo && (
                        <img src={schoolLogo} alt={schoolName} className="mx-auto mb-3 h-16 w-16 rounded-full object-cover" />
                    )}
                    <h2 className="text-xl font-bold text-gray-900">{schoolName}</h2>
                    <p className="text-sm text-gray-500">{schoolAddress}</p>
                    <p className="text-sm text-gray-500">{schoolPhone}</p>
                    <div className="mt-3 border-t border-b border-gray-200 py-2">
                        <h3 className="text-lg font-semibold tracking-wider text-gray-800">PAYMENT RECEIPT</h3>
                    </div>
                </div>

                {/* Receipt Details */}
                <div className="mb-4 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span className="text-gray-500">Receipt No:</span>
                        <p className="font-semibold text-gray-900">{receiptNumber}</p>
                    </div>
                    <div className="text-right">
                        <span className="text-gray-500">Date:</span>
                        <p className="font-semibold text-gray-900">{date}</p>
                    </div>
                </div>

                {/* Student Info */}
                <div className="mb-4 rounded-lg bg-gray-50 p-3 text-sm">
                    <div className="grid grid-cols-3 gap-2">
                        <div>
                            <span className="text-gray-500">Student</span>
                            <p className="font-semibold text-gray-900">{studentName}</p>
                        </div>
                        <div className="text-center">
                            <span className="text-gray-500">Class</span>
                            <p className="font-semibold text-gray-900">{studentClass}</p>
                        </div>
                        <div className="text-right">
                            <span className="text-gray-500">Adm No</span>
                            <p className="font-semibold text-gray-900">{admissionNumber}</p>
                        </div>
                    </div>
                </div>

                {/* Items */}
                <div className="mb-4">
                    <div className="mb-2 flex justify-between border-b border-gray-200 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <span>Description</span>
                        <span>Amount</span>
                    </div>
                    {items.map((item, index) => (
                        <div key={index} className="flex justify-between py-1 text-sm">
                            <span className="text-gray-700">{item.description}</span>
                            <span className="font-medium text-gray-900">{formatCurrency(item.amount)}</span>
                        </div>
                    ))}
                </div>

                {/* Totals */}
                <div className="border-t border-gray-200 pt-3 text-sm">
                    <div className="flex justify-between py-1">
                        <span className="font-medium text-gray-700">Total</span>
                        <span className="font-bold text-gray-900">{formatCurrency(totalAmount)}</span>
                    </div>
                    <div className="flex justify-between py-1">
                        <span className="font-medium text-gray-700">Paid</span>
                        <span className="font-bold text-emerald-600">({formatCurrency(paidAmount)})</span>
                    </div>
                    <div className="flex justify-between border-t border-gray-200 py-1">
                        <span className="font-semibold text-gray-900">Balance</span>
                        <span className={`font-bold ${balance === 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                            {formatCurrency(balance)}
                        </span>
                    </div>
                </div>

                {/* Amount in Words */}
                <p className="mt-3 rounded bg-gray-50 p-2 text-xs italic text-gray-600">
                    {numberToWords(paidAmount)}
                </p>

                {/* Payment Method */}
                <div className="mt-4 text-sm">
                    <div className="flex justify-between">
                        <span className="text-gray-500">Payment Method:</span>
                        <span className="font-medium capitalize text-gray-900">{paymentMethod}</span>
                    </div>
                    {transactionId && (
                        <div className="flex justify-between">
                            <span className="text-gray-500">Transaction ID:</span>
                            <span className="font-mono font-medium text-gray-900">{transactionId}</span>
                        </div>
                    )}
                    {mpesaReceipt && (
                        <div className="flex justify-between">
                            <span className="text-gray-500">M-Pesa Receipt:</span>
                            <span className="font-mono font-medium text-gray-900">{mpesaReceipt}</span>
                        </div>
                    )}
                </div>

                {/* QR Code */}
                {qrCodeData && (
                    <div className="mt-4 text-center">
                        <img src={qrCodeData} alt="Receipt QR Code" className="mx-auto h-24 w-24" />
                        <p className="mt-1 text-xs text-gray-400">Scan to verify</p>
                    </div>
                )}

                {/* Actions */}
                {(onDownload ?? onPrint) && (
                    <div className="mt-6 flex gap-3 print:hidden">
                        {onDownload && (
                            <button
                                type="button"
                                onClick={onDownload}
                                className="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Download PDF
                            </button>
                        )}
                        {onPrint && (
                            <button
                                type="button"
                                onClick={onPrint}
                                className="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                Print
                            </button>
                        )}
                    </div>
                )}
            </div>
        );
    },
);

ModernReceipt.displayName = 'ModernReceipt';
