import { useState, useRef, useEffect } from 'react';

export default function ColumnDropdown({
    columns = [],
    selectedColumnId = null,
    onSelect,
    placeholder = "Select a column",
    className = "",
    disabled = false
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [highlightedIndex, setHighlightedIndex] = useState(-1);
    const dropdownRef = useRef(null);

    // Get selected column object
    const selectedColumn = columns.find(col => col.id === selectedColumnId);

    // Handle clicking outside to close dropdown
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
                setHighlightedIndex(-1);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Handle keyboard navigation
    const handleKeyDown = (e) => {
        if (!isOpen) {
            if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === ' ') {
                e.preventDefault();
                setIsOpen(true);
                setHighlightedIndex(selectedColumn ? columns.findIndex(col => col.id === selectedColumnId) : 0);
            }
            return;
        }

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                setHighlightedIndex(prev =>
                    prev < columns.length - 1 ? prev + 1 : prev
                );
                break;
            case 'ArrowUp':
                e.preventDefault();
                setHighlightedIndex(prev => prev > 0 ? prev - 1 : prev);
                break;
            case 'Enter':
            case ' ':
                e.preventDefault();
                if (highlightedIndex >= 0 && columns[highlightedIndex]) {
                    handleColumnSelect(columns[highlightedIndex]);
                }
                break;
            case 'Escape':
                setIsOpen(false);
                setHighlightedIndex(-1);
                break;
        }
    };

    const handleColumnSelect = (column) => {
        onSelect(column.id);
        setIsOpen(false);
        setHighlightedIndex(-1);
    };

    const handleToggle = () => {
        if (!disabled) {
            setIsOpen(!isOpen);
            if (!isOpen && selectedColumn) {
                setHighlightedIndex(columns.findIndex(col => col.id === selectedColumnId));
            }
        }
    };

    return (
        <div className={`relative ${className}`} ref={dropdownRef}>
            <div
                className={`
                    relative w-full px-3 py-2 border border-gray-300 rounded-md
                    focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer
                    ${disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'}
                `}
                onClick={handleToggle}
                onKeyDown={handleKeyDown}
                tabIndex={disabled ? -1 : 0}
                role="combobox"
                aria-expanded={isOpen}
                aria-haspopup="listbox"
            >
                <div className="flex items-center justify-between">
                    <span className={selectedColumn ? 'text-gray-900' : 'text-gray-500'}>
                        {selectedColumn ? selectedColumn.name : placeholder}
                    </span>
                    <svg
                        className={`w-5 h-5 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`}
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {isOpen && !disabled && (
                <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    {columns.length > 0 ? (
                        columns.map((column, index) => (
                            <div
                                key={column.id}
                                className={`
                                    flex items-center justify-between px-3 py-2 cursor-pointer transition-colors
                                    ${highlightedIndex === index
                                        ? 'bg-blue-50 text-blue-900'
                                        : 'hover:bg-gray-50 text-gray-900'
                                    }
                                    ${selectedColumnId === column.id ? 'font-medium' : ''}
                                `}
                                onClick={() => handleColumnSelect(column)}
                                onMouseEnter={() => setHighlightedIndex(index)}
                                role="option"
                                aria-selected={selectedColumnId === column.id}
                            >
                                <span>{column.name}</span>
                                {selectedColumnId === column.id && (
                                    <svg className="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                    </svg>
                                )}
                            </div>
                        ))
                    ) : (
                        <div className="px-3 py-2 text-gray-500 text-sm">
                            No columns available
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
