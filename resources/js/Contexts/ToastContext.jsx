import { createContext, useContext, useState, useCallback } from 'react';

const ToastContext = createContext();

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};

export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback((toast) => {
        const id = Date.now() + Math.random();
        const newToast = {
            id,
            type: 'success',
            duration: 4000,
            ...toast,
        };
        
        setToasts(prev => [...prev, newToast]);
        return id;
    }, []);

    const removeToast = useCallback((id) => {
        setToasts(prev => prev.filter(toast => toast.id !== id));
    }, []);

    const success = useCallback((message, title = null) => {
        return addToast({ type: 'success', message, title });
    }, [addToast]);

    const error = useCallback((message, title = null) => {
        return addToast({ type: 'error', message, title });
    }, [addToast]);

    const warning = useCallback((message, title = null) => {
        return addToast({ type: 'warning', message, title });
    }, [addToast]);

    const info = useCallback((message, title = null) => {
        return addToast({ type: 'info', message, title });
    }, [addToast]);

    const value = {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    };

    return (
        <ToastContext.Provider value={value}>
            {children}
        </ToastContext.Provider>
    );
};
