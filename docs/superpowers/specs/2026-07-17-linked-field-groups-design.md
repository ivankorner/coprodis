# Linked Field Groups — Form Designer Reordering

## Problem
When a Radio Button (or Select) field has conditional sub-questions (child fields), moving the parent field with the up/down arrows leaves the children behind, breaking the logical grouping. Child fields must always remain immediately after their parent.

## Current Behavior
- `moveField(index, direction)` swaps a single field with its adjacent neighbor
- Child fields have independent move buttons and can be freely separated from their parent
- `reorderFields()` runs only on init to fix ordering after data load, but does not prevent separation during moves

## Solution
Group parent + descendants into inseparable "blocks" for all move operations.

### 1. Hide move arrows on child fields
The up/down button container gets `x-show="!field.condicion_campo_padre_id"` so children have no move controls — they move implicitly with their parent.

### 2. `getBlock(index) → { block, blockSize, parentIndex }`
Given a field index, returns the contiguous block of fields that must stay together:

- **Standalone field** (no children, not a child): block = `[index]`, size = 1
- **Parent field** (has descendants): scans forward to collect all consecutive descendants (including nested/grandchildren), returns `[parentIdx ... lastDescendantIdx]`
- **Child field** (has `condicion_campo_padre_id`): finds the parent field's index, then returns the parent's full block. This is a safety fallback; children won't have move buttons so this path is only hit if called programmatically.

A field `B` is a descendant of parent `A` if `B.condicion_campo_padre_id` equals `A.nombre` or `A.id`, or if `B`'s parent is itself a descendant of `A` (supports arbitrary nesting depth).

### 3. Modified `moveField(index, direction)`
```
1. Compute block = getBlock(index) for the field at [index]
2. Identify the "neighbor block" adjacent to this block in the given direction
3. Compute neighborBlock = getBlock(neighborIndex) 
4. Splice both blocks out and reinsert in swapped positions
5. If editingIndex falls within either block, relocate editingIndex to the correct new position
```

### Edge Cases
- **Block too large to move** (at top/bottom boundary): move is refused (already handled by existing bounds check)
- **Editing a child field when its parent moves**: the child's editingIndex is adjusted if the parent block moves
- **Removing a child** (via removeField): works normally, no special handling needed — only move is affected

## Files Changed
- `views/forms/edit.php` — exclusively frontend JavaScript changes in the Alpine.js `formBuilder()` component

## Unchanged
- Backend `FormBuilderController` — no changes needed
- Database schema — no changes needed
- Record rendering (`records/create.php`, etc.) — no changes needed
- `addField()`, `updateField()`, `removeField()`, `reorderFields()` — all preserved
