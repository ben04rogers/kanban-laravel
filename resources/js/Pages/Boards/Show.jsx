import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import Breadcrumb from '@/Components/Breadcrumb';
import CardModal from '@/Components/CardModal';
import CardDetailModal from '@/Components/CardDetailModal';
import ShareBoardModal from '@/Components/ShareBoardModal';
import EditBoardModal from '@/Components/EditBoardModal';
import DroppableColumn from '@/Components/DroppableColumn';
import Dropdown from '@/Components/Dropdown';
import { useState, useEffect } from 'react';
import { useToast } from '@/Contexts/ToastContext';

export default function Show({ board, boardUsers = [], cardId = null }) {
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [showCardModal, setShowCardModal] = useState(false);
    const [showCardDetailModal, setShowCardDetailModal] = useState(false);
    const [showShareModal, setShowShareModal] = useState(false);
    const [selectedColumn, setSelectedColumn] = useState(null);
    const [selectedCard, setSelectedCard] = useState(null);
    const [isExpanded, setIsExpanded] = useState(() => {
        // Initialize from localStorage, default to false
        const saved = localStorage.getItem('board-expanded');
        return saved ? JSON.parse(saved) : false;
    });
    const { success, error } = useToast();

    const toggleExpanded = () => {
        const newExpanded = !isExpanded;
        setIsExpanded(newExpanded);
        // Save to localStorage
        localStorage.setItem('board-expanded', JSON.stringify(newExpanded));
    };

    const deleteBoard = () => {
        router.delete(route('boards.destroy', board.id), {
            onSuccess: () => {
                success(`Board "${board.name}" deleted successfully!`, 'Board Deleted');
            },
            onError: () => {
                error('Failed to delete board. Please try again.');
            },
        });
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
        // Navigate to the card URL to load full card data including comments
        router.visit(route('cards.show', card.id));
    };

    const closeCardDetailModal = () => {
        setShowCardDetailModal(false);
        setSelectedCard(null);
        // Navigate back to board
        router.visit(route('boards.show', board.id));
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
                    router.visit(`/cards/${cardIdFromUrl}`);
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
        <AuthenticatedLayout isExpanded={isExpanded}>
            <Head title={board.name} />

            <div className="py-12">
                <div className={`mx-auto sm:px-6 lg:px-8 ${isExpanded ? 'max-w-none px-4' : 'max-w-7xl'}`}>
                    {/* Breadcrumb */}
                    <div className="mb-6">
                        <Breadcrumb items={breadcrumbItems} />
                    </div>
                    
                    {/* Board Header */}
                    <div className="bg-white shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="flex justify-between items-start">
                                <div>
                                    <div className="flex items-center gap-3 mb-2">
                                        <h1 className="text-2xl font-bold text-gray-900">{board.name}</h1>
                                        <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                                            board.status === 'active' ? 'bg-blue-100 text-blue-800' :
                                            board.status === 'completed' ? 'bg-green-100 text-green-800' :
                                            'bg-gray-100 text-gray-800'
                                        }`}>
                                            {board.status === 'active' ? 'Active' :
                                             board.status === 'completed' ? 'Completed' : 'Archived'}
                                        </span>
                                    </div>
                                    {board.description && (
                                        <p className="text-gray-600 mt-2">{board.description}</p>
                                    )}
                                </div>
                                
                                <div className="flex items-center space-x-3">
                                    {/* Board Width Toggle */}
                                    <button
                                        onClick={toggleExpanded}
                                        className={`flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                            isExpanded 
                                                ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' 
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        }`}
                                        title={isExpanded ? 'Collapse board width' : 'Expand board width'}
                                    >
                                        {isExpanded ? (
                                            <>
                                                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 9V4.5M9 9H4.5M9 9L3.5 3.5M15 9h4.5M15 9V4.5M15 9l5.5-5.5M9 15v4.5M9 15H4.5M9 15l-5.5 5.5M15 15h4.5M15 15v4.5m0-4.5l5.5 5.5" />
                                                </svg>
                                                Compact
                                            </>
                                        ) : (
                                            <>
                                                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                                </svg>
                                                Expand
                                            </>
                                        )}
                                    </button>
                                    
                                    {/* Actions */}
                                    {board.is_creator ? (
                                        <Dropdown>
                                            <Dropdown.Trigger>
                                                <button className="flex items-center px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <span>Actions</span>
                                                    <svg className="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                                    </svg>
                                                </button>
                                            </Dropdown.Trigger>

                                            <Dropdown.Content contentClasses="py-1 bg-white" width="48">
                                                <button
                                                    onClick={() => setShowCardModal(true)}
                                                    className="block w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                                >
                                                    <div className="flex items-center">
                                                        <svg className="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Add Card
                                                    </div>
                                                </button>

                                                <button
                                                    onClick={() => setShowShareModal(true)}
                                                    className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                                >
                                                    <div className="flex items-center">
                                                        <svg className="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                                        </svg>
                                                        Share Board
                                                    </div>
                                                </button>

                                                <button
                                                    onClick={() => setShowEditModal(true)}
                                                    className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                                >
                                                    <div className="flex items-center">
                                                        <svg className="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit Board
                                                    </div>
                                                </button>

                                                <div className="border-t border-gray-100"></div>

                                                <button
                                                    onClick={() => setShowDeleteModal(true)}
                                                    className="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 focus:bg-red-50 focus:outline-none"
                                                >
                                                    <div className="flex items-center">
                                                        <svg className="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete Board
                                                    </div>
                                                </button>
                                            </Dropdown.Content>
                                        </Dropdown>
                                    ) : (
                                        <button
                                            onClick={() => setShowCardModal(true)}
                                            className="flex items-center pr-4 pl-2 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        >
                                            <svg className="h-4 mr-1 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Card
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Kanban Board */}
                    <div className={`bg-white shadow-sm sm:rounded-lg overflow-x-auto ${isExpanded ? 'px-4' : 'px-6'}`}>
                        <div className="py-6">
                            <div className="flex gap-6 min-w-max" style={{ paddingRight: isExpanded ? '1rem' : '1.5rem' }}>
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
                boardUsers={boardUsers}
            />

            {/* Card Detail Modal */}
            <CardDetailModal
                isOpen={showCardDetailModal}
                onClose={closeCardDetailModal}
                card={selectedCard}
                boardUsers={boardUsers}
            />

            {/* Share Board Modal */}
            <ShareBoardModal
                isOpen={showShareModal}
                onClose={() => setShowShareModal(false)}
                boardId={board.id}
            />

            {/* Edit Board Modal */}
            <EditBoardModal
                isOpen={showEditModal}
                onClose={() => setShowEditModal(false)}
                board={board}
            />
        </AuthenticatedLayout>
    );
}
