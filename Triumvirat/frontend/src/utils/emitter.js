export const emitter = {
    emit(event, data) {
        window.dispatchEvent(new CustomEvent(event, { detail: data }));
    },
    on(event, callback) {
        window.addEventListener(event, (e) => callback(e.detail));
    }
};