import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    forAdmin?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_admin: boolean;
}

export interface ItemType {
    id: number;
    name: string;
    description: string | null;
    created_at: string;
    updated_at: string;
}

export interface Item {
    id: number;
    name: string;
    quantity: number;
    unit: string | null;
    price_per_unit: number | undefined;
    created_at: string;
    updated_at: string;
    item_type_id: number | null;
    item_type: ItemType | null;
}

export interface ResourceCollection<T> {
    data: T[];
}

export type BreadcrumbItemType = BreadcrumbItem;
