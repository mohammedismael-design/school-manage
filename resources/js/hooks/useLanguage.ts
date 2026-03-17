import { useState, useCallback } from 'react';

type Language = 'en' | 'sw';

const translations: Record<Language, Record<string, string>> = {
    en: {
        dashboard: 'Dashboard',
        students: 'Students',
        staff: 'Staff',
        finance: 'Finance',
        attendance: 'Attendance',
        academics: 'Academics',
        settings: 'Settings',
        logout: 'Logout',
        save: 'Save',
        cancel: 'Cancel',
        delete: 'Delete',
        edit: 'Edit',
        create: 'Create',
        search: 'Search',
        loading: 'Loading...',
        success: 'Success',
        error: 'Error',
        confirm_delete: 'Are you sure you want to delete this?',
    },
    sw: {
        dashboard: 'Dashibodi',
        students: 'Wanafunzi',
        staff: 'Wafanyakazi',
        finance: 'Fedha',
        attendance: 'Mahudhurio',
        academics: 'Masomo',
        settings: 'Mipangilio',
        logout: 'Toka',
        save: 'Hifadhi',
        cancel: 'Ghairi',
        delete: 'Futa',
        edit: 'Hariri',
        create: 'Unda',
        search: 'Tafuta',
        loading: 'Inapakia...',
        success: 'Mafanikio',
        error: 'Hitilafu',
        confirm_delete: 'Je, una uhakika unataka kufuta hii?',
    },
};

const LANGUAGE_KEY = 'app_language';

export function useLanguage() {
    const [currentLanguage, setCurrentLanguageState] = useState<Language>(() => {
        const stored = localStorage.getItem(LANGUAGE_KEY) as Language | null;
        return stored && ['en', 'sw'].includes(stored) ? stored : 'en';
    });

    const setLanguage = useCallback((lang: Language) => {
        localStorage.setItem(LANGUAGE_KEY, lang);
        setCurrentLanguageState(lang);
    }, []);

    const t = useCallback(
        (key: string): string => {
            return translations[currentLanguage][key] ?? translations.en[key] ?? key;
        },
        [currentLanguage],
    );

    const availableLanguages: { code: Language; name: string }[] = [
        { code: 'en', name: 'English' },
        { code: 'sw', name: 'Kiswahili' },
    ];

    return {
        currentLanguage,
        setLanguage,
        t,
        availableLanguages,
    };
}
