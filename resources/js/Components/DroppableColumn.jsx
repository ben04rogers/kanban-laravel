import { useEffect, useRef, useState } from 'react';
import { dropTargetForElements } from '@atlaskit/pragmatic-drag-and-drop/element/adapter';
import DraggableCard from './DraggableCard';

export default function DroppableColumn({ 
    column, 
    onCardMove, 
    onCardMoveToColumn 
}) {
    const ref = useRef(null);
    const [isDragOver, setIsDragOver] = useState(false);

    useEffect(() => {
        const element = ref.current;
        if (!element) return;

        return dropTargetForElements({
            element,
            getData: ({ input, element }) => ({
                type: 'column',
                columnId: column.id,
            }),
            canDrop: ({ source }) => {
                return source.data.type === 'card';
            },
            onDragEnter: () => {
                setIsDragOver(true);
            },
            onDragLeave: () => {
                setIsDragOver(false);
            },
            onDrop: ({ source }) => {
                setIsDragOver(false);
                if (source.data.type === 'card') {
                    // Move card to this column at the end
                    const newPosition = column.cards ? column.cards.length : 0;
                    onCardMoveToColumn(source.data.cardId, column.id, newPosition);
                }
            },
        });
    }, [column.id, column.cards, onCardMoveToColumn]);

    const handleCardMove = (cardId, newPosition) => {
        onCardMove(cardId, column.id, newPosition);
    };

    return (
        <div 
            ref={ref}
            className={`flex-shrink-0 w-80 ${
                isDragOver ? 'bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg' : ''
            }`}
        >
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
                        <DraggableCard
                            key={card.id}
                            card={card}
                            onMove={handleCardMove}
                        />
                    ))
                ) : (
                    <div className="text-center text-gray-400 py-8 border-2 border-dashed border-gray-200 rounded-lg">
                        No cards yet
                    </div>
                )}
            </div>
        </div>
    );
}
