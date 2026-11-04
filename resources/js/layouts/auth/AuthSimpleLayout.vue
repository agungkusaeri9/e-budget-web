<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Moon, Sun } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useAppearance } from '@/composables/useAppearance';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();

const toggleTheme = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center bg-gradient-to-br from-background via-muted/40 to-background p-4 sm:p-6 md:p-10"
    >
        <!-- Floating Theme Switcher -->
        <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
            <Button
                variant="outline"
                size="icon"
                class="h-9 w-9 rounded-full border border-border/80 bg-card/80 text-muted-foreground shadow-xs backdrop-blur-xs hover:text-foreground"
                :title="
                    resolvedAppearance === 'dark'
                        ? 'Ganti ke Mode Terang'
                        : 'Ganti ke Mode Gelap'
                "
                @click="toggleTheme"
            >
                <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                <Moon v-else class="h-4 w-4" />
            </Button>
        </div>

        <div class="w-full max-w-[420px]">
            <Card
                class="rounded-2xl border border-border/70 bg-card/95 shadow-xl backdrop-blur-sm"
            >
                <CardHeader class="space-y-4 px-6 pt-8 pb-4 text-center sm:px-8">
                    <Link
                        :href="home()"
                        class="mx-auto flex flex-col items-center gap-2.5 transition-transform hover:scale-105"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-md shadow-primary/25"
                        >
                            <AppLogoIcon class="size-7 fill-current" />
                        </div>
                        <span class="text-xl font-bold tracking-tight text-foreground"
                            >e-Budget</span
                        >
                    </Link>

                    <div class="space-y-1">
                        <CardTitle
                            v-if="title"
                            class="text-lg font-semibold tracking-tight text-foreground"
                        >
                            {{ title }}
                        </CardTitle>
                        <CardDescription v-if="description" class="text-xs">
                            {{ description }}
                        </CardDescription>
                    </div>
                </CardHeader>

                <CardContent class="px-6 pt-2 pb-8 sm:px-8">
                    <slot />
                </CardContent>
            </Card>

            <p class="mt-6 text-center text-xs text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} e-Budget. Kelola anggaran lebih rapi dan terkontrol.
            </p>
        </div>
    </div>
</template>
