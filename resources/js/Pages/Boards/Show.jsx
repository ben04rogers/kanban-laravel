import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import DangerButton from '@/Components/DangerButton';
import { useState } from 'react';

export default function Show({ board }) {
    const [showEditForm, setShowEditForm] = useState(false);
    
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

    return (
        <AuthenticatedLayout>
            <Head title={board.name} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        <button
                                            onClick={() => setShowEditForm(true)}
                                            className="px-4 py-2 text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
                                        >
                                            Edit Board
                                        </button>
                                    )}
                                    <Link
                                        href={route('boards.index')}
                                        className="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200"
                                    >
                                        Back to Boards
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Kanban Board */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex gap-6 overflow-x-auto pb-4">
                                {board.columns.map((column) => (
                                    <div key={column.id} className="flex-shrink-0 w-80">
                                        <div className="p-4 rounded-lg mb-4 bg-gray-100">
                                            <h3 className="font-semibold text-lg mb-2 text-gray-900">
                                                {column.name}
                                            </h3>
                                            <span className="text-sm text-gray-500">
                                                {column.cards?.length || 0} cards
                                            </span>
                                        </div>
                                        
                                        <div className="space-y-3 min-h-96">
                                            {column.cards && column.cards.length > 0 ? (
                                                column.cards.map((card) => (
                                                    <div 
                                                        key={card.id} 
                                                        className="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                                    >
                                                        <h4 className="font-medium text-gray-900 mb-2">{card.title}</h4>
                                                        {card.description && (
                                                            <p className="text-sm text-gray-600 mb-2">{card.description}</p>
                                                        )}
                                                        <div className="text-xs text-gray-500">
                                                            Created by {card.user?.name}
                                                        </div>
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="text-center text-gray-400 py-8 border-2 border-dashed border-gray-200 rounded-lg">
                                                    No cards yet
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
