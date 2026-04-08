function getCsrfToken(): string {
    const meta = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
    if (meta) return meta;
    // Fallback: XSRF-TOKEN cookie
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function request<T>(url: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers ?? {}),
        },
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message ?? data.error ?? 'Terjadi kesalahan.');
    }

    return data as T;
}

export const api = {
    get: <T>(url: string) => request<T>(url),
    post: <T>(url: string, body: unknown) =>
        request<T>(url, { method: 'POST', body: JSON.stringify(body) }),
};
