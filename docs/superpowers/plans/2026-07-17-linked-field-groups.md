# Linked Field Groups Implementation Plan

> **For agentic workers:** Inline execution — single file change.

**Goal:** Make parent fields (radio/select with conditional sub-questions) and their children move as a single unit in the form designer.

**Architecture:** Modify Alpine.js `formBuilder()` component in `views/forms/edit.php` — add `getBlock()` helper, rewrite `moveField()` to work with blocks, hide move arrows on child fields.

**Tech Stack:** Alpine.js 3.x (inline in PHP view)

## Global Constraints

- Single file change: `views/forms/edit.php` only
- No backend changes
- No database changes
- Must preserve existing `reorderFields()` logic on init

---

### Task 1: Hide move arrows on child fields + add getBlock() + rewrite moveField()

**Files:**
- Modify: `views/forms/edit.php:31-42` (hide arrows)
- Modify: `views/forms/edit.php:434-445` (getBlock + moveField rewrite)

- [ ] **Step 1: Hide move arrows on child fields**

Wrap the arrow buttons container with `x-show="!field.condicion_campo_padre_id"` so children have no move controls.

```html
<div x-show="!field.condicion_campo_padre_id" class="flex-shrink-0 flex flex-col mr-3 mt-1">
    <button @click="moveField(index, -1)" :disabled="index === 0"
            class="p-0.5 text-gray-400 hover:text-blue-600 disabled:opacity-25 disabled:cursor-not-allowed"
            title="Mover arriba">
        <i class="fas fa-chevron-up text-xs"></i>
    </button>
    <button @click="moveField(index, 1)" :disabled="index === fields.length - 1"
            class="p-0.5 text-gray-400 hover:text-blue-600 disabled:opacity-25 disabled:cursor-not-allowed"
            title="Mover abajo">
        <i class="fas fa-chevron-down text-xs"></i>
    </button>
</div>
```

Replace the old `<div class="flex-shrink-0 flex flex-col mr-3 mt-1">` with the one above (adds `x-show`).

- [ ] **Step 2: Replace `moveField` with block-aware version**

Replace the entire `moveField` method (lines 434-445) with:

```javascript
getBlock(index) {
    const field = this.fields[index];
    if (!field) return { start: index, end: index };

    // If this is a child field, find the parent block
    if (field.condicion_campo_padre_id) {
        const parentIdx = this.fields.findIndex(f =>
            !f.condicion_campo_padre_id &&
            (f.id == field.condicion_campo_padre_id || f.nombre === field.condicion_campo_padre_id)
        );
        if (parentIdx !== -1) return this.getBlock(parentIdx);
        return { start: index, end: index };
    }

    // Parent or standalone — find all consecutive descendants
    const parentKey = field.nombre || field.id;
    if (!parentKey) return { start: index, end: index };

    let end = index;
    const descendantKeys = new Set([parentKey]);
    for (let i = index + 1; i < this.fields.length; i++) {
        const f = this.fields[i];
        if (f.condicion_campo_padre_id && descendantKeys.has(f.condicion_campo_padre_id)) {
            end = i;
            const fKey = f.nombre || f.id;
            if (fKey) descendantKeys.add(fKey);
        } else {
            break;
        }
    }
    return { start: index, end };
},

moveField(index, direction) {
    const block = this.getBlock(index);
    const blockSize = block.end - block.start + 1;

    if (direction === -1 && block.start === 0) return;
    if (direction === 1 && block.end === this.fields.length - 1) return;

    const neighborIdx = direction === -1 ? block.start - 1 : block.end + 1;
    const neighborBlock = this.getBlock(neighborIdx);
    const neighborSize = neighborBlock.end - neighborBlock.start + 1;

    const items = [...this.fields];
    const blockItems = items.splice(block.start, blockSize);
    const neighborItems = direction === -1
        ? items.splice(block.start - blockSize, neighborSize)
        : items.splice(block.start, neighborSize);

    const insertAt = direction === -1
        ? block.start - neighborSize
        : block.start + neighborSize - neighborSize;

    items.splice(insertAt, 0, ...blockItems, ...neighborItems);
    this.fields = items;

    // Adjust editingIndex
    if (this.editingIndex !== null) {
        const wasEditing = this.fields[this.editingIndex];
        if (!wasEditing || wasEditing.nombre !== (this.fields[this.editingIndex]?.nombre)) {
            // Try to find the edited field by its unique properties
            const oldField = this.fields.find(f =>
                f.id === (this.fields[this.editingIndex]?.id) ||
                f.nombre === (this.fields[this.editingIndex]?.nombre)
            );
            if (oldField) {
                this.editingIndex = this.fields.indexOf(oldField);
            }
        }
    }
},
```

- [ ] **Step 3: Verify it works**

1. Load the form designer
2. Create a Radio Button with options and add sub-questions to some options
3. Verify child fields have NO up/down arrows
4. Move the parent field up/down — verify all children move with it as a unit
5. Verify standalone fields (no children) still move normally
6. Verify moving a block past another block works correctly
7. Save and reload — verify reorderFields() still places children correctly
