import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import Dropdown from '@/Components/Dropdown';
import Breadcrumb from '@/Components/Breadcrumb';
import BoardModal from '@/Components/BoardModal';
import { useState } from 'react';
import { useToast } from '@/Contexts/ToastContext';

export default function Index({ boards }) {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [boardToDelete, setBoardToDelete] = useState(null);
    const { success, error } = useToast();

    const breadcrumbItems = [
        {
            label: 'Boards'
        }
    ];

    const deleteBoard = (boardId) => {
        const board = boards.find(b => b.id === boardId);
        router.delete(route('boards.destroy', boardId), {
            onSuccess: () => {
                success(`Board "${board?.name}" deleted successfully!`, 'Board Deleted');
                setBoardToDelete(null);
            },
            onError: () => {
                error('Failed to delete board. Please try again.');
            },
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Boards" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <div className="mb-6">
                        <Breadcrumb items={breadcrumbItems} />
                    </div>
                    
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-bold">My Boards</h2>
                                <PrimaryButton onClick={() => setShowCreateModal(true)}>
                                    Create Board
                                </PrimaryButton>
                            </div>

                            {/* Boards Grid */}
                            {boards.length === 0 ? (
                                <div className="text-center py-12">
                                    <div className="text-gray-500 text-lg mb-4">No boards yet</div>
                                    <p className="text-gray-400">Create your first board to get started!</p>
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {boards.map((board) => (
                                        <div key={board.id} className="relative bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow group">
                                            {/* Clickable area */}
                                            <Link
                                                href={route('boards.show', board.id)}
                                                className="block absolute inset-0 z-10"
                                            >
                                                <span className="sr-only">View {board.name}</span>
                                            </Link>
                                            
                                            {/* Content */}
                                            <div className="relative z-0">
                                                <div className="flex justify-between items-start">
                                                    <div className="flex-1">
                                                        <h3 className="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                                            {board.name}
                                                        </h3>
                                                        <div className="my-2 text-sm text-gray-500">
                                                            Created {new Date(board.created_at).toLocaleDateString('en-US', {
                                                                year: 'numeric',
                                                                month: 'short',
                                                                day: 'numeric'
                                                            })}
                                                        </div>  
                                                        {board.is_owner ? (
                                                            <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                                                Owner
                                                            </span>
                                                        ) : (
                                                            <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                                Shared with me
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
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

            {/* Create Board Modal */}
            <BoardModal 
                isOpen={showCreateModal} 
                onClose={() => setShowCreateModal(false)} 
            />
        </AuthenticatedLayout>
    );
}
