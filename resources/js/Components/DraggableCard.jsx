import { useEffect, useRef, useState } from 'react';
import { draggable, dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { combine } from '@atlaskit/pragmatic-drag-and-drop/combine';

export default function DraggableCard({ 
    card, 
    onMove, 
    onCardClick,
    isDragging = false 
}) {
    const ref = useRef(null);
    const [isDragOver, setIsDragOver] = useState(false);

    useEffect(() => {
        const element = ref.current;
        if (!element) return;

        return combine(
            draggable({
                element,
                getInitialData: () => ({
                    type: 'card',
                    cardId: card.id,
                    cardTitle: card.title,
                }),
                onDragStart: () => {
                    element.style.opacity = '0.5';
                },
                onDrop: () => {
                    element.style.opacity = '1';
                },
            }),
            dropTargetForElements({
                element,
                getData: ({ input, element }) => ({
                    type: 'card',
                    cardId: card.id,
                }),
                canDrop: ({ source }) => {
                    return source.data.type === 'card' && source.data.cardId !== card.id;
                },
                onDragEnter: () => {
                    setIsDragOver(true);
                },
                onDragLeave: () => {
                    setIsDragOver(false);
                },
                onDrop: ({ source, self }) => {
                    setIsDragOver(false);
                    if (source.data.type === 'card' && source.data.cardId !== card.id) {
                        // Calculate the new position
                        const rect = element.getBoundingClientRect();
                        const isAfter = self.data.clientY > rect.top + rect.height / 2;
                        const newPosition = isAfter ? card.position + 1 : card.position;
                        
                        onMove(source.data.cardId, card.board_column_id, newPosition);
                    }
                },
            })
        );
    }, [card.id, card.title, card.position, card.board_column_id, onMove]);

    const handleClick = (e) => {
        // Only handle click if not dragging
        if (!isDragging && onCardClick) {
            e.preventDefault();
            onCardClick(card);
        }
    };

    return (
        <div
            ref={ref}
            onClick={handleClick}
            className={`bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer ${
                isDragOver ? 'border-blue-400 bg-blue-50' : ''
            } ${isDragging ? 'opacity-50' : ''}`}
        >
            <h4 className="font-medium text-gray-900 mb-2">{card.title}</h4>
            <div className="flex items-center justify-between">
                {card.user ? (
                    <div className="flex items-center space-x-1">
                        <div className="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                            {card.user.name.charAt(0).toUpperCase()}
                        </div>
                        <span className="text-xs text-gray-600">{card.user.name}</span>
                    </div>
                ) : (
                    <span className="text-xs text-gray-400 italic">Unassigned</span>
                )}
                <span className="text-xs text-gray-400">
                    {new Date(card.created_at).toLocaleDateString()}
                </span>
            </div>
        </div>
    );
}
