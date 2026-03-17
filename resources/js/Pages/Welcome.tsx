import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome to Feeyangu" />
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col items-center justify-center p-8">
                <div className="max-w-2xl text-center">
                    <h1 className="text-5xl font-bold text-blue-800 mb-4">
                        🏫 Feeyangu
                    </h1>
                    <p className="text-xl text-gray-600 mb-2">
                        School Management System
                    </p>
                    <p className="text-gray-500 mb-8">
                        Multi-tenant SaaS platform for Kenyan schools (CBC, 8-4-4, Cambridge)
                    </p>

                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            href="/login"
                            className="btn-primary px-8 py-3 text-base rounded-xl"
                        >
                            Login
                        </Link>
                        <Link
                            href="/admin/dashboard"
                            className="btn-secondary px-8 py-3 text-base rounded-xl"
                        >
                            Admin Portal
                        </Link>
                    </div>

                    <div className="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                        {[
                            { icon: '🏫', label: 'Multi-School', desc: 'Multi-tenant architecture' },
                            { icon: '📚', label: 'CBC & 8-4-4', desc: 'Kenyan curriculum support' },
                            { icon: '💳', label: 'M-Pesa', desc: 'Local payment methods' },
                            { icon: '📊', label: 'Analytics', desc: 'Real-time insights' },
                        ].map((item) => (
                            <div key={item.label} className="card p-4">
                                <div className="text-3xl mb-2">{item.icon}</div>
                                <div className="font-semibold text-gray-800 text-sm">{item.label}</div>
                                <div className="text-gray-500 text-xs mt-1">{item.desc}</div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
