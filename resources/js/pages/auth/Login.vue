<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Masuk ke Akun',
        description: 'Masukkan username dan kata sandi Anda untuk melanjutkan.',
    },
});

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();
</script>

<template>
    <Head title="Masuk" />

    <div
        v-if="status"
        class="mb-4 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-4"
    >
        <div class="grid gap-4">
            <div class="grid gap-1.5">
                <Label for="username" class="text-xs font-medium">Username</Label>
                <Input
                    id="username"
                    type="text"
                    name="username"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    placeholder="Masukkan username"
                    class="h-10 text-sm"
                />
                <InputError :message="errors.username" />
            </div>

            <div class="grid gap-1.5">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-xs font-medium">Kata Sandi</Label>
                    <TextLink
                        v-if="canResetPassword"
                        href="/forgot-password"
                        class="text-xs text-muted-foreground hover:text-primary"
                    >
                        Lupa kata sandi?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                    class="h-10 text-sm"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <Label for="remember" class="flex cursor-pointer items-center space-x-2 text-xs">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span class="text-muted-foreground hover:text-foreground">Ingat saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 h-10 w-full text-sm font-semibold shadow-xs"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" class="mr-2" />
                <LogIn v-else class="mr-2 h-4 w-4" />
                Masuk
            </Button>
        </div>
    </Form>
</template>
