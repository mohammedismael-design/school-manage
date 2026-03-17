import { useCallback, useEffect, useRef } from 'react';
import toast from 'react-hot-toast';
import { usePage } from '@inertiajs/react';

interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

interface PageProps {
    flash?: FlashMessages;
    [key: string]: unknown;
}

export function useFlash() {
    const { flash } = usePage<PageProps>().props;
    const shownRef = useRef<Set<string>>(new Set());

    useEffect(() => {
        if (!flash) return;

        if (flash.success) {
            const key = `success:${flash.success}`;
            if (!shownRef.current.has(key)) {
                shownRef.current.add(key);
                toast.success(flash.success, { duration: 5000 });
            }
        }
        if (flash.error) {
            const key = `error:${flash.error}`;
            if (!shownRef.current.has(key)) {
                shownRef.current.add(key);
                toast.error(flash.error, { duration: 5000 });
            }
        }
        if (flash.warning) {
            const key = `warning:${flash.warning}`;
            if (!shownRef.current.has(key)) {
                shownRef.current.add(key);
                toast(flash.warning, { icon: '⚠️', duration: 5000 });
            }
        }
        if (flash.info) {
            const key = `info:${flash.info}`;
            if (!shownRef.current.has(key)) {
                shownRef.current.add(key);
                toast(flash.info, { icon: 'ℹ️', duration: 5000 });
            }
        }
    }, [flash]);

    const success = useCallback((message: string) => toast.success(message, { duration: 5000 }), []);
    const error = useCallback((message: string) => toast.error(message, { duration: 5000 }), []);
    const warning = useCallback((message: string) => toast(message, { icon: '⚠️', duration: 5000 }), []);
    const info = useCallback((message: string) => toast(message, { icon: 'ℹ️', duration: 5000 }), []);

    return { success, error, warning, info };
}
