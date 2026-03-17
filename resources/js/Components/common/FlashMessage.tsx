import React from 'react';
import { Toaster } from 'react-hot-toast';
import { useFlash } from '@/hooks/useFlash';

/**
 * FlashMessage component — renders a Toaster and auto-shows
 * Laravel flash messages (success/error/warning/info) passed via Inertia props.
 */
export function FlashMessage() {
    // Side-effect: auto-displays flash messages from page props
    useFlash();

    return (
        <Toaster
            position="top-right"
            toastOptions={{
                duration: 5000,
                style: {
                    background: '#fff',
                    color: '#374151',
                    borderRadius: '0.5rem',
                    boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)',
                    padding: '0.75rem 1rem',
                    fontSize: '0.875rem',
                    maxWidth: '400px',
                },
                success: {
                    iconTheme: { primary: '#10b981', secondary: '#fff' },
                },
                error: {
                    iconTheme: { primary: '#ef4444', secondary: '#fff' },
                },
            }}
        />
    );
}
