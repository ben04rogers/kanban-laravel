import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import { useToast } from '@/Contexts/ToastContext';

export default function CommentForm({ cardId, currentUser }) {
    const [isExpanded, setIsExpanded] = useState(false);
    const { success, error } = useToast();

    const { data, setData, post, processing, errors, reset } = useForm({
        content: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('comments.store', cardId), {
            onSuccess: () => {
                success('Comment added successfully!');
                reset();
                setIsExpanded(false);
            },
            onError: () => {
                error('Failed to add comment. Please try again.');
            },
        });
    };

    const handleCancel = () => {
        reset();
        setIsExpanded(false);
    };

    return (
        <div className="pt-4">
            <div className="flex space-x-3">
                <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                        {currentUser.name.charAt(0).toUpperCase()}
                    </div>
                </div>
                <div className="flex-1 min-w-0">
                    {!isExpanded ? (
                        <button
                            onClick={() => setIsExpanded(true)}
                            className="w-full text-left px-4 py-3 border border-gray-300 rounded-lg text-gray-500 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            Add a comment...
                        </button>
                    ) : (
                        <form onSubmit={submit} className="space-y-3">
                            <div>
                                <textarea
                                    value={data.content}
                                    onChange={(e) => setData('content', e.target.value)}
                                    placeholder="Write a comment..."
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                    rows={3}
                                    autoFocus
                                />
                                {errors.content && (
                                    <div className="text-red-500 text-sm mt-1">{errors.content}</div>
                                )}
                            </div>
                            <div className="flex justify-end space-x-2">
                                <button
                                    type="button"
                                    onClick={handleCancel}
                                    className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                                    disabled={processing}
                                >
                                    Cancel
                                </button>
                                <PrimaryButton
                                    type="submit"
                                    disabled={processing || !data.content.trim()}
                                >
                                    {processing ? 'Adding...' : 'Add Comment'}
                                </PrimaryButton>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </div>
    );
}
