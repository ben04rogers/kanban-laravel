import { useForm, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import StatusDropdown from '@/Components/StatusDropdown';
import { useState, useEffect, useRef } from 'react';
import { useToast } from '@/Contexts/ToastContext';
import { draggable, dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { combine } from '@atlaskit/pragmatic-drag-and-drop/combine';

export default function EditBoardModal({ isOpen, onClose, board }) {
    const { success, error } = useToast();
    const [columns, setColumns] = useState(board.columns || []);
    const [draggedItem, setDraggedItem] = useState(null);
    const [editingColumnId, setEditingColumnId] = useState(null);
    const [editingColumnName, setEditingColumnName] = useState('');
    
    const { data, setData, put, processing, errors, reset } = useForm({
        name: board.name || '',
        description: board.description || '',
        status: board.status || 'active',
        columns: board.columns || [],
    });

    // Reset form when modal opens/closes
    useEffect(() => {
        if (isOpen) {
            setColumns(board.columns || []);
            setData({
                name: board.name || '',
                description: board.description || '',
                status: board.status || 'active',
                columns: board.columns || [],
            });
        }
    }, [isOpen, board]);

    const submit = (e) => {
        e.preventDefault();

        // Prepare column data with names and positions
        const columnData = columns.map((col, idx) => ({
            id: col.id || null,
            name: col.name,
            position: idx
        }));

        // Use router.put with explicit data to ensure columns are sent
        router.put(route('boards.update', board.id), {
            name: data.name,
            description: data.description,
            status: data.status,
            columns: columnData
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                success(`Board "${data.name}" updated successfully!`, 'Board Updated');
                onClose();
            },
            onError: (errors) => {
                if (errors.columns) {
                    error(errors.columns);
                } else {
                    error('Failed to update board. Please try again.');
                }
            },
        });
    };

    const handleAddColumn = () => {
        const tempId = `temp-${Date.now()}`; // Temporary unique ID for new columns
        const newColumn = {
            id: null, // null ID indicates a new column to backend
            tempId: tempId, // Local tracking ID
            name: 'New Column',
            position: columns.length,
            cards: [],
            isNew: true
        };
        setColumns([...columns, newColumn]);
    };

    const handleDeleteColumn = (column) => {
        // Use tempId for new columns, id for existing columns
        const identifier = column.tempId || column.id;
        setColumns(columns.filter(col => (col.tempId || col.id) !== identifier));
    };

    const handleStartEdit = (column) => {
        const identifier = column.tempId || column.id;
        setEditingColumnId(identifier);
        setEditingColumnName(column.name);
    };

    const handleSaveEdit = (column) => {
        const identifier = column.tempId || column.id;
        setColumns(columns.map(col =>
            (col.tempId || col.id) === identifier ? { ...col, name: editingColumnName } : col
        ));
        setEditingColumnId(null);
        setEditingColumnName('');
    };

    const handleCancelEdit = () => {
        setEditingColumnId(null);
        setEditingColumnName('');
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    // Create a draggable column component using Atlaskit
    const DraggableColumn = ({ column, index }) => {
        const ref = useRef(null);
        const [isDragOver, setIsDragOver] = useState(false);

        useEffect(() => {
            const element = ref.current;
            if (!element) return;

            return combine(
                draggable({
                    element,
                    getInitialData: () => ({
                        type: 'column',
                        columnId: column.id,
                        columnIndex: index,
                    }),
                    onDragStart: () => {
                        element.style.opacity = '0.5';
                        setDraggedItem(column);
                    },
                    onDrop: () => {
                        element.style.opacity = '1';
                        setDraggedItem(null);
                    },
                }),
                dropTargetForElements({
                    element,
                    getData: ({ input, element }) => ({
                        type: 'column',
                        columnId: column.id,
                        columnIndex: index,
                    }),
                    canDrop: ({ source }) => {
                        return source.data.type === 'column' && source.data.columnId !== column.id;
                    },
                    onDragEnter: () => {
                        setIsDragOver(true);
                    },
                    onDragLeave: () => {
                        setIsDragOver(false);
                    },
                    onDrop: ({ source }) => {
                        setIsDragOver(false);
                        if (source.data.type === 'column' && source.data.columnId !== column.id) {
                            const draggedIndex = source.data.columnIndex;
                            const targetIndex = index;
                            
                            const newColumns = [...columns];
                            const [removed] = newColumns.splice(draggedIndex, 1);
                            newColumns.splice(targetIndex, 0, removed);
                            
                            // Update positions
                            const updatedColumns = newColumns.map((col, idx) => ({
                                ...col,
                                position: idx
                            }));
                            
                            setColumns(updatedColumns);
                        }
                    },
                })
            );
        }, [column.id, index, columns, board.id]);

        const identifier = column.tempId || column.id;
        const isEditing = editingColumnId === identifier;

        return (
            <div
                ref={ref}
                className={`flex items-center p-3 bg-white border rounded-md transition-all duration-200 ${
                    column.isNew ? 'border-green-300 bg-green-50' : ''
                } ${
                    isDragOver
                        ? 'bg-blue-50 border-blue-400 shadow-lg transform scale-105'
                        : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                } ${!isEditing ? 'cursor-move' : ''}`}
            >
                <div className="flex items-center flex-1">
                    {!isEditing && (
                        <svg className={`w-5 h-5 mr-3 transition-colors duration-200 ${
                            isDragOver ? 'text-blue-500' : 'text-gray-400'
                        }`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                        </svg>
                    )}

                    {isEditing ? (
                        <div className="flex items-center flex-1 gap-2">
                            <input
                                type="text"
                                value={editingColumnName}
                                onChange={(e) => setEditingColumnName(e.target.value)}
                                className="flex-1 px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                autoFocus
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') handleSaveEdit(column);
                                    if (e.key === 'Escape') handleCancelEdit();
                                }}
                            />
                            <button
                                onClick={() => handleSaveEdit(column)}
                                className="p-1 text-green-600 hover:text-green-700 transition-colors"
                                title="Save"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button
                                onClick={handleCancelEdit}
                                className="p-1 text-gray-600 hover:text-gray-700 transition-colors"
                                title="Cancel"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    ) : (
                        <>
                            <span className={`font-medium transition-colors duration-200 ${
                                isDragOver ? 'text-blue-900' : 'text-gray-900'
                            }`}>
                                {column.name}
                            </span>
                            <span className={`ml-2 text-sm transition-colors duration-200 ${
                                isDragOver ? 'text-blue-600' : 'text-gray-500'
                            }`}>
                                ({column.cards?.length || 0} cards)
                            </span>
                            {column.isNew && (
                                <span className="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-200 text-green-800">
                                    New
                                </span>
                            )}
                        </>
                    )}

                    {/* Edit and Delete buttons (only if not editing and is board owner) */}
                    {!isEditing && board.is_creator && (
                        <div className="ml-auto flex items-center gap-2">
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleStartEdit(column);
                                }}
                                className="p-1 text-blue-600 hover:text-blue-700 transition-colors"
                                title="Edit column name"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <div className="relative group">
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        if (column.cards?.length === 0) {
                                            handleDeleteColumn(column);
                                        }
                                    }}
                                    disabled={column.cards?.length > 0}
                                    className={`p-1 transition-colors ${
                                        column.cards?.length > 0
                                            ? 'text-gray-400 cursor-not-allowed'
                                            : 'text-red-600 hover:text-red-700'
                                    }`}
                                    title={
                                        column.cards?.length > 0
                                            ? `Cannot delete: column contains ${column.cards.length} card(s). Please move or delete the cards first.`
                                            : 'Delete column'
                                    }
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                {column.cards?.length > 0 && (
                                    <div className="absolute bottom-full right-0 mb-2 hidden group-hover:block w-64 p-2 bg-gray-900 text-white text-xs rounded shadow-lg z-50">
                                        Cannot delete: column contains {column.cards.length} card(s). Please move or delete the cards first.
                                        <div className="absolute top-full right-2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Drop indicator */}
                    {isDragOver && (
                        <span className="ml-auto inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-200 text-blue-800">
                            <svg className="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Drop here
                        </span>
                    )}
                </div>

                {/* Drop position line */}
                {isDragOver && (
                    <div className="absolute inset-x-0 -top-1 h-0.5 bg-blue-400 rounded-full"></div>
                )}
            </div>
        );
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div className="relative top-10 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div className="mt-3">
                    {/* Header */}
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-medium text-gray-900">
                            Edit Board
                        </h3>
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {/* Form */}
                    <form onSubmit={submit}>
                        <div className="mb-4">
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                Board Name *
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter board name"
                                autoFocus
                            />
                            {errors.name && <div className="text-red-500 text-sm mt-1">{errors.name}</div>}
                        </div>

                        <div className="mb-4">
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea
                                id="description"
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows="3"
                                placeholder="Enter board description (optional)"
                            />
                            {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <StatusDropdown
                                value={data.status}
                                onChange={(value) => setData('status', value)}
                                placeholder="Select board status..."
                            />
                            {errors.status && <div className="text-red-500 text-sm mt-1">{errors.status}</div>}
                        </div>

                        {/* Column Management Section */}
                        <div className="mb-6">
                            <div className="flex items-center justify-between mb-3">
                                <label className="block text-sm font-medium text-gray-700">
                                    Columns
                                </label>
                                {board.is_creator && (
                                    <button
                                        type="button"
                                        onClick={handleAddColumn}
                                        className="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors"
                                    >
                                        <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Column
                                    </button>
                                )}
                            </div>
                            <div className="bg-gray-50 rounded-lg p-4">
                                {board.is_creator ? (
                                    <p className="text-sm text-gray-600 mb-4">
                                        <span className="inline-flex items-center">
                                            <svg className="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                            Drag to reorder • Click edit to rename • Click delete to remove
                                        </span>
                                    </p>
                                ) : (
                                    <p className="text-sm text-gray-600 mb-4">
                                        <span className="inline-flex items-center">
                                            <svg className="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Only the board owner can modify columns
                                        </span>
                                    </p>
                                )}
                                <div className="space-y-2">
                                    {columns.map((column, index) => (
                                        <DraggableColumn
                                            key={column.tempId || column.id || `col-${index}`}
                                            column={column}
                                            index={index}
                                        />
                                    ))}
                                </div>
                                {columns.length === 0 && (
                                    <p className="text-center text-gray-500 py-4">
                                        No columns yet. Click "Add Column" to create one.
                                    </p>
                                )}
                            </div>
                            {errors.columns && <div className="text-red-500 text-sm mt-2">{errors.columns}</div>}
                        </div>

                        {/* Actions */}
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                            >
                                Cancel
                            </button>
                            <PrimaryButton type="submit" disabled={processing}>
                                {processing ? 'Saving...' : 'Save Changes'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
