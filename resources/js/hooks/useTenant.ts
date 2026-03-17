import { usePage } from '@inertiajs/react';

interface Tenant {
    id: number;
    name: string;
    slug: string;
    logo: string | null;
    favicon: string | null;
    colors: Record<string, string> | null;
    status: string;
    subscription_status: string;
    settings: Record<string, unknown> | null;
    features: Record<string, unknown> | null;
    addon_modules: string[] | null;
}

interface PageProps {
    tenant?: Tenant;
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            user_type: string;
            tenant_id: number | null;
        };
    };
    [key: string]: unknown;
}

export function useTenant() {
    const { tenant, auth } = usePage<PageProps>().props;

    const getCurrentTenant = (): Tenant | null => tenant ?? null;

    const isModuleEnabled = (moduleKey: string): boolean => {
        if (!tenant) return false;
        const features = tenant.features as Record<string, boolean> | null;
        return features?.[moduleKey] === true;
    };

    const getTenantConfig = <T = unknown>(key: string, defaultValue?: T): T => {
        const settings = tenant?.settings as Record<string, unknown> | null;
        return (settings?.[key] as T) ?? (defaultValue as T);
    };

    const hasAddon = (addonKey: string): boolean => {
        return tenant?.addon_modules?.includes(addonKey) ?? false;
    };

    const isSuperAdmin = (): boolean => auth.user.user_type === 'super_admin';

    const getTenantColors = (): Record<string, string> => {
        return tenant?.colors ?? {
            primary: '#1e40af',
            secondary: '#64748b',
            accent: '#f59e0b',
        };
    };

    return {
        tenant: getCurrentTenant(),
        getCurrentTenant,
        isModuleEnabled,
        getTenantConfig,
        hasAddon,
        isSuperAdmin,
        getTenantColors,
    };
}
