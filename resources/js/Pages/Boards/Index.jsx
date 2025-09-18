import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useState } from 'react';

export default function Index({ boards }) {
    const [showCreateForm, setShowCreateForm] = useState(false);
    
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
        </AuthenticatedLayout>
    );
}
