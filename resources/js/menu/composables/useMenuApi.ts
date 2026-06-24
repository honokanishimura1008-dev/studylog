import type { MenuApiResponse, MenuFormPayload } from '../types';

interface ApiErrorBody {
  message?: string;
  errors?: Record<string, string[]>;
}

export function useMenuApi(csrfToken: string) {
  async function request<T>(
    method: string,
    url: string,
    body?: MenuFormPayload,
  ): Promise<T> {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    const data = (await res.json()) as T & ApiErrorBody;

    if (!res.ok) {
      const msg =
        data.message ??
        (data.errors
          ? Object.values(data.errors).flat().join('\n')
          : 'エラーが発生しました');
      throw new Error(msg);
    }

    return data;
  }

  return {
    store: (url: string, body: MenuFormPayload) =>
      request<MenuApiResponse>('POST', url, body),
    update: (url: string, body: MenuFormPayload) =>
      request<MenuApiResponse>('PATCH', url, body),
    destroy: (url: string) => request<{ message: string }>('DELETE', url),
  };
}
