import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { ToastProvider } from './Contexts/ToastContext';
import ToastContainer from './Components/ToastContainer';
import { useToast } from './Contexts/ToastContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Wrapper component that includes toast functionality
function AppWithToasts({ App, props }) {
    const { toasts, removeToast } = useToast();
    
    return (
        <>
            <App {...props} />
            <ToastContainer toasts={toasts} onRemove={removeToast} />
        </>
    );
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <ToastProvider>
                <AppWithToasts App={App} props={props} />
            </ToastProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
