import { useEffect, useRef, useState } from 'react';
import { draggable, dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import { combine } from '@atlaskit/pragmatic-drag-and-drop/combine';

export default function DraggableCard({ 
    card, 
    onMove, 
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

    return (
        <div
            ref={ref}
            className={`bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-grab active:cursor-grabbing ${
                isDragOver ? 'border-blue-400 bg-blue-50' : ''
            } ${isDragging ? 'opacity-50' : ''}`}
        >
            <h4 className="font-medium text-gray-900 mb-2">{card.title}</h4>
            {card.description && (
                <p className="text-sm text-gray-600 mb-2">{card.description}</p>
            )}
            <div className="text-xs text-gray-500">
                Created by {card.user?.name}
            </div>
        </div>
    );
}
