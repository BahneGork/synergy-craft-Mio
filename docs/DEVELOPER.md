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
- **Card View** (default) — CSS grid of item cards, each showing ingredients with gathered counters. Right sidebar shows the **Used In** ingredient lookup.
- **Location View** — `<table>` grouped by ingredient for a selected location. Right sidebar swaps to the **Filter Items** item checklist.

**Abyssal Hunt grouping:**
Locations prefixed `Abyssal Hunt Mod:` are collapsed into a single dropdown entry. Selecting it reveals a checkbox panel (one per mod) so the user can include one or both mods. Mod names are extracted dynamically from `craftingData` — adding a third mod requires no code change.

**Persistence:**
- `localStorage` with two keys:
  - `synergy-crafting-progress` — `{ itemName: [number, number, ...] }` (gathered count per ingredient index)
  - `synergy-crafting-shopping` — `[itemName, ...]` (shopping list selection)
- In-memory fallback object used when `localStorage` is blocked (e.g. Safari on `file://`)

---

## Persistence

### How it works

Two `localStorage` keys are written on every state change:

| Key | Format | Written by |
|---|---|---|
| `synergy-crafting-progress` | `{ "Item Name": [gathered0, gathered1, ...] }` | `saveProgress()` via `setIngredientCount()` |
| `synergy-crafting-shopping` | `["Item Name", "Item Name", ...]` | `saveSelected()` via `toggleSelected()` |

Both are read once at startup by `loadState()` and merged into `appState`. All rendering then reads from `appState`, not directly from `localStorage`.

The `progress` array is indexed by ingredient position within that item's `ingredients` array. This means **ingredient order within `craftingData` must never change** for an existing item — reordering ingredients shifts all saved indices and corrupts progress for that item.

### In-memory fallback

`localStorage` access is wrapped in a try/catch. If it throws (Safari on `file://` with strict ITP, or the browser quota is exceeded), the code falls back to a plain JS object (`memStore`). The fallback is transparent to the rest of the code but data is lost on page close.

### Known failure modes

| Cause | Effect | Notes |
|---|---|---|
| User clears cookies / site data | Both keys deleted — all progress lost | No recovery possible |
| File moved or renamed | `localStorage` is scoped to the full `file://` path — new path = new (empty) storage | Old data remains orphaned under the old key; not deleted, just unreachable |
| File opened from a different machine | `localStorage` is per-device, not synced | Export to CSV before switching machines |
| Private / incognito window | `localStorage` is cleared when the window closes | Data survives within the session but not beyond it |
| Safari strict ITP on `file://` | `localStorage.setItem` throws — falls back to `memStore` | Effective result: session-only persistence |
| `localStorage` quota exceeded (~5 MB) | `setItem` throws — same fallback path | Extremely unlikely given the data size |
| Item name changed in `craftingData` | Old key in `progress` is now orphaned; new name starts fresh | Item names are the primary key — treat them as immutable |

### Hosting on a domain

When served over HTTP/HTTPS, `localStorage` is scoped to the **origin** — the combination of protocol, hostname, and port. This has different implications than running from a local file.

**What improves:**
- Safari's ITP restriction does not apply to hosted origins — `localStorage` works reliably without the in-memory fallback
- The storage key is stable as long as the URL doesn't change, regardless of where the file physically lives on the server

**New failure modes introduced by hosting:**

| Cause | Effect | Notes |
|---|---|---|
| Domain changes (e.g. moved to a new host) | Old origin's storage is inaccessible — fresh start | `http://old.com` and `http://new.com` are separate origins |
| HTTP → HTTPS migration | Protocol is part of the origin — treated as a new origin | Always host on HTTPS from the start to avoid this |
| Subdomain change | `crafting.example.com` and `example.com` are different origins | Plan the final URL before sharing with users |
| Port change | `example.com:8080` and `example.com` are different origins | Only relevant for self-hosted setups |
| User visits on a different device | `localStorage` is per-browser, per-device — not synced across users or devices | Each visitor has their own independent save |
| Multiple people using the same hosted URL | Each person's browser has its own isolated `localStorage` | This is correct behaviour — users do not share progress |
| Moving from local file to hosted | `file://` and `https://` are different origins — local progress does not carry over | Advise users to export before switching |

**Key point:** once you choose a URL to host the tool on, treat it as permanent. Any change to the origin (protocol, domain, subdomain, port) creates a new storage slot and leaves all existing user progress unreachable.

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
Builds `#locationTableBody` for Location View. Reads search, class, location filter, and `appState.abyssalMods` / `appState.locExcluded`. Flow:
1. Resolves which location strings to match — for `"Abyssal Hunt"` expands to all checked mod strings; otherwise exact match
2. Collects all matching item×ingredient `pairs` (before exclusion)
3. Populates `locSourceMap` from `pairs`
4. Rebuilds the **Filter Items** checklist in `#locItemChecks` from the unique items in `pairs`
5. Filters `pairs` to `activePairs` by removing `locExcluded` items
6. Groups `activePairs` by ingredient name into a `Map`
7. Renders one `<tr>` per ingredient, sorted alphabetically. Each row: total qty needed, a single gathered counter (value derived from `locSourceMap` as max gathered across all sources), colour-coded ingredient name, comma-separated item list with individual qtys (items turn green via `.loc-item-met` when the counter meets their qty)

### State management

**`appState`**
Central state object:
```js
{
  selectedItems: new Set(),  // item names in the shopping list
  progress: {},              // { itemName: [gathered, gathered, ...] }
  abyssalMods: new Set(),    // which Abyssal Hunt mod names are currently checked
  locExcluded: new Set(),    // item names excluded from Location View totals
  view: 'cards'              // 'cards' | 'location'
}
```

**`locSourceMap`**
Module-level `Map` populated by `renderLocationTable()` before the item exclusion filter. Maps ingredient name → `[{ entry, ing, i }]` for every item that needs it at the current location. Used by `setLocGathered()` to know which per-item progress slots to update, and by `setIngredientCount()` to sync the location table DOM.

**`setIngredientCount(itemName, index, max, value)`**
Updates `appState.progress[itemName][index]`, clamps the value to `[0, max]`, saves to localStorage, and performs a **targeted DOM update** (finds the specific row by `data-item`/`data-index` selectors and toggles `.done` class). Does not trigger a full re-render. Also updates the card's progress counter text and refreshes the shopping list panel. If Location View is active, also updates the corresponding location table counter (derived as the max gathered across all `locSourceMap` sources for that ingredient).

**`setLocGathered(ingName, value)`**
Called by the location table counter `oninput`. Looks up all source items for `ingName` in `locSourceMap`, writes `min(value, item.qty)` to each item's `appState.progress` slot, and performs targeted DOM updates on both the card grid and the location table row (opacity, green item highlights). Calls `saveProgress()` and `updatePanel()`. Does not call `setIngredientCount()` — updates progress directly to avoid circular calls.

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
Toggles `appState.view` between `'cards'` and `'location'`. Shows/hides `#itemsGrid`, `#locationTableWrap`, and `#locationFilterWrap`. Swaps the right sidebar between `#lookupSection` (Used In) and `#locItemSection` (Filter Items). Updates the toggle button label and active state. Calls `renderLocationTable()` when entering Location View.

**`onLocationChange()`**
Called by the location dropdown `onchange`. Shows/hides `#abyssalModWrap` (Abyssal Hunt mod checkboxes), clears `appState.locExcluded`, and calls `renderLocationTable()`.

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
| Ingredients | `.ingredient-row`, `.qty`, `.name-btn`, `.counter-group`, `.counter-input` |
| Colour coding | `.color-orange`, `.color-purple`, `.color-blue` |
| Done state | `.ingredient-row.done` overrides |
| Shopping panel | `.panel-header`, `.panel-content`, `.panel-footer`, `.shop-ingredient-row` |
| Export buttons | `.export-btn-row`, `.export-btn` |
| Ingredient lookup | `.lookup-panel`, `.lookup-header`, `.lookup-item-row` |
| Location view table | `.view-toggle-btn`, `.loc-table`, `.loc-needed`, `.loc-ingredient`, `.loc-items-cell` |
| Location item labels | `.loc-item-label`, `.loc-item-met` (green when gathered ≥ item qty), `.loc-item-sep` |
| Help modal | `.help-modal`, `.help-modal-box`, `.help-modal-header`, `.help-modal-body` |
| Responsive | `@media (max-width: 800px)` overrides |
