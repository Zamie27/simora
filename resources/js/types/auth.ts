export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role?: {
        name: string;
    };
    athlete_profile?: {
        profile_photo_path?: string;
        license_path?: string;
        uci_id?: string;
        license_valid_until?: string;
    };
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type SharedData = {
    auth: Auth;
    name: string;
    sidebarOpen: boolean;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
