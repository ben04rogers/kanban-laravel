import { useForm, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import { useState, useEffect, useRef } from 'react';
import { useToast } from '@/Contexts/ToastContext';
import { draggable, dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { combine } from '@atlaskit/pragmatic-drag-and-drop/combine';

export default function EditBoardModal({ isOpen, onClose, board }) {
    const { success, error } = useToast();
    const [columns, setColumns] = useState(board.columns || []);
    const [draggedItem, setDraggedItem] = useState(null);
    
    const { data, setData, put, processing, errors, reset } = useForm({
        name: board.name || '',
        description: board.description || '',
        columns: board.columns || [],
    });

    // Reset form when modal opens/closes
    useEffect(() => {
        if (isOpen) {
            setColumns(board.columns || []);
            setData({
                name: board.name || '',
                description: board.description || '',
                columns: board.columns || [],
            });
        }
    }, [isOpen, board]);

    const submit = (e) => {
        e.preventDefault();
        
        // Prepare column data with updated positions
        const columnData = columns.map((col, idx) => ({
            id: col.id,
            position: idx
        }));
        
        // Update form data with columns
        setData('columns', columnData);
        
        put(route('boards.update', board.id), {
            onSuccess: () => {
                // If column order changed, also update column positions
                if (JSON.stringify(columnData) !== JSON.stringify(board.columns.map(col => ({ id: col.id, position: col.position })))) {
                    router.post(route('boards.columns.reorder', board.id), {
                        columns: columnData
                    }, {
                        preserveScroll: true,
                        onSuccess: () => {
                            success(`Board "${data.name}" updated successfully!`, 'Board Updated');
                            onClose();
                        },
                        onError: () => {
                            error('Board updated but failed to update column order. Please try again.');
                        }
                    });
                } else {
                    success(`Board "${data.name}" updated successfully!`, 'Board Updated');
                    onClose();
                }
            },
            onError: () => {
                error('Failed to update board. Please try again.');
            },
        });
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

        return (
            <div
                ref={ref}
                className={`flex items-center p-3 bg-white border rounded-md cursor-move transition-all duration-200 ${
                    isDragOver 
                        ? 'bg-blue-50 border-blue-400 shadow-lg transform scale-105' 
                        : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
                }`}
            >
                <div className="flex items-center flex-1">
                    <svg className={`w-5 h-5 mr-3 transition-colors duration-200 ${
                        isDragOver ? 'text-blue-500' : 'text-gray-400'
                    }`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 8h16M4 16h16" />
                    </svg>
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

                        <div className="mb-6">
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

                        {/* Column Reordering Section */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                Column Order
                            </label>
                            <div className="bg-gray-50 rounded-lg p-4">
                                <p className="text-sm text-gray-600 mb-4">
                                    <span className="inline-flex items-center">
                                        <svg className="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                        Drag and drop columns to reorder them
                                    </span>
                                </p>
                                <div className="space-y-2">
                                    {columns.map((column, index) => (
                                        <DraggableColumn
                                            key={column.id}
                                            column={column}
                                            index={index}
                                        />
                                    ))}
                                </div>
                            </div>
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
