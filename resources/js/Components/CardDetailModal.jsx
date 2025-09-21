import { useForm, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useState, useEffect } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { useToast } from '@/Contexts/ToastContext';

export default function CardDetailModal({ 
    isOpen, 
    onClose, 
    card 
}) {
    const [isEditing, setIsEditing] = useState(false);
    const { success, error } = useToast();
    
    const { data, setData, put, processing, errors, reset } = useForm({
        title: card?.title || '',
        description: card?.description || '',
    });

    // Reset form when modal opens/closes or card changes
    useEffect(() => {
        if (isOpen && card) {
            setData({
                title: card.title,
                description: card.description || '',
            });
            setIsEditing(false);
        } else if (!isOpen) {
            reset();
            setIsEditing(false);
        }
    }, [isOpen, card]);

    // Handle Escape key to close modal
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && isOpen) {
                handleClose();
            }
        };

        if (isOpen) {
            document.addEventListener('keydown', handleEscape);
            return () => document.removeEventListener('keydown', handleEscape);
        }
    }, [isOpen]);

    const submit = (e) => {
        e.preventDefault();
        put(route('cards.update', card.id), {
            onSuccess: () => {
                success(`Card "${data.title}" updated successfully!`, 'Card Updated');
                setIsEditing(false);
            },
            onError: () => {
                error('Failed to update card. Please try again.');
            },
        });
    };

    const handleClose = () => {
        setIsEditing(false);
        onClose();
    };

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this card? This action cannot be undone.')) {
            router.delete(route('cards.destroy', card.id), {
                onSuccess: () => {
                    success(`Card "${card.title}" deleted successfully!`, 'Card Deleted');
                    onClose();
                },
                onError: () => {
                    error('Failed to delete card. Please try again.');
                },
            });
        }
    };

    if (!isOpen || !card) return null;

    return (
        <div 
            className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            onClick={handleClose}
        >
            <div 
                className="relative top-10 mx-auto p-6 border w-full max-w-5xl shadow-lg rounded-md bg-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="mt-3">
                    {/* Header with close button */}
                    <div className="flex items-center justify-between mb-4">
                        {!isEditing && <h1 className="text-2xl font-bold text-gray-900">{card.title}</h1>}
                        {isEditing && <div className="flex-1"></div>}
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {/* Card Content */}
                    <div className="space-y-6">
                        {/* Card Title (when editing) */}
                        {isEditing && (
                            <div>
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    className="w-full px-3 py-2 text-2xl font-bold text-gray-900 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    autoFocus
                                />
                                {errors.title && <div className="text-red-500 text-sm mt-1">{errors.title}</div>}
                            </div>
                        )}

                        {/* Card Meta */}
                        <div className="space-y-4">
                            {/* Assigned User */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Assigned User
                                </label>
                                <div className="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                                    {card.user ? (
                                        <div className="flex items-center space-x-2">
                                            <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {card.user.name.charAt(0).toUpperCase()}
                                            </div>
                                            <span className="text-gray-900">{card.user.name}</span>
                                        </div>
                                    ) : (
                                        <span className="text-gray-400 italic">Unassigned</span>
                                    )}
                                </div>
                            </div>
                            
                            {/* Created Date */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Created Date
                                </label>
                                <div className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600">
                                    {new Date(card.created_at).toLocaleDateString()}
                                </div>
                            </div>
                        </div>

                        {/* Card Description */}
                        <div>
                            <h3 className="text-lg font-medium text-gray-900 mb-2">Description</h3>
                            {isEditing ? (
                                <Editor
                                    apiKey={import.meta.env.VITE_TINYMCE_API_KEY}
                                    value={data.description}
                                    onEditorChange={(content) => setData('description', content)}
                                    init={{
                                        height: 400,
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
                            ) : (
                                <div className="p-4 bg-gray-50 rounded-md border">
                                    {card.description ? (
                                        <div 
                                            className="text-gray-700 prose prose-sm max-w-none"
                                            dangerouslySetInnerHTML={{ __html: card.description }}
                                        />
                                    ) : (
                                        <p className="text-gray-400 italic">No description added</p>
                                    )}
                                </div>
                            )}
                            {errors.description && <div className="text-red-500 text-sm mt-1">{errors.description}</div>}
                        </div>

                        {/* Actions */}
                        <div className="flex justify-between items-center pt-4 border-t border-gray-200">
                            <div className="flex gap-2">
                                {isEditing ? (
                                    <>
                                        <PrimaryButton 
                                            onClick={submit} 
                                            disabled={processing}
                                        >
                                            {processing ? 'Saving...' : 'Save Changes'}
                                        </PrimaryButton>
                                        <button
                                            onClick={() => setIsEditing(false)}
                                            className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                                        >
                                            Cancel
                                        </button>
                                    </>
                                ) : (
                                    <button
                                        onClick={() => setIsEditing(true)}
                                        className="px-4 py-2 text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                    >
                                        Edit Card
                                    </button>
                                )}
                            </div>
                            
                            <DangerButton onClick={handleDelete}>
                                Delete Card
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
