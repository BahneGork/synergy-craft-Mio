# Developer Documentation — Masterwork Crafting Ledger

## Architecture Overview

The entire application is a **single HTML file** (`crafting-ledger.html`). There is no build step, no package manager, and no server-side component. Opening the file in any modern browser is sufficient to run it.

**External dependencies (CDN only):**
- Google Fonts — `MedievalSharp` (headers) and `Lora` (body text)
- TransparentTextures — paper fibre background image

**Layout: three columns**
```
┌─────────────────┬──────────────────────────┬──────────────┐
│  Shopping List  │     Main Content         │  Used In /   │
│  (left sidebar) │  sticky header + grid    │  Filter Items│
│  300px fixed    │  OR location table       │  (right      │
│                 │  flex: 1                 │   sidebar)   │
│                 │                          │  220px fixed │
└─────────────────┴──────────────────────────┴──────────────┘
```

**Two views (main content area):**
- **Card View** (default) — CSS grid of item cards, each showing ingredients (qty, name, source location) with a progress badge. No per-ingredient gathered counters — gathering is managed entirely through the gathering ledger. Right sidebar shows the **Used In** ingredient lookup.
- **Gathering Ledger** (Location View) — `<table>` grouped by ingredient for a selected location. Each row has a single editable gathered counter. Right sidebar swaps to the **Filter Items** item checklist.

**Abyssal Hunt grouping:**
Locations prefixed `Abyssal Hunt Mod:` are collapsed into a single dropdown entry. Selecting it reveals a checkbox panel (one per mod) so the user can include one or both mods. Mod names are extracted dynamically from `craftingData` — adding a third mod requires no code change.

**Persistence:**
- `localStorage` with three keys:
  - `synergy-crafting-loc-gathered` — `{ ingredientName: rawNumber }` (primary gathered amounts, set by the gathering ledger)
  - `synergy-crafting-shopping` — `[itemName, ...]` (shopping list selection)
  - `synergy-crafting-progress` — legacy per-item progress array, no longer written by the app; preserved for read compatibility with old saves
- In-memory fallback object used when `localStorage` is blocked (e.g. Safari on `file://`)

---

## Persistence

### How it works

Three `localStorage` keys are managed:

| Key | Format | Written by | Purpose |
|---|---|---|---|
| `synergy-crafting-loc-gathered` | `{ "Ingredient Name": number }` | `saveProgress()` via `setLocGathered()` | Primary gathered amounts (shared pool per ingredient) |
| `synergy-crafting-shopping` | `["Item Name", ...]` | `saveSelected()` via `toggleSelected()` | Shopping list selections |
| `synergy-crafting-progress` | `{ "Item Name": [number, ...] }` | legacy — read only | Old per-item progress from earlier versions |

All keys are read once at startup by `loadState()` and merged into `appState`. All rendering reads from `appState`, not directly from `localStorage`.

`locGathered` is the sole source of truth for "how much of this ingredient has been gathered." The legacy `progress` key is loaded but never written — it only prevents old saves from losing collected data entirely on first load.

### In-memory fallback

`localStorage` access is wrapped in a try/catch. If it throws (Safari on `file://` with strict ITP, or the browser quota is exceeded), the code falls back to a plain JS object. The fallback is transparent to the rest of the code but data is lost on page close.

### Known failure modes

| Cause | Effect | Notes |
|---|---|---|
| User clears cookies / site data | All keys deleted — all progress lost | Use Export Backup to prevent data loss |
| File moved or renamed | `localStorage` is scoped to the full `file://` path — new path = new (empty) storage | Export Backup before moving |
| File opened from a different machine | `localStorage` is per-device, not synced | Use Export Backup to transfer |
| Private / incognito window | `localStorage` is cleared when the window closes | Data survives within the session but not beyond it |
| Safari strict ITP on `file://` | `localStorage.setItem` throws — falls back to in-memory | Session-only persistence |
| Item name changed in `craftingData` | Old shopping list key orphaned; item no longer appears selected | Item names are the shopping list primary key — treat as immutable |

### Backup / Restore

`exportBackup()` downloads a JSON file containing `locGathered` and `selectedItems`. `importBackup()` reads it back, writes both keys to `localStorage`, and re-renders. This is the recommended path for moving the file or recovering from a path change.

### Hosting on a domain

When served over HTTP/HTTPS, `localStorage` is scoped to the **origin**. Safari's ITP restriction does not apply to hosted origins. All the same failure modes around origin changes apply — any change to protocol, domain, subdomain, or port creates a new storage slot.

---

## Data Format

All crafting data lives in the `craftingData` array at the top of the `<script>` block. Each entry follows this shape:

```js
{
  "classes": ["Bard", "Fighter"],          // which classes can craft/equip this item
  "item": "Duergar Mercenary's Steel Rapier",  // unique display name — used as the primary key
  "ingredients": [
    {
      "resource": "36x Druegarsteel Scrap",    // "NNx Name" format — parsed by parseIngredient()
      "location": "Abyssal Hunt Mod: Tricky Reversal"  // source location string
    }
  ]
}
```

**Adding a new item:**
1. Add an entry to `craftingData` following the format above
2. Use `"NNx Ingredient Name"` format for all `resource` strings — the regex `/^(\d+)x\s+(.+)$/` parses these
3. Location strings must be consistent — the location dropdown is derived dynamically, so new unique locations appear automatically
4. Item names must be unique — they are used as shopping list keys

**Intermediate crafted ingredients:**
If an ingredient is itself crafted from other materials (e.g. a fiber made from raw flora), expand it directly into its component raw materials in `craftingData`. Do not list crafted intermediates as ingredients — the tool only tracks raw gathered resources.

**Adding a new ingredient colour:**
Material names are colour-coded in `getColorClass()`. To add a new colour rule, add a `n.includes("material keyword")` check to the appropriate colour branch (or add a new CSS class and branch).

Current colour scheme:
| Colour | CSS class | Examples |
|---|---|---|
| Orange | `.color-orange` | Druegarsteel Scrap, Fallen God's Ore, Abyssal Crystal |
| Purple | `.color-purple` | Mushroom Log, Goristro Hide, Luminescent Darklake Water |
| Blue | `.color-blue` | Terebinth |
| Default (ink) | — | Anything unmatched |

---

## Key Functions

### Data layer

**`parseIngredient(resource)`**
Converts a raw `"NNx Ingredient Name"` string into `{ qty: number, name: string }` using the regex `/^(\d+)x\s+(.+)$/`. Returns `{ qty: 0, name: resource }` as a fallback.

**`parsedCraftingData`**
Derived constant built once at startup. Each entry is a copy of `craftingData` with ingredients expanded to `{ qty, name, location }`. All rendering and filtering operates on this, never on the raw `craftingData`.

**`getColorClass(name)`**
Takes an ingredient name string, lowercases it, and returns a CSS class name (`'color-orange'`, `'color-purple'`, `'color-blue'`, or `''`).

### Rendering

**`renderTable(data)`**
Rebuilds the `#itemsGrid` DOM entirely from a supplied data array (a filtered/sorted subset of `parsedCraftingData`). Reads `appState.locGathered` and `appState.selectedItems` to set ingredient row done states and selection state. Does not use `appState.progress`. Called by `filterData()`.

**`filterData()`**
Reads the search input, class dropdown, and exclusive-class checkbox. Filters `parsedCraftingData`, sorts selected items to the top, then calls `renderTable()`. In the gathering ledger (`appState.view === 'location'`), delegates to `renderLocationTable()` instead. Also controls visibility of the "this class only" label (hidden in location view).

**`renderLocationTable()`**
Builds `#locationTableBody` for the gathering ledger. Reads search, class, location filter, and `appState.abyssalMods` / `appState.locExcluded`. Flow:
1. Resolves which location strings to match — for `"Abyssal Hunt"` expands to all checked mod strings; otherwise exact match
2. Collects all matching item×ingredient `pairs` (before exclusion)
3. Populates `locSourceMap` from all pairs (including excluded items, so `setLocGathered` can find sources even for excluded items)
4. Rebuilds the **Filter Items** checklist in `#locItemChecks` from the unique items in `pairs`
5. Filters `pairs` to `activePairs` by removing `locExcluded` items
6. Groups `activePairs` by ingredient name into a `Map`
7. Renders one `<tr>` per ingredient, sorted alphabetically. Each row: total qty needed, a gathered counter (value from `appState.locGathered[ingName]` if set, otherwise falls back to max of capped per-item progress), colour-coded ingredient name, comma-separated item list with individual qtys (items turn green via `.loc-item-met` when the counter meets their qty)

### State management

**`appState`**
Central state object:
```js
{
  selectedItems: new Set(),  // item names in the shopping list
  progress: {},              // legacy — { itemName: [gathered, ...] } — read from localStorage, never written
  locGathered: {},           // { ingredientName: rawNumber } — primary gathered tracking
  abyssalMods: new Set(),    // which Abyssal Hunt mod names are currently checked
  locExcluded: new Set(),    // item names excluded from gathering ledger totals
  view: 'cards'              // 'cards' | 'location'
}
```

**`locSourceMap`**
Module-level `Map` populated by `renderLocationTable()`. Maps ingredient name → `[{ entry, ing, i }]` for every item that needs it at the current location (including excluded items). Used by `setLocGathered()` to update card DOM states.

**`setLocGathered(ingName, value)`**
Called by the gathering ledger counter `oninput`. Stores the raw entered value in `appState.locGathered[ingName]`, then performs targeted DOM updates:
- Toggles `.done` on all `.ingredient-row[data-ing]` elements in the card grid that match the ingredient
- Updates the "X / N ingredients complete" progress badge on affected cards (derived from `locGathered`)
- Saves via `saveProgress()`, calls `updatePanel()`, and updates location table row visuals (opacity, `.loc-item-met` highlights)

Does not write to `appState.progress`.

**`toggleSelected(itemName)`**
Adds or removes an item from `appState.selectedItems`, saves to localStorage, then calls `filterData()` to trigger a re-render (which re-sorts selected items to the top).

**`loadState()` / `saveProgress()` / `saveSelected()`**
`loadState()` reads all three localStorage keys into `appState` at startup. `saveProgress()` writes both `synergy-crafting-progress` (legacy, currently a no-op write) and `synergy-crafting-loc-gathered`. `saveSelected()` writes `synergy-crafting-shopping`. Save functions are called immediately after any state mutation.

### Shopping list panel

**`buildShoppingList()`**
Iterates over `appState.selectedItems`, looks up each item in `parsedCraftingData`, and aggregates: sums `qty` and unions `locations` per ingredient name. Then sets `collected` for each ingredient from `appState.locGathered[ingName]` (capped at `qty`). Returns `{ ingredientName: { qty, locations, collected } }`.

The `collected` field is derived entirely from `locGathered` — the legacy `progress` fallback is not used.

**`updatePanel()`**
Calls `buildShoppingList()` and renders the aggregated ingredient list into `#shoppingContent`. Sort order: incomplete first, then by location, then alphabetically. For each incomplete ingredient renders a "still needed" count (`.shop-remaining`) above the collected line. Fully gathered ingredients are dimmed and cross-struck. Updates the item count subtitle. Called after every state change that affects the shopping list.

### Right panel

**`showIngredientUses(name)`**
Searches `parsedCraftingData` for all entries containing an ingredient with the given name. Renders a list of matching item names + required quantity into `#lookupContent`. Triggered by clicking any ingredient name button in card view.

### Export

**`exportRows()`**
Calls `buildShoppingList()` and returns sorted rows (same order as `updatePanel`) plus metadata (date, item count). Used by all three export formatters.

**`exportTxt()` / `exportMd()` / `exportCsv()`**
Each calls `exportRows()`, formats the aggregated data into the target format, and calls `downloadFile()`. Guards against empty shopping list with a shake animation on the button.

**`downloadFile(filename, content, mimeType)`**
Creates a `Blob`, generates an object URL, triggers an `<a download>` click, and revokes the URL.

### Backup / Restore

**`exportBackup()`**
Packages `appState.selectedItems` and `appState.locGathered` into a versioned JSON object and calls `downloadFile()` with `application/json`. The JSON includes an `exported` ISO timestamp and `version: 1`.

**`importBackup(input)`**
Reads the selected file via `FileReader`, parses the JSON, validates `version === 1`, then writes `shopping` and `locGathered` back into `appState` and `localStorage`. Calls `filterData()`, `updatePanel()`, and (if in location view) `renderLocationTable()` to re-render from restored state.

### View switching

**`toggleView()`**
Toggles `appState.view` between `'cards'` and `'location'`. Shows/hides `#itemsGrid`, `#locationTableWrap`, and `#locationFilterWrap`. Swaps the right sidebar between `#lookupSection` (Used In) and `#locItemSection` (Filter Items). Updates the toggle button label. Calls `filterData()` when entering the gathering ledger (which hides the exclusive-class label and delegates to `renderLocationTable()`).

**`onLocationChange()`**
Called by the location dropdown `onchange`. Shows/hides `#abyssalModWrap`, clears `appState.locExcluded`, and calls `renderLocationTable()`.

**`toggleAbyssalMod(modName)`**
Adds/removes a mod name from `appState.abyssalMods` and calls `renderLocationTable()`.

**`toggleLocItem(itemName)`**
Adds/removes an item name from `appState.locExcluded` and calls `renderLocationTable()`.

---

## CSS Organisation

All styles live in the single `<style>` block. Organised by component:

| Section | What it covers |
|---|---|
| `:root` variables | `--parchment`, `--ink`, `--gold`, `--crimson`, `--card-bg` |
| Layout | `.ledger-container`, `.main-content`, `.shopping-panel`, `.lookup-panel` |
| Sticky header | `.sticky-header`, `.header-section`, `.controls` |
| Cards | `.items-grid`, `.item-card`, `.card-header`, `.card-body` |
| Ingredients | `.ingredient-row`, `.qty`, `.name-btn` |
| Colour coding | `.color-orange`, `.color-purple`, `.color-blue` |
| Done state | `.ingredient-row.done` overrides |
| Shopping panel | `.panel-header`, `.panel-content`, `.panel-footer`, `.shop-ingredient-row`, `.shop-remaining`, `.shop-collected` |
| Backup buttons | `.backup-btn-row`, `.backup-btn` |
| Export buttons | `.export-btn-row`, `.export-btn` |
| Ingredient lookup | `.lookup-panel`, `.lookup-header`, `.lookup-item-row` |
| Location view table | `.view-toggle-btn`, `.loc-table`, `.loc-needed`, `.loc-ingredient`, `.loc-items-cell`, `.counter-input` |
| Location item labels | `.loc-item-label`, `.loc-item-met` (green when gathered ≥ item qty), `.loc-item-sep` |
| Help modal | `.help-modal`, `.help-modal-box`, `.help-modal-header`, `.help-modal-body` |
| Responsive | `@media (max-width: 800px)` overrides |
