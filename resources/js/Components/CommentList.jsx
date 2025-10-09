import { router } from '@inertiajs/react';
import { useToast } from '@/Contexts/ToastContext';
import { useState } from 'react';

export default function CommentList({ comments = [], currentUser }) {
    const { success, error } = useToast();
    const [deletingCommentId, setDeletingCommentId] = useState(null);

    const handleDeleteClick = (commentId) => {
        setDeletingCommentId(commentId);
    };

    const handleCancelDelete = () => {
        setDeletingCommentId(null);
    };

    const handleConfirmDelete = (commentId) => {
        router.delete(route('comments.destroy', commentId), {
            onSuccess: () => {
                success('Comment deleted successfully!');
                setDeletingCommentId(null);
            },
            onError: () => {
                error('Failed to delete comment. Please try again.');
                setDeletingCommentId(null);
            },
        });
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 1) {
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            return `${diffInMinutes} minute${diffInMinutes !== 1 ? 's' : ''} ago`;
        } else if (diffInHours < 24) {
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            return `${diffInHours} hour${diffInHours !== 1 ? 's' : ''} ago`;
        } else if (diffInHours < 168) { // 7 days
            const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
            return `${diffInDays} day${diffInDays !== 1 ? 's' : ''} ago`;
        } else {
            return date.toLocaleDateString();
        }
    };

    if (comments.length === 0) {
        return (
            <div className="text-center py-8 text-gray-500">
                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 className="mt-2 text-sm font-medium text-gray-900">No comments</h3>
                <p className="mt-1 text-sm text-gray-500">Get started by adding a comment.</p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {comments.map((comment) => (
                <div key={comment.id} className="flex space-x-3">
                    <div className="flex-shrink-0">
                        <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                            {comment.user.name.charAt(0).toUpperCase()}
                        </div>
                    </div>
                    <div className="flex-1 min-w-0">
                        <div className="bg-gray-50 rounded-lg px-4 py-3">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center space-x-2">
                                    <p className="text-sm font-medium text-gray-900">
                                        {comment.user.name}
                                    </p>
                                    <span className="text-xs text-gray-500">
                                        {formatDate(comment.created_at)}
                                    </span>
                                </div>
                                {(currentUser.id === comment.user_id || currentUser.id === comment.card?.board?.user_id) && (
                                    <div className="flex items-center gap-2">
                                        {deletingCommentId === comment.id ? (
                                            <>
                                                <span className="text-xs text-gray-600 font-medium">Delete comment?</span>
                                                <button
                                                    onClick={() => handleConfirmDelete(comment.id)}
                                                    className="px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 transition-colors"
                                                >
                                                    Delete
                                                </button>
                                                <button
                                                    onClick={handleCancelDelete}
                                                    className="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition-colors"
                                                >
                                                    Cancel
                                                </button>
                                            </>
                                        ) : (
                                            <button
                                                onClick={() => handleDeleteClick(comment.id)}
                                                className="text-gray-400 hover:text-red-500 transition-colors"
                                                title="Delete comment"
                                            >
                                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                            <div className="mt-2">
                                <p className="text-sm text-gray-700 whitespace-pre-wrap">
                                    {comment.content}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
