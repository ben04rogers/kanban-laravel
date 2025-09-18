import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import Breadcrumb from '@/Components/Breadcrumb';
import CardModal from '@/Components/CardModal';
import CardDetailModal from '@/Components/CardDetailModal';
import DroppableColumn from '@/Components/DroppableColumn';
import { useState, useEffect } from 'react';

export default function Show({ board, cardId = null }) {
    const [showEditForm, setShowEditForm] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showCardModal, setShowCardModal] = useState(false);
    const [showCardDetailModal, setShowCardDetailModal] = useState(false);
    const [selectedColumn, setSelectedColumn] = useState(null);
    const [selectedCard, setSelectedCard] = useState(null);
    
    const { data, setData, put, processing, errors, reset } = useForm({
        name: board.name,
        description: board.description || '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('boards.update', board.id), {
            onSuccess: () => {
                setShowEditForm(false);
            },
        });
    };

    const deleteBoard = () => {
        router.delete(route('boards.destroy', board.id));
    };

    const openCardModal = (column = null) => {
        setSelectedColumn(column);
        setShowCardModal(true);
    };

    const closeCardModal = () => {
        setShowCardModal(false);
        setSelectedColumn(null);
    };

    const handleCardMove = (cardId, columnId, newPosition) => {
        router.post(route('cards.move', cardId), {
            board_column_id: columnId,
            position: newPosition,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // The page will refresh with updated data
            },
        });
    };

    const handleCardMoveToColumn = (cardId, columnId, newPosition) => {
        router.post(route('cards.move', cardId), {
            board_column_id: columnId,
            position: newPosition,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // The page will refresh with updated data
            },
        });
    };

    const handleCardClick = (card) => {
        setSelectedCard(card);
        setShowCardDetailModal(true);
        // Update URL without page reload
        window.history.pushState({}, '', `/cards/${card.id}`);
    };

    const closeCardDetailModal = () => {
        setShowCardDetailModal(false);
        setSelectedCard(null);
        // Reset URL to board
        window.history.pushState({}, '', `/boards/${board.id}`);
    };

    // Handle URL parameter for card modal
    useEffect(() => {
        if (cardId) {
            // Find the card in the board data
            const card = board.columns
                .flatMap(col => col.cards || [])
                .find(c => c.id == cardId);
            
            if (card) {
                setSelectedCard(card);
                setShowCardDetailModal(true);
            }
        }
    }, [cardId, board.columns]);

    // Handle browser back/forward navigation
    useEffect(() => {
        const handlePopState = () => {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/cards/')) {
                const cardIdFromUrl = currentPath.split('/cards/')[1];
                if (cardIdFromUrl && cardIdFromUrl !== cardId) {
                    // Navigate to the card
                    window.location.href = `/cards/${cardIdFromUrl}`;
                }
            } else if (showCardDetailModal) {
                // Close modal if we're back on the board
                closeCardDetailModal();
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, [cardId, showCardDetailModal]);

    const breadcrumbItems = [
        {
            label: 'Boards',
            href: '/'
        },
        {
            label: board.name
        }
    ];

    return (
        <AuthenticatedLayout>
            <Head title={board.name} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <div className="mb-6">
                        <Breadcrumb items={breadcrumbItems} />
                    </div>
                    
                    {/* Board Header */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="flex justify-between items-start mb-4">
                                <div>
                                    {showEditForm ? (
                                        <form onSubmit={submit} className="space-y-4">
                                            <div>
                                                <input
                                                    type="text"
                                                    value={data.name}
                                                    onChange={(e) => setData('name', e.target.value)}
                                                    className="text-2xl font-bold w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                />
                                                {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                                            </div>
                                            <div>
                                                <textarea
                                                    value={data.description}
                                                    onChange={(e) => setData('description', e.target.value)}
                                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    rows="2"
                                                    placeholder="Board description"
                                                />
                                                {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                                            </div>
                                            <div className="flex gap-2">
                                                <PrimaryButton type="submit" disabled={processing}>
                                                    {processing ? 'Saving...' : 'Save'}
                                                </PrimaryButton>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setShowEditForm(false);
                                                        reset();
                                                    }}
                                                    className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    ) : (
                                        <div>
                                            <h1 className="text-2xl font-bold text-gray-900">{board.name}</h1>
                                            {board.description && (
                                                <p className="text-gray-600 mt-2">{board.description}</p>
                                            )}
                                        </div>
                                    )}
                                </div>
                                
                                <div className="flex gap-2">
                                    {!showEditForm && (
                                        <>
                                            <button
                                                onClick={() => setShowCardModal(true)}
                                                className="px-4 py-2 text-green-600 bg-green-50 rounded-md hover:bg-green-100"
                                            >
                                                Add Card
                                            </button>
                                            <button
                                                onClick={() => setShowEditForm(true)}
                                                className="px-4 py-2 text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                                            >
                                                Edit Board
                                            </button>
                                            <button
                                                onClick={() => setShowDeleteModal(true)}
                                                className="px-4 py-2 text-red-600 bg-red-50 rounded-md hover:bg-red-100"
                                            >
                                                Delete Board
                                            </button>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Kanban Board */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex gap-6 overflow-x-auto pb-4">
                                {board.columns.map((column) => (
                                    <DroppableColumn
                                        key={column.id}
                                        column={column}
                                        onCardMove={handleCardMove}
                                        onCardMoveToColumn={handleCardMoveToColumn}
                                        onCardClick={handleCardClick}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            {showDeleteModal && (
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
                                    Are you sure you want to delete "{board.name}"? This action cannot be undone and will delete all columns and cards in this board.
                                </p>
                            </div>
                            <div className="flex justify-center gap-3 mt-4">
                                <button
                                    onClick={() => setShowDeleteModal(false)}
                                    className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400"
                                >
                                    Cancel
                                </button>
                                <DangerButton onClick={deleteBoard}>
                                    Delete Board
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Card Creation Modal */}
            <CardModal
                isOpen={showCardModal}
                onClose={closeCardModal}
                boardId={board.id}
                columnId={selectedColumn?.id}
                columnName={selectedColumn?.name}
                columns={board.columns}
            />

            {/* Card Detail Modal */}
            <CardDetailModal
                isOpen={showCardDetailModal}
                onClose={closeCardDetailModal}
                card={selectedCard}
            />
        </AuthenticatedLayout>
    );
}
