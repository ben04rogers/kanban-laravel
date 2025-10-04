import { useState, useRef, useEffect } from 'react';

export default function StatusDropdown({ 
    value, 
    onChange, 
    disabled = false, 
    className = '',
    placeholder = "Select status..."
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [highlightedIndex, setHighlightedIndex] = useState(-1);
    const dropdownRef = useRef(null);

    const statusOptions = [
        { value: 'active', label: 'Active', color: 'blue' },
        { value: 'completed', label: 'Completed', color: 'green' },
        { value: 'archived', label: 'Archived', color: 'gray' }
    ];

    const selectedOption = statusOptions.find(option => option.value === value);

    const handleOptionClick = (option) => {
        onChange(option.value);
        setIsOpen(false);
        setHighlightedIndex(-1);
    };

    const handleKeyDown = (e) => {
        if (disabled) return;

        switch (e.key) {
            case 'Enter':
            case ' ':
                e.preventDefault();
                if (isOpen && highlightedIndex >= 0) {
                    handleOptionClick(statusOptions[highlightedIndex]);
                } else {
                    setIsOpen(!isOpen);
                }
                break;
            case 'ArrowDown':
                e.preventDefault();
                if (!isOpen) {
                    setIsOpen(true);
                } else {
                    setHighlightedIndex(prev => 
                        prev < statusOptions.length - 1 ? prev + 1 : 0
                    );
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (isOpen) {
                    setHighlightedIndex(prev => 
                        prev > 0 ? prev - 1 : statusOptions.length - 1
                    );
                }
                break;
            case 'Escape':
                setIsOpen(false);
                setHighlightedIndex(-1);
                break;
        }
    };

    const handleClickOutside = (event) => {
        if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
            setIsOpen(false);
            setHighlightedIndex(-1);
        }
    };

    useEffect(() => {
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const getStatusBadgeClasses = (color) => {
        const colorClasses = {
            blue: 'bg-blue-100 text-blue-800',
            green: 'bg-green-100 text-green-800',
            gray: 'bg-gray-100 text-gray-800'
        };
        return `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${colorClasses[color]}`;
    };

    return (
        <div className={`relative ${className}`} ref={dropdownRef}>
            <div 
                className={`
                    relative w-full px-3 py-2 border border-gray-300 rounded-md 
                    focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer
                    ${disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'}
                `}
                onClick={() => !disabled && setIsOpen(!isOpen)}
                onKeyDown={handleKeyDown}
                tabIndex={disabled ? -1 : 0}
                role="combobox"
                aria-expanded={isOpen}
                aria-haspopup="listbox"
            >
                {selectedOption ? (
                    <div className="flex items-center justify-between">
                        <span className={getStatusBadgeClasses(selectedOption.color)}>
                            {selectedOption.label}
                        </span>
                        <svg 
                            className={`w-4 h-4 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                ) : (
                    <div className="flex items-center justify-between">
                        <span className="text-gray-500">{placeholder}</span>
                        <svg 
                            className={`w-4 h-4 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} 
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                )}
            </div>

            {isOpen && !disabled && (
                <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    {statusOptions.map((option, index) => (
                        <div
                            key={option.value}
                            className={`
                                flex items-center justify-between px-3 py-2 cursor-pointer transition-colors
                                ${highlightedIndex === index 
                                    ? 'bg-blue-50 text-blue-900' 
                                    : 'hover:bg-gray-50 text-gray-900'
                                }
                            `}
                            onClick={() => handleOptionClick(option)}
                            onMouseEnter={() => setHighlightedIndex(index)}
                            role="option"
                            aria-selected={option.value === value}
                        >
                            <span className={getStatusBadgeClasses(option.color)}>
                                {option.label}
                            </span>
                            {option.value === value && (
                                <svg className="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                </svg>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
