import { useForm, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useState, useEffect } from 'react';

export default function CardDetailModal({ 
    isOpen, 
    onClose, 
    card 
}) {
    const [isEditing, setIsEditing] = useState(false);
    
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
                setIsEditing(false);
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
                    onClose();
                }
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
                className="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="mt-3">
                    {/* Header with close button */}
                    <div className="flex items-center justify-between mb-4">
                        <div className="flex-1"></div>
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
                        {/* Card Title */}
                        <div>
                            {isEditing ? (
                                <input
                                    type="text"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    className="w-full text-2xl font-bold text-gray-900 border-none outline-none bg-transparent"
                                    autoFocus
                                />
                            ) : (
                                <h1 className="text-2xl font-bold text-gray-900">{card.title}</h1>
                            )}
                            {errors.title && <div className="text-red-500 text-sm mt-1">{errors.title}</div>}
                        </div>

                        {/* Card Meta */}
                        <div className="gap-4 text-sm text-gray-500">
                            <p>Created by {card.user?.name} on {new Date(card.created_at).toLocaleDateString()}</p>
                        </div>

                        {/* Card Description */}
                        <div>
                            <h3 className="text-lg font-medium text-gray-900 mb-2">Description</h3>
                            {isEditing ? (
                                <textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    rows="6"
                                    placeholder="Add a description..."
                                />
                            ) : (
                                <div className="min-h-24 p-3 bg-gray-50 rounded-md">
                                    {card.description ? (
                                        <p className="text-gray-700 whitespace-pre-wrap">{card.description}</p>
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
