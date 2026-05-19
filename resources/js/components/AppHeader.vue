<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChefHat, LayoutGrid, UserCog } from 'lucide-vue-next';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { index as recipesIndex } from '@/actions/App/Http/Controllers/RecipeController';
import { edit as fitnessEdit } from '@/actions/App/Http/Controllers/FitnessProfileController';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    { breadcrumbs: () => [] },
);

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

const navItems = [
    { href: dashboard(), icon: LayoutGrid, label: 'Dashboard', exact: true },
    { href: recipesIndex(), icon: ChefHat, label: 'Recipes', exact: false },
    { href: fitnessEdit(), icon: UserCog, label: 'Fitness', exact: true },
];

function isActive(href: string, exact: boolean): boolean {
    return exact ? isCurrentUrl(href) : isCurrentOrParentUrl(href);
}
</script>

<template>
    <header
        class="sticky top-0 z-50 flex h-16 shrink-0 items-center gap-3 border-b border-border bg-background/95 px-3 sm:px-5 backdrop-blur"
    >
        <!-- Logo -->
        <Link :href="dashboard()" class="flex shrink-0 items-center">
            <img src="/icon-192.png" alt="MacroBot" class="size-8 object-contain" />
        </Link>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Nav icons -->
        <nav class="flex items-center gap-1.5">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                class="flex h-10 items-center justify-center gap-2 overflow-hidden rounded-full px-3 transition-all duration-300 ease-in-out"
                :class="
                    isActive(item.href, item.exact)
                        ? 'bg-foreground/10 text-foreground'
                        : 'text-muted-foreground hover:bg-foreground/5 hover:text-foreground'
                "
            >
                <component :is="item.icon" class="h-[22px] w-[22px] shrink-0" />
                <span
                    class="hidden sm:block overflow-hidden whitespace-nowrap text-sm font-medium transition-all duration-300 ease-in-out"
                    :class="isActive(item.href, item.exact) ? 'sm:max-w-24 sm:opacity-100' : 'sm:max-w-0 sm:opacity-0'"
                >{{ item.label }}</span>
            </Link>

            <!-- User dropdown -->
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="ml-1 h-10 w-10 rounded-full p-0.5">
                        <Avatar class="size-8">
                            <AvatarImage
                                v-if="auth.user.avatar"
                                :src="auth.user.avatar"
                                :alt="auth.user.name"
                            />
                            <AvatarFallback
                                class="bg-neutral-200 text-xs font-semibold text-black dark:bg-neutral-700 dark:text-white"
                            >
                                {{ getInitials(auth.user?.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <UserMenuContent :user="auth.user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </nav>
    </header>
</template>
