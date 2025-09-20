import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    return (
        <>
            <Head title="Velocity - Kanban Board" />
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
                <div className="flex flex-col items-center justify-center min-h-screen px-4">
                    <div className="text-center max-w-4xl mx-auto">
                        {/* Logo */}
                        <div className="mb-8">
                            <h1 className="text-6xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                Velocity
                            </h1>
                            <p className="text-xl text-gray-600 mt-2">Streamline Your Workflow</p>
                                    </div>

                        {/* Main Content */}
                        <div className="mb-12">
                            <h2 className="text-4xl font-semibold text-gray-800 mb-6">
                                Organize Your Tasks with Kanban Boards
                                                </h2>
                            <p className="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                                Create, manage, and collaborate on projects with our intuitive Kanban board system. 
                                Drag and drop cards, share boards with your team, and stay organized.
                            </p>
                                        </div>

                        {/* Features */}
                        <div className="grid md:grid-cols-3 gap-8 mb-12">
                            <div className="bg-white rounded-lg p-6 shadow-lg">
                                <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                                    <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                    </div>
                                <h3 className="text-lg font-semibold text-gray-800 mb-2">Create Boards</h3>
                                <p className="text-gray-600">Set up custom Kanban boards for your projects and organize tasks efficiently.</p>
                                    </div>

                            <div className="bg-white rounded-lg p-6 shadow-lg">
                                <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                                    <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                <h3 className="text-lg font-semibold text-gray-800 mb-2">Drag & Drop</h3>
                                <p className="text-gray-600">Move cards between columns with simple drag and drop functionality.</p>
                                    </div>

                            <div className="bg-white rounded-lg p-6 shadow-lg">
                                <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4 mx-auto">
                                    <svg className="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 className="text-lg font-semibold text-gray-800 mb-2">Collaborate</h3>
                                <p className="text-gray-600">Share boards with team members and work together on projects.</p>
                            </div>
                        </div>

                        {/* CTA Buttons */}
                        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            {auth.user ? (
                                <Link
                                    href="/"
                                    className="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    Go to Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('register')}
                                        className="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl"
                                    >
                                        Get Started
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="bg-white text-gray-700 px-8 py-3 rounded-lg font-semibold text-lg border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 shadow-lg hover:shadow-xl"
                                    >
                                        Sign In
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>

                    {/* Footer */}
                    <footer className="mt-16 text-center text-sm text-gray-500">
                        <p>Velocity Kanban Board • Built with Laravel v{laravelVersion}</p>
                    </footer>
                </div>
            </div>
        </>
    );
}