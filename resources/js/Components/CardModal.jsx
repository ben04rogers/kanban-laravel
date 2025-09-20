import { useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import { useState, useEffect } from 'react';
import { Editor } from '@tinymce/tinymce-react';

export default function CardModal({ 
    isOpen, 
    onClose, 
    boardId, 
    columnId, 
    columnName,
    columns = []
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        description: '',
        board_id: boardId,
        board_column_id: columnId || '',
    });

    // Reset form when modal opens/closes
    useEffect(() => {
        if (isOpen) {
            setData('board_column_id', columnId || '');
        } else {
            reset();
        }
    }, [isOpen, columnId]);

    const submit = (e) => {
        e.preventDefault();
        post(route('cards.store'), {
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div className="relative top-10 mx-auto p-6 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div className="mt-3">
                    {/* Header */}
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-medium text-gray-900">
                            {columnName ? `Add Card to ${columnName}` : 'Add New Card'}
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
                        {/* Column Selection - only show if no specific column is selected */}
                        {!columnName && columns.length > 0 && (
                            <div className="mb-4">
                                <label htmlFor="board_column_id" className="block text-sm font-medium text-gray-700 mb-2">
                                    Column *
                                </label>
                                <select
                                    id="board_column_id"
                                    value={data.board_column_id}
                                    onChange={(e) => setData('board_column_id', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Select a column</option>
                                    {columns.map((column) => (
                                        <option key={column.id} value={column.id}>
                                            {column.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.board_column_id && <div className="text-red-500 text-sm mt-1">{errors.board_column_id}</div>}
                            </div>
                        )}

                        <div className="mb-4">
                            <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                                Card Title *
                            </label>
                            <input
                                id="title"
                                type="text"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter card title"
                                autoFocus
                            />
                            {errors.title && <div className="text-red-500 text-sm mt-1">{errors.title}</div>}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <Editor
                                apiKey={import.meta.env.VITE_TINYMCE_API_KEY}
                                value={data.description}
                                onEditorChange={(content) => setData('description', content)}
                                init={{
                                    height: 300,
                                    menubar: false,
                                    plugins: [
                                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                                    ],
                                    toolbar: 'undo redo | blocks | ' +
                                        'bold italic forecolor | alignleft aligncenter ' +
                                        'alignright alignjustify | bullist numlist outdent indent | ' +
                                        'removeformat | help',
                                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
                                }}
                            />
                            {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
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
                                {processing ? 'Creating...' : 'Create Card'}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
