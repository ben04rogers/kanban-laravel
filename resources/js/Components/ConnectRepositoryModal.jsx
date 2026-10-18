import { useForm, router } from '@inertiajs/react';
import { useEffect } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useToast } from '@/Contexts/ToastContext';

export default function ConnectRepositoryModal({ isOpen, onClose, board }) {
    const { success, error } = useToast();
    const repository = board.repository;

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        repository: '',
        access_token: '',
    });

    useEffect(() => {
        if (isOpen) {
            reset();
            clearErrors();
        }
    }, [isOpen]);

    const handleClose = () => {
        reset();
        clearErrors();
        onClose();
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('boards.repository.store', board.id), {
            preserveScroll: true,
            onSuccess: () => {
                success('Repository connected successfully!', 'GitHub Connected');
                reset();
                onClose();
            },
            onError: () => {
                error('Could not connect repository. Check the details below.');
            },
        });
    };

    const disconnect = () => {
        if (!confirm(`Disconnect ${repository.full_name} from this board?`)) {
            return;
        }

        router.delete(route('boards.repository.destroy', board.id), {
            preserveScroll: true,
            onSuccess: () => {
                success('Repository disconnected.', 'GitHub Disconnected');
                onClose();
            },
            onError: () => {
                error('Failed to disconnect repository.');
            },
        });
    };

    if (!isOpen) return null;

    return (
        <div
            className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            onClick={handleClose}
        >
            <div
                className="relative top-10 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="mt-3">
                    {/* Header */}
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-medium text-gray-900">GitHub Repository</h3>
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {repository ? (
                        <div>
                            <div className="flex items-center justify-between p-3 bg-gray-50 rounded-md mb-6">
                                <div>
                                    <div className="font-medium text-gray-900">{repository.full_name}</div>
                                    <div className="text-sm text-gray-500">Default branch: {repository.default_branch}</div>
                                </div>
                                <DangerButton onClick={disconnect} className="px-3 py-1 text-sm">
                                    Disconnect
                                </DangerButton>
                            </div>
                        </div>
                    ) : (
                        <form onSubmit={submit}>
                            <p className="text-sm text-gray-500 mb-4">
                                Connect a repository so cards on this board can be implemented by AI and turned into pull requests.
                            </p>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Repository
                                </label>
                                <input
                                    type="text"
                                    value={data.repository}
                                    onChange={(e) => setData('repository', e.target.value)}
                                    placeholder="owner/repo"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.repository && (
                                    <p className="mt-1 text-sm text-red-600">{errors.repository}</p>
                                )}
                            </div>

                            <div className="mb-2">
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    GitHub Access Token
                                </label>
                                <input
                                    type="password"
                                    value={data.access_token}
                                    onChange={(e) => setData('access_token', e.target.value)}
                                    placeholder="ghp_..."
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.access_token && (
                                    <p className="mt-1 text-sm text-red-600">{errors.access_token}</p>
                                )}
                            </div>
                            <p className="text-xs text-gray-400 mb-6">
                                Use a fine-grained personal access token scoped to this one repository, with
                                read/write access to code and pull requests. The token is encrypted and never shown again.
                            </p>

                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-200">
                                <button
                                    type="button"
                                    onClick={handleClose}
                                    className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <PrimaryButton disabled={processing}>
                                    {processing ? 'Connecting...' : 'Connect Repository'}
                                </PrimaryButton>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </div>
    );
}
