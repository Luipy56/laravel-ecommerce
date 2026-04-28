import React from 'react';
import {
  DndContext,
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import {
  SortableContext,
  arrayMove,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

function SortableColumnRow({ colId, label, checked, onToggle, dragLabel }) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: colId,
  });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };
  return (
    <div
      ref={setNodeRef}
      style={style}
      className={`flex items-start gap-2 rounded-lg border border-base-200 bg-base-100 p-2 ${
        isDragging ? 'z-10 opacity-60 shadow-lg' : ''
      }`}
    >
      <button
        type="button"
        className="btn btn-ghost btn-square btn-sm shrink-0 cursor-grab touch-none active:cursor-grabbing"
        aria-label={dragLabel}
        {...attributes}
        {...listeners}
      >
        <span className="select-none text-base-content/50" aria-hidden="true">
          ⋮⋮
        </span>
      </button>
      <label className="label min-w-0 flex-1 cursor-pointer items-start justify-start gap-2 py-0">
        <input
          type="checkbox"
          className="checkbox checkbox-sm checkbox-primary mt-0.5 shrink-0"
          checked={checked}
          onChange={(e) => onToggle(colId, e.target.checked)}
        />
        <span className="label-text min-w-0 flex-1 text-sm">{label}</span>
      </label>
    </div>
  );
}

/**
 * Sortable checklist for one admin list table (visibility + order).
 */
export default function AdminIndexColumnsFieldset({
  tableId,
  cols,
  title,
  columnPrefs,
  setColumnPrefs,
  columnOrder,
  setColumnOrder,
  t,
}) {
  const ids = columnOrder[tableId]?.length ? columnOrder[tableId] : cols.map((c) => c.id);
  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 8 } }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  const handleDragEnd = (event) => {
    const { active, over } = event;
    if (!over || active.id === over.id) {
      return;
    }
    setColumnOrder((prev) => {
      const items = [...(prev[tableId] ?? [])];
      const oldIndex = items.indexOf(String(active.id));
      const newIndex = items.indexOf(String(over.id));
      if (oldIndex < 0 || newIndex < 0) {
        return prev;
      }
      return { ...prev, [tableId]: arrayMove(items, oldIndex, newIndex) };
    });
  };

  const onToggle = (colId, checked) => {
    setColumnPrefs((p) => ({
      ...p,
      [tableId]: { ...p[tableId], [colId]: checked },
    }));
  };

  return (
    <fieldset className="fieldset border border-base-200 rounded-box min-w-0 space-y-3 p-4">
      <legend className="fieldset-legend text-base font-semibold">{title}</legend>
      <p className="text-xs text-base-content/60">{t('admin.settings.index_columns_order_hint')}</p>
      <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
        <SortableContext items={ids} strategy={verticalListSortingStrategy}>
          <div className="flex flex-col gap-2">
            {ids.map((colId) => {
              const col = cols.find((c) => c.id === colId);
              if (!col) {
                return null;
              }
              return (
                <SortableColumnRow
                  key={colId}
                  colId={colId}
                  label={t(col.labelKey)}
                  checked={columnPrefs[tableId]?.[colId] !== false}
                  onToggle={onToggle}
                  dragLabel={t('admin.settings.column_drag_handle')}
                />
              );
            })}
          </div>
        </SortableContext>
      </DndContext>
    </fieldset>
  );
}
