import React from 'react';
import { Head, useForm } from '@inertiajs/react';

interface LoginProps {
    errors?: Record<string, string>;
}

export default function Login({ errors }: LoginProps) {
    const { data, setData, post, processing } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Login" />
            <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
                <div className="w-full max-w-md">
                    <div className="text-center mb-8">
                        <h1 className="text-3xl font-bold text-blue-800">🏫 Feeyangu</h1>
                        <p className="text-gray-500 mt-1">Sign in to your account</p>
                    </div>

                    <div className="card p-8">
                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label className="label" htmlFor="email">
                                    Email Address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="input"
                                    placeholder="you@school.ac.ke"
                                    autoFocus
                                    required
                                />
                                {errors?.email && (
                                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label className="label" htmlFor="password">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="input"
                                    placeholder="••••••••"
                                    required
                                />
                                {errors?.password && (
                                    <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-between">
                                <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        className="rounded border-gray-300"
                                    />
                                    Remember me
                                </label>
                                <a href="/forgot-password" className="text-sm text-blue-600 hover:underline">
                                    Forgot password?
                                </a>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="btn-primary w-full py-3"
                            >
                                {processing ? 'Signing in...' : 'Sign In'}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
