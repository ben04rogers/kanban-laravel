import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useState } from 'react';

export default function Index({ boards }) {
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [boardToDelete, setBoardToDelete] = useState(null);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('boards.store'), {
            onSuccess: () => {
                reset();
                setShowCreateForm(false);
            },
        });
    };

    const deleteBoard = (boardId) => {
        router.delete(route('boards.destroy', boardId), {
            onSuccess: () => {
                setBoardToDelete(null);
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Boards" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold">My Boards</h2>
                                <PrimaryButton onClick={() => setShowCreateForm(true)}>
                                    Create New Board
                                </PrimaryButton>
                            </div>

                            {/* Create Board Form */}
                            {showCreateForm && (
                                <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                                    <h3 className="text-lg font-semibold mb-4">Create New Board</h3>
                                    <form onSubmit={submit}>
                                        <div className="mb-4">
                                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                                Board Name
                                            </label>
                                            <input
                                                id="name"
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="Enter board name"
                                            />
                                            {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                                        </div>

                                        <div className="mb-4">
                                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                                Description (Optional)
                                            </label>
                                            <textarea
                                                id="description"
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                rows="3"
                                                placeholder="Enter board description"
                                            />
                                            {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                                        </div>

                                        <div className="flex gap-2">
                                            <PrimaryButton type="submit" disabled={processing}>
                                                {processing ? 'Creating...' : 'Create Board'}
                                            </PrimaryButton>
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setShowCreateForm(false);
                                                    reset();
                                                }}
                                                className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            )}

                            {/* Boards Grid */}
                            {boards.length === 0 ? (
                                <div className="text-center py-12">
                                    <div className="text-gray-500 text-lg mb-4">No boards yet</div>
                                    <p className="text-gray-400">Create your first board to get started!</p>
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {boards.map((board) => (
                                        <div key={board.id} className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                            <div className="flex justify-between items-start mb-4">
                                                <h3 className="text-xl font-semibold text-gray-900">{board.name}</h3>
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={route('boards.show', board.id)}
                                                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                                    >
                                                        View
                                                    </Link>
                                                    <button
                                                        onClick={() => setBoardToDelete(board)}
                                                        className="text-red-600 hover:text-red-800 text-sm font-medium"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            {board.description && (
                                                <p className="text-gray-600 mb-4">{board.description}</p>
                                            )}
                                            
                                            <div className="flex items-center justify-between text-sm text-gray-500">
                                                <span>{board.columns.length} columns</span>
                                                <span>{board.cards?.length || 0} cards</span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            {boardToDelete && (
                <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div className="mt-3 text-center">
                            <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <svg className="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 mt-2">Delete Board</h3>
                            <div className="mt-2 px-7 py-3">
                                <p className="text-sm text-gray-500">
                                    Are you sure you want to delete "{boardToDelete.name}"? This action cannot be undone and will delete all columns and cards in this board.
                                </p>
                            </div>
                            <div className="flex justify-center gap-3 mt-4">
                                <button
                                    onClick={() => setBoardToDelete(null)}
                                    className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400"
                                >
                                    Cancel
                                </button>
                                <DangerButton onClick={() => deleteBoard(boardToDelete.id)}>
                                    Delete Board
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
