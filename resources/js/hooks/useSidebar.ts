import { useState, useCallback } from 'react';

const STORAGE_KEY = 'sidebar_collapsed_categories';

function loadCollapsed(): Set<string> {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? new Set(JSON.parse(stored) as string[]) : new Set();
    } catch {
        return new Set();
    }
}

function saveCollapsed(collapsed: Set<string>): void {
    localStorage.setItem(STORAGE_KEY, JSON.stringify([...collapsed]));
}

export function useSidebar() {
    const [collapsedCategories, setCollapsedCategories] = useState<Set<string>>(loadCollapsed);

    const toggleCategory = useCallback((category: string) => {
        setCollapsedCategories((prev) => {
            const next = new Set(prev);
            if (next.has(category)) {
                next.delete(category);
            } else {
                next.add(category);
            }
            saveCollapsed(next);
            return next;
        });
    }, []);

    const isCategoryOpen = useCallback(
        (category: string): boolean => !collapsedCategories.has(category),
        [collapsedCategories],
    );

    const openAll = useCallback(() => {
        setCollapsedCategories(new Set());
        saveCollapsed(new Set());
    }, []);

    const closeAll = useCallback((categories: string[]) => {
        const next = new Set(categories);
        setCollapsedCategories(next);
        saveCollapsed(next);
    }, []);

    return {
        toggleCategory,
        isCategoryOpen,
        collapsedCategories,
        openAll,
        closeAll,
    };
}
