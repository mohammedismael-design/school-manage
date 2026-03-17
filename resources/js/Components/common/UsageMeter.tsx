import React from 'react';

interface UsageMeterProps {
    label: string;
    current: number;
    limit: number;
    isUnlimited?: boolean;
    unit?: string;
    showNumbers?: boolean;
    className?: string;
}

function getColorClass(percentage: number): string {
    if (percentage >= 100) return 'bg-red-600';
    if (percentage >= 80) return 'bg-orange-500';
    if (percentage >= 60) return 'bg-yellow-500';
    return 'bg-emerald-500';
}

function getTextColorClass(percentage: number): string {
    if (percentage >= 100) return 'text-red-600';
    if (percentage >= 80) return 'text-orange-500';
    if (percentage >= 60) return 'text-yellow-600';
    return 'text-emerald-600';
}

function formatStorage(mb: number): string {
    if (mb >= 1024) return `${(mb / 1024).toFixed(1)} GB`;
    return `${mb} MB`;
}

export function UsageMeter({
    label,
    current,
    limit,
    isUnlimited = false,
    unit = '',
    showNumbers = true,
    className = '',
}: UsageMeterProps) {
    const percentage = isUnlimited ? 0 : limit > 0 ? Math.min((current / limit) * 100, 100) : 0;
    const colorClass = getColorClass(percentage);
    const textColor = getTextColorClass(percentage);

    const formatValue = (value: number): string => {
        if (unit === 'storage') return formatStorage(value);
        return value.toLocaleString();
    };

    return (
        <div className={`space-y-1 ${className}`}>
            <div className="flex items-center justify-between text-sm">
                <span className="font-medium text-gray-700">{label}</span>
                {showNumbers && (
                    <span className={`font-semibold ${textColor}`}>
                        {isUnlimited ? (
                            <span className="text-emerald-600">Unlimited</span>
                        ) : (
                            <>
                                {formatValue(current)} / {formatValue(limit)}
                                {unit && unit !== 'storage' ? ` ${unit}` : ''}
                            </>
                        )}
                    </span>
                )}
            </div>

            {!isUnlimited && (
                <>
                    <div className="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                        <div
                            className={`h-full rounded-full transition-all duration-500 ${colorClass}`}
                            style={{ width: `${percentage}%` }}
                            role="progressbar"
                            aria-valuenow={current}
                            aria-valuemin={0}
                            aria-valuemax={limit}
                        />
                    </div>
                    {percentage >= 80 && (
                        <p className={`text-xs font-medium ${textColor}`}>
                            {percentage >= 100
                                ? '⚠️ Limit reached — upgrade to add more'
                                : `⚡ ${Math.round(100 - percentage)}% remaining`}
                        </p>
                    )}
                </>
            )}
        </div>
    );
}
