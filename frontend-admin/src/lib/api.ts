const API_BASE = '/api';

export const api = {
    get: async (endpoint: string) => {
        const token = localStorage.getItem('adminToken');
        const res = await fetch(`${API_BASE}${endpoint}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        if (res.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/login';
        }
        return res;
    },
    post: async (endpoint: string, body: any) => {
        const token = localStorage.getItem('adminToken');
        const isFormData = body instanceof FormData;
        const headers: HeadersInit = {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        };
        if (!isFormData) {
            headers['Content-Type'] = 'application/json';
        }

        const res = await fetch(`${API_BASE}${endpoint}`, {
            method: 'POST',
            headers: headers,
            body: isFormData ? body : JSON.stringify(body)
        });
        if (res.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/login';
        }
        return res;
    },
    delete: async (endpoint: string, options: RequestInit = {}) => {
        const token = localStorage.getItem('adminToken');
        const res = await fetch(`${API_BASE}${endpoint}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                ...(options.headers || {}) as any
            },
            body: options.body
        });
        if (res.status === 401) {
            localStorage.removeItem('adminToken');
            window.location.href = '/login';
        }
        return res;
    }
};
