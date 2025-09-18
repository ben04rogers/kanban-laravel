import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';

export default function ShareBoardModal({ 
    isOpen, 
    onClose, 
    boardId 
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [shares, setShares] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    // Load existing shares when modal opens
    useEffect(() => {
        if (isOpen && boardId) {
            loadShares();
        }
    }, [isOpen, boardId]);

    // Search users with debounce
    useEffect(() => {
        if (searchQuery.length >= 2) {
            const timeoutId = setTimeout(() => {
                searchUsers(searchQuery);
            }, 300);
            return () => clearTimeout(timeoutId);
        } else {
            setSearchResults([]);
        }
    }, [searchQuery]);

    const loadShares = async () => {
        try {
            const sharesUrl = `/boards/${boardId}/shares`;
            console.log('Loading shares from:', sharesUrl);
            const response = await fetch(sharesUrl);
            const data = await response.json();
            console.log('Shares response:', data);
            setShares(data.shares || []);
        } catch (error) {
            console.error('Error loading shares:', error);
        }
    };

    const searchUsers = async (query) => {
        setIsSearching(true);
        try {
            console.log('Searching users with query:', query);
            const searchUrl = `/users/search?q=${encodeURIComponent(query)}`;
            console.log('Search URL:', searchUrl);
            const response = await fetch(searchUrl);
            const data = await response.json();
            console.log('Search response:', data);
            setSearchResults(data.users || []);
        } catch (error) {
            console.error('Error searching users:', error);
        } finally {
            setIsSearching(false);
        }
    };

    const shareWithUser = async (user) => {
        setIsLoading(true);
        try {
            console.log('Sharing board with user:', user);
            
            // Use Inertia's router.post which handles CSRF automatically
            router.post(`/boards/${boardId}/shares`, {
                user_id: user.id
            }, {
                onSuccess: (page) => {
                    // The page will refresh with updated data
                    setSearchQuery('');
                    setSearchResults([]);
                    // Reload shares to get the updated list
                    loadShares();
                },
                onError: (errors) => {
                    console.error('Share error:', errors);
                    alert(errors.user_id || 'Error sharing board');
                },
                onFinish: () => {
                    setIsLoading(false);
                }
            });
        } catch (error) {
            console.error('Error sharing board:', error);
            alert('Error sharing board: ' + error.message);
            setIsLoading(false);
        }
    };

    const removeShare = async (shareId) => {
        if (!confirm('Are you sure you want to remove this share?')) {
            return;
        }

        try {
            router.delete(`/boards/${boardId}/shares/${shareId}`, {
                onSuccess: () => {
                    // Reload shares to get the updated list
                    loadShares();
                },
                onError: (errors) => {
                    console.error('Remove share error:', errors);
                    alert('Error removing share');
                }
            });
        } catch (error) {
            console.error('Error removing share:', error);
            alert('Error removing share');
        }
    };

    const handleClose = () => {
        setSearchQuery('');
        setSearchResults([]);
        onClose();
    };

    if (!isOpen) return null;

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
                    {/* Header */}
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="text-lg font-medium text-gray-900">Share Board</h3>
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {/* Search Section */}
                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Search users to share with
                        </label>
                        <div className="relative">
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                placeholder="Type name or email..."
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            {isSearching && (
                                <div className="absolute right-3 top-2.5">
                                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                                </div>
                            )}
                        </div>

                        {/* Search Results */}
                        {searchResults.length > 0 && (
                            <div className="mt-2 border border-gray-200 rounded-md max-h-48 overflow-y-auto">
                                {searchResults.map((user) => (
                                    <div
                                        key={user.id}
                                        className="flex items-center justify-between p-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">{user.name}</div>
                                            <div className="text-sm text-gray-500">{user.email}</div>
                                        </div>
                                        <button
                                            onClick={() => shareWithUser(user)}
                                            disabled={isLoading}
                                            className="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            {isLoading ? 'Sharing...' : 'Share'}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Current Shares */}
                    <div>
                        <h4 className="text-md font-medium text-gray-900 mb-3">Currently shared with</h4>
                        {shares.length === 0 ? (
                            <p className="text-gray-500 text-sm">No users have been shared this board yet.</p>
                        ) : (
                            <div className="space-y-2">
                                {shares.map((share) => (
                                    <div
                                        key={share.id}
                                        className="flex items-center justify-between p-3 bg-gray-50 rounded-md"
                                    >
                                        <div>
                                            <div className="font-medium text-gray-900">{share.user.name}</div>
                                            <div className="text-sm text-gray-500">{share.user.email}</div>
                                        </div>
                                        <DangerButton
                                            onClick={() => removeShare(share.id)}
                                            className="px-3 py-1 text-sm"
                                        >
                                            Remove
                                        </DangerButton>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="flex justify-end mt-6 pt-4 border-t border-gray-200">
                        <button
                            onClick={handleClose}
                            className="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
