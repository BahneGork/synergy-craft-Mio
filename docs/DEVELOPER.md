# Developer Documentation — Masterwork Crafting Ledger

## Architecture Overview

The entire application is a **single HTML file** (`crafting-ledger.html`). There is no build step, no package manager, and no server-side component. Opening the file in any modern browser is sufficient to run it.

**External dependencies (CDN only):**
- Google Fonts — `MedievalSharp` (headers) and `Lora` (body text)
- TransparentTextures — paper fibre background image

**Layout: three columns**
```
┌─────────────────┬──────────────────────────┬──────────────┐
│  Shopping List  │     Main Content         │  Used In     │
│  (left sidebar) │  sticky header + grid    │  (right      │
│  300px fixed    │  OR location table       │   sidebar)   │
│                 │  flex: 1                 │  220px fixed │
└─────────────────┴──────────────────────────┴──────────────┘
```

**Two views (main content area):**
- **Card View** (default) — CSS grid of item cards, each showing ingredients with gathered counters
- **Location View** — flat `<table>` of ingredients for a selected location, toggled by a button in the controls row

**Persistence:**
- `localStorage` with two keys:
  - `synergy-crafting-progress` — `{ itemName: [number, number, ...] }` (gathered count per ingredient index)
  - `synergy-crafting-shopping` — `[itemName, ...]` (shopping list selection)
- In-memory fallback object used when `localStorage` is blocked (e.g. Safari on `file://`)

---

## Data Format

All crafting data lives in the `craftingData` array at the top of the `<script>` block. Each entry follows this shape:

```js
{
  "classes": ["Bard", "Fighter"],  // which classes can craft/equip this item
  "item": "Duergar Mercenary's Steel Rapier",  // unique display name — used as the primary key
  "ingredients": [
    {
      "resource": "36x Druegarsteel Scrap",  // "NNx Name" format — parsed by parseIngredient()
      "location": "Abyssal Hunt Mod: Tricky Reversal"  // source location string
    }
  ]
}
```

**Adding a new item:**
1. Add an entry to `craftingData` following the format above
2. Use `"NNx Ingredient Name"` format for all `resource` strings — the regex `/^(\d+)x\s+(.+)$/` parses these
3. Location strings must be consistent — the location dropdown is derived dynamically, so new unique locations appear automatically
4. Item names must be unique — they are used as localStorage keys for progress tracking

**Adding a new ingredient colour:**
Material names are colour-coded in `getColorClass()`. To add a new colour rule, add a `n.includes("material keyword")` check to the appropriate colour branch (or add a new CSS class and branch).

Current colour scheme:
| Colour | CSS class | Examples |
|---|---|---|
| Orange | `.color-orange` | Druegarsteel Scrap, Fallen God's Ore, Abyssal Crystal |
| Purple | `.color-purple` | Mushroom Log, Goristro Hide, Luminescent Darklake Water |
| Blue | `.color-blue` | Terebinth |
| Default (ink) | — | Underdark Fiber, anything unmatched |

---

## Key Functions

### Data layer

**`parseIngredient(resource)`**
Converts a raw `"NNx Ingredient Name"` string into `{ qty: number, name: string }` using the regex `/^(\d+)x\s+(.+)$/`. Returns `{ qty: 0, name: resource }` as a fallback (should never trigger with valid data).

**`parsedCraftingData`**
Derived constant built once at startup. Each entry is a copy of `craftingData` with ingredients expanded to `{ qty, name, location }`. All rendering and filtering operates on this, never on the raw `craftingData`.

**`getColorClass(name)`**
Takes an ingredient name string, lowercases it, and returns a CSS class name (`'color-orange'`, `'color-purple'`, `'color-blue'`, or `''`). Applied directly to ingredient name elements.

### Rendering

**`renderTable(data)`**
Rebuilds the `#itemsGrid` DOM entirely from a supplied data array (a filtered/sorted subset of `parsedCraftingData`). Reads `appState.progress` and `appState.selectedItems` to restore counter values and selection state. Called by `filterData()` and `resetProgress()`.

**`filterData()`**
Reads the search input and class dropdown, filters `parsedCraftingData`, sorts selected items to the top, then calls `renderTable()`. In Location View (`appState.view === 'location'`), delegates to `renderLocationTable()` instead.

**`renderLocationTable()`**
Builds the flat `#locationTableBody` for Location View. Reads search, class, and location filters. Produces one `<tr>` per item×ingredient pair matching the selected location, sorted alphabetically by ingredient name. Reuses `setIngredientCount()` for gathered counter inputs.

### State management

**`appState`**
Central state object:
```js
{
  selectedItems: new Set(),  // item names in the shopping list
  progress: {},              // { itemName: [gathered, gathered, ...] }
  view: 'cards'              // 'cards' | 'location'
}
```

**`setIngredientCount(itemName, index, max, value)`**
Updates `appState.progress[itemName][index]`, clamps the value to `[0, max]`, saves to localStorage, and performs a **targeted DOM update** (finds the specific row by `data-item`/`data-index` selectors and toggles `.done` class). Does not trigger a full re-render. Also updates the card's progress counter text and refreshes the shopping list panel.

**`toggleSelected(itemName)`**
Adds or removes an item from `appState.selectedItems`, saves to localStorage, then calls `filterData()` to trigger a re-render (which re-sorts selected items to the top).

**`loadState()` / `saveProgress()` / `saveSelected()`**
Read/write `appState.progress` and `appState.selectedItems` to/from `localStorage`. `loadState()` is called once at startup. The save functions are called immediately after any state mutation.

### Shopping list panel

**`buildShoppingList()`**
Iterates over `appState.selectedItems`, looks up each item in `parsedCraftingData`, and aggregates ingredients: sums `qty`, unions `locations` (as a `Set`), and sums `collected` (capped per ingredient at its needed qty). Returns `{ ingredientName: { qty, locations, collected } }`.

**`updatePanel()`**
Calls `buildShoppingList()` and renders the aggregated ingredient list into `#shoppingContent`. Updates the item count subtitle. Shows an empty state message if no items are selected. Called after every state change that affects the shopping list.

### Right panel

**`showIngredientUses(name)`**
Searches `parsedCraftingData` for all entries containing an ingredient with the given name. Renders a list of matching item names + required quantity into `#lookupContent`. Triggered by clicking any ingredient name button.

### Export

**`exportTxt()` / `exportMd()` / `exportCsv()`**
Each calls `buildShoppingList()`, formats the aggregated data into the target format, and calls `downloadFile()`. Guards against empty shopping list with a shake animation on the button. Exported data includes: ingredient name, total needed, total gathered, source locations.

**`downloadFile(filename, content, mimeType)`**
Creates a `Blob`, generates an object URL, triggers an `<a download>` click, and revokes the URL. Works on both `file://` and hosted origins.

### View switching

**`toggleView()`**
Toggles `appState.view` between `'cards'` and `'location'`. Shows/hides `#itemsGrid`, `#locationTableWrap`, and `#locationFilterWrap`. Updates the toggle button label and active state.

---

## CSS Organisation

All styles live in the single `<style>` block. Organised by component:

| Section | What it covers |
|---|---|
| `:root` variables | `--parchment`, `--ink`, `--gold`, `--crimson`, `--card-bg` |
| Layout | `.ledger-container`, `.main-content`, `.shopping-panel`, `.lookup-panel` |
| Sticky header | `.sticky-header`, `.header-section`, `.controls` |
| Cards | `.items-grid`, `.item-card`, `.card-header`, `.card-body` |
| Ingredients | `.ingredient-row`, `.qty`, `.name-btn`, `.counter-group`, `.counter-input` |
| Colour coding | `.color-orange`, `.color-purple`, `.color-blue` |
| Done state | `.ingredient-row.done` overrides |
| Shopping panel | `.panel-header`, `.panel-content`, `.panel-footer`, `.shop-ingredient-row` |
| Export buttons | `.export-btn-row`, `.export-btn` |
| Ingredient lookup | `.lookup-panel`, `.lookup-header`, `.lookup-item-row` |
| Location view | `.view-toggle-btn`, `.loc-table`, `.loc-needed`, `.loc-ingredient` |
| Help modal | `.help-modal`, `.help-modal-box`, `.help-modal-header`, `.help-modal-body` |
| Responsive | `@media (max-width: 800px)` overrides |
