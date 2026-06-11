export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    unread_conversations: number;
};

interface Country {
    id: number;
    name: string;
    ar_name: string;
    flag?: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    registration_type?: number;
    username?: string;
    phone_number?: string;
    age?: string | number;
    marriage_type?: string | number;
    marriage_type_label?: string;
    marriage_status?: string | number;
    marriage_status_label?: string;
    child_count?: string | number;
    residence?: Country | null;
    nationality?: Country | null;
    city?: Country | null;
    weight?: string | number;
    height?: string | number;
    skin_color?: string | number;
    skin_color_label?: string;
    body_shape?: string | number;
    body_shape_label?: string;
    devotion?: string | number;
    devotion_label?: string;
    prayer?: string | number;
    prayer_label?: string;
    smoking?: boolean;
    beard?: boolean;
    education_level?: string | number;
    education_level_label?: string;
    financial_status?: string | number;
    financial_status_label?: string;
    field_of_work?: string | number;
    field_of_work_label?: string;
    job?: string;
    monthly_income?: string | number;
    monthly_income_label?: string;
    health_status?: string | number;
    health_status_label?: string;
    about_self?: string;
    about_partner?: string;
    has_active_subscription?: boolean;
    subcription_type?: string;
}
