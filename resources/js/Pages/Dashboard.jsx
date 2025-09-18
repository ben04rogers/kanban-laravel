import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';

export default function Dashboard({ boards }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-semibold">Your Boards</h3>
                                <Link href={route('boards.index')}>
                                    <PrimaryButton>View All Boards</PrimaryButton>
                                </Link>
                            </div>
                            
                            {boards && boards.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {boards.slice(0, 6).map((board) => (
                                        <Link
                                            key={board.id}
                                            href={route('boards.show', board.id)}
                                            className="block p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                                        >
                                            <h4 className="font-medium text-gray-900 mb-2">{board.name}</h4>
                                            {board.description && (
                                                <p className="text-sm text-gray-600 mb-2">{board.description}</p>
                                            )}
                                            <div className="text-xs text-gray-500">
                                                {board.columns?.length || 0} columns • {board.cards?.length || 0} cards
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <p className="text-gray-500 mb-4">No boards yet</p>
                                    <Link href={route('boards.index')}>
                                        <PrimaryButton>Create Your First Board</PrimaryButton>
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
