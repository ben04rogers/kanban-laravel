import { Link } from '@inertiajs/react';

export default function Breadcrumb({ items = [] }) {
    if (!items || items.length === 0) {
        return null;
    }

    return (
        <nav className="flex" aria-label="Breadcrumb">
            <ol className="inline-flex items-center space-x-1">
                {items.map((item, index) => (
                    <li key={index} className="inline-flex items-center">
                        {index > 0 && (
                            <svg 
                                className="w-4 h-4 text-gray-400 mx-1" 
                                fill="currentColor" 
                                viewBox="0 0 20 20"
                            >
                                <path 
                                    fillRule="evenodd" 
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" 
                                    clipRule="evenodd" 
                                />
                            </svg>
                        )}
                        
                        {item.href ? (
                            <Link
                                href={item.href}
                                className="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors"
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <span className="text-sm font-medium text-gray-500">
                                {item.label}
                            </span>
                        )}
                    </li>
                ))}
            </ol>
        </nav>
    );
}
