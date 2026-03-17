import { usePage } from '@inertiajs/react';

interface SubscriptionLimits {
    students: LimitInfo;
    staff: LimitInfo;
    storage: LimitInfo;
}

interface LimitInfo {
    can_add: boolean;
    current: number;
    limit: number;
    is_unlimited: boolean;
    remaining: number | null;
    percentage: number;
    is_near_limit: boolean;
    is_at_limit: boolean;
}

interface PageProps {
    subscription_limits?: SubscriptionLimits;
    [key: string]: unknown;
}

const DEFAULT_LIMIT: LimitInfo = {
    can_add: true,
    current: 0,
    limit: 0,
    is_unlimited: true,
    remaining: null,
    percentage: 0,
    is_near_limit: false,
    is_at_limit: false,
};

export function useSubscriptionLimits() {
    const { subscription_limits } = usePage<PageProps>().props;
    const limits = subscription_limits;

    const getRemainingStudents = (): number | null =>
        limits?.students.remaining ?? null;

    const getRemainingStaff = (): number | null =>
        limits?.staff.remaining ?? null;

    const getRemainingStorage = (): number | null =>
        limits?.storage.remaining ?? null;

    const getUsagePercentages = () => ({
        students: limits?.students.percentage ?? 0,
        staff: limits?.staff.percentage ?? 0,
        storage: limits?.storage.percentage ?? 0,
    });

    const isNearLimit = (resource: keyof SubscriptionLimits): boolean =>
        limits?.[resource]?.is_near_limit ?? false;

    const isAtLimit = (resource: keyof SubscriptionLimits): boolean =>
        limits?.[resource]?.is_at_limit ?? false;

    const canAdd = (resource: keyof SubscriptionLimits): boolean =>
        limits?.[resource]?.can_add ?? true;

    const getLimitMessage = (resource: keyof SubscriptionLimits): string => {
        const info = limits?.[resource] ?? DEFAULT_LIMIT;

        if (info.is_unlimited) return 'Unlimited';
        if (info.is_at_limit) {
            return `Limit reached (${info.current}/${info.limit}). Upgrade your plan.`;
        }
        if (info.is_near_limit) {
            return `${info.remaining} remaining (${info.percentage}% used)`;
        }

        return `${info.current}/${info.limit} used`;
    };

    const getInfo = (resource: keyof SubscriptionLimits): LimitInfo =>
        limits?.[resource] ?? DEFAULT_LIMIT;

    return {
        limits,
        getRemainingStudents,
        getRemainingStaff,
        getRemainingStorage,
        getUsagePercentages,
        isNearLimit,
        isAtLimit,
        canAdd,
        getLimitMessage,
        getInfo,
    };
}
