import { ref } from 'vue'

export const authState = ref({
    isLoggedIn: !!localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user')) || null
});

export const updateAuthState = () => {
    authState.value.isLoggedIn = !!localStorage.getItem('token');
    authState.value.user = JSON.parse(localStorage.getItem('user')) || null;

    window.dispatchEvent(new Event('auth-change'));
};

export const logout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    updateAuthState()
}