import { usePage } from '@inertiajs/react';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            user_type: string;
            permissions: string[];
            roles: Role[];
        };
    };
    [key: string]: unknown;
}

export function usePermissions() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    const userPermissions: string[] = user.permissions ?? [];
    const userRoles: Role[] = user.roles ?? [];

    /**
     * Check if the user has a specific permission.
     * Super admins bypass all permission checks.
     */
    const can = (permission: string): boolean => {
        if (user.user_type === 'super_admin') return true;
        return userPermissions.includes(permission);
    };

    /**
     * Check if the user has any of the given permissions.
     */
    const canAny = (permissions: string[]): boolean => {
        if (user.user_type === 'super_admin') return true;
        return permissions.some((p) => userPermissions.includes(p));
    };

    /**
     * Check if the user has all of the given permissions.
     */
    const canAll = (permissions: string[]): boolean => {
        if (user.user_type === 'super_admin') return true;
        return permissions.every((p) => userPermissions.includes(p));
    };

    /**
     * Check if the user has a specific role by name.
     */
    const hasRole = (roleName: string): boolean => {
        if (user.user_type === 'super_admin') return true;
        return userRoles.some((r) => r.name === roleName);
    };

    /**
     * Check if the user has any of the given roles.
     */
    const hasAnyRole = (roleNames: string[]): boolean => {
        if (user.user_type === 'super_admin') return true;
        return roleNames.some((r) => hasRole(r));
    };

    return {
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        permissions: userPermissions,
        roles: userRoles,
        userType: user.user_type,
        isSuperAdmin: user.user_type === 'super_admin',
        isAdmin: ['admin', 'principal'].includes(user.user_type),
    };
}
