// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { type FormEventHandler, useEffect } from 'react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { email as passwordEmailRoute } from '@/routes/password';

declare global {
    interface Window {
        grecaptcha?: {
            ready: (cb: () => void) => void;
            execute: (siteKey: string, options: { action: string }) => Promise<string>;
        };
    }
}

interface Props {
    status?: string;
    recaptchaEnabled?: boolean;
    recaptchaSiteKey?: string | null;
}

export default function ForgotPassword({ status, recaptchaEnabled, recaptchaSiteKey }: Props) {
    const recaptchaActive = !!(recaptchaEnabled && recaptchaSiteKey);

    const form = useForm<{ email: string; recaptcha_token: string }>({
        email: '',
        recaptcha_token: '',
    });

    useEffect(() => {
        if (!recaptchaActive) return;

        const id = 'recaptcha-script';
        if (document.getElementById(id)) return;

        const script = document.createElement('script');
        script.id = id;
        script.src = `https://www.google.com/recaptcha/api.js?render=${recaptchaSiteKey}`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }, [recaptchaActive, recaptchaSiteKey]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        const post = (token: string = '') => {
            form.transform((data) => ({ ...data, recaptcha_token: token }));
            form.post(passwordEmailRoute().url);
        };

        if (recaptchaActive && window.grecaptcha && recaptchaSiteKey) {
            window.grecaptcha.ready(() => {
                window
                    .grecaptcha!.execute(recaptchaSiteKey, { action: 'forgot_password' })
                    .then((token) => post(token))
                    .catch(() => post(''));
            });
        } else {
            post('');
        }
    };

    return (
        <>
            <Head title="Forgot password" />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <form onSubmit={submit}>
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autoComplete="off"
                            autoFocus
                            placeholder="email@example.com"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                        />

                        <InputError message={form.errors.email} />
                    </div>

                    <div className="my-6 flex items-center justify-start">
                        <Button
                            className="w-full"
                            disabled={form.processing}
                            data-test="email-password-reset-link-button"
                        >
                            {form.processing && (
                                <LoaderCircle className="h-4 w-4 animate-spin" />
                            )}
                            Email password reset link
                        </Button>
                    </div>
                </form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>Or, return to</span>
                    <TextLink href={login()}>log in</TextLink>
                </div>

                {recaptchaActive && (
                    <p className="text-center text-[11px] text-muted-foreground/70">
                        Halaman ini dilindungi reCAPTCHA — Google{' '}
                        <a href="https://policies.google.com/privacy" target="_blank" rel="noreferrer" className="underline">
                            Privacy
                        </a>{' '}
                        &{' '}
                        <a href="https://policies.google.com/terms" target="_blank" rel="noreferrer" className="underline">
                            Terms
                        </a>
                    </p>
                )}
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Forgot password',
    description: 'Enter your email to receive a password reset link',
};
