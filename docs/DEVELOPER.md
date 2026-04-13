# Developer Documentation — Masterwork Crafting Ledger

## Architecture Overview

The entire application is a **single HTML file** (`crafting-ledger.html`). There is no build step, no package manager, and no server-side component. Opening the file in any modern browser is sufficient to run it.

**External dependencies (CDN only):**
- Google Fonts — `MedievalSharp` (headers) and `Lora` (body text)
- TransparentTextures — paper fibre background image

**Layout: three columns (desktop)**
```
┌─────────────────┬──────────────────────────┬──────────────┐
│  Order List     │     Main Content         │  Used In /   │
│  (left sidebar) │  sticky header + grid    │  Filter Items│
│  300px fixed    │  OR location table       │  (right      │
│                 │  OR inventory table      │   sidebar)   │
│                 │  flex: 1                 │  220px fixed │
└─────────────────┴──────────────────────────┴──────────────┘
```

**Mobile layout (≤800px):**
- Three-column layout collapses to a single column (flex-direction: column)
- Right sidebar (Used In / Filter Items) is hidden; ingredient clicks open a slide-up modal instead
- Shopping panel stacks above the main content; its body is collapsible
- Item cards are collapsed by default; the item title is the toggle

**Three views (main content area):**
- **Card View** (default) — CSS grid of item cards. Each card has a front face (ingredients + progress) and a back face (production chain). No per-ingredient gathered counters on the front — gathering is managed through the gathering ledger.
- **Gathering Ledger** (Location View) — `<table>` grouped by ingredient for a selected location. Each row has a single editable gathered counter. Right sidebar swaps to the **Filter Items** item checklist.
- **All Resources** (Inventory View) — flat table of every ingredient across all recipes, with gathered amounts.

**Abyssal Hunt grouping:**
Locations prefixed `Abyssal Hunt Mod:` are collapsed into a single dropdown entry. Selecting it reveals a checkbox panel (one per mod). Mod names are extracted dynamically from `craftingData` — adding a third mod requires no code change.

**Persistence:**
- `localStorage` with three keys:
  - `synergy-crafting-loc-gathered` — `{ ingredientName: rawNumber }`
  - `synergy-crafting-shopping` — `[itemName, ...]`
  - `synergy-crafting-craft-qty` — `{ itemName: number }` (Craft Planner quantities)
  - `synergy-crafting-progress` — legacy per-item progress array (read only, never written)
- In-memory fallback object used when `localStorage` is blocked

---

## Persistence

### localStorage keys

| Key | Format | Written by | Purpose |
|---|---|---|---|
| `synergy-crafting-loc-gathered` | `{ "Ingredient Name": number }` | `saveProgress()` | Primary gathered amounts |
| `synergy-crafting-shopping` | `["Item Name", ...]` | `saveSelected()` | Shopping list selections |
| `synergy-crafting-craft-qty` | `{ "Item Name": number }` | `saveCraftQty()` | Craft Planner quantities |
| `synergy-crafting-progress` | `{ "Item Name": [number, ...] }` | legacy — read only | Old per-item progress |

All keys are read once at startup by `loadState()` and merged into `appState`. All rendering reads from `appState`.

### In-memory fallback

`localStorage` access is wrapped in a try/catch. If it throws (Safari on `file://`, quota exceeded), the code falls back to a plain JS object. Transparent to the rest of the code but data is lost on page close.

### Backup / Restore

`exportBackup()` packages `selectedItems`, `locGathered`, and `craftQty` into a versioned JSON object (`version: 1`). `importBackup()` reads it back, writes all three to `appState` and `localStorage`, and re-renders.

---

## Data Format

### craftingData

Hardcoded array at the top of the `<script>` block. Each entry:

```js
{
  "classes": ["Bard", "Fighter"],
  "item": "Duergar Mercenary's Steel Rapier",   // unique — used as primary key
  "ingredients": [
    {
      "resource": "36x Druegarsteel Scrap",      // "NNx Name" — parsed by parseIngredient()
      "location": "Abyssal Hunt Mod: Tricky Reversal"
    }
  ]
}
```

**Adding a new item:**
1. Add an entry to `craftingData`
2. Use `"NNx Ingredient Name"` for all `resource` strings
3. Location strings must be consistent — the dropdown is built dynamically
4. Item names must be unique — they are the shopping list primary key

**Ingredients are pre-expanded raw materials.** Do not list crafted intermediates; expand them to their base components. The production chain view (card back face) is loaded separately from the live MW Recipes Google Sheet.

### MW Recipes Sheet

Loaded at startup by `loadMWRecipes()` from a separate Google Sheet (GID `1562621832`). Parsed by `parseMWRecipes()` into `intermediateRecipes: { itemName: [{ name, qty }] }`. Used only by `buildProductionChain()` for the card flip view. Does not affect the shopping list or gathering ledger — those use `craftingData`.

**Important:** the MW sheet contains typo product names (e.g. `Purifierd Darklake Water`). `parseMWRecipes()` strips the leading quantity prefix (`"3x "`) from product cells before normalising through `normKey()`. If the chain shows wrong data, check for new product name typos in the sheet.

**Yield table** (`RECIPE_YIELD`):

| Item | Yield per run |
|---|---|
| Purified Darklake Water | 3 |
| Mushroom Lumber | 4 |
| Hardened Mushroom | 2 |
| Living Fungi | 3 |
| Marilith Charm | 4 |
| Unknown Godsteel | 3 |
| Lolthbead | 4 |
| Soul Bead | 2 |
| Lacquered Mushroom | 2 |
| Lacquered Goristro Leather | 2 |
| Spool of Marilithsilk | 2 |
| Dreamer's Incense | 3 |
| Dreamer's Medical Tea | 3 |
| Everything else | 1 |

---

## Key Functions

### Data layer

**`parseIngredient(resource)`**  
Converts `"NNx Ingredient Name"` → `{ qty: number, name: string }`. Fallback: `{ qty: 0, name: resource }`.

**`parsedCraftingData`**  
Built once at startup from `craftingData`. Each entry has ingredients expanded to `{ qty, name, location }`. All rendering reads this, never `craftingData` directly.

**`normKey(str)`**  
Normalises ingredient/item name strings: converts curly quotes to straight quotes, collapses multiple spaces. Used as a consistent key for `locGathered` and `intermediateRecipes` lookups.

**`parseMWRecipes(csvText)`**  
Parses the MW Recipes CSV. Strips `"Nx "` prefix from product cells, then applies `normKey()`. Returns `{ normalisedProductName: [{ name, qty }] }`.

**`buildProductionChain(itemName, qtyNeeded, visited)`**  
Recursively resolves the full production chain for an item. Uses `intermediateRecipes` and `RECIPE_YIELD`. Returns a tree node: `{ name, qtyNeeded, isRaw, runsNeeded, yieldPerRun, produced, spare, children }`. `visited` prevents infinite loops on circular references.

### Rendering

**`renderTable(data)`**  
Rebuilds `#itemsGrid` from a filtered/sorted subset of `parsedCraftingData`. Each card is a `div.item-card[data-item]` containing a `.card-inner` with `.card-front` and `.card-back`. Reads `appState.locGathered` and `appState.selectedItems`.

**`flipCard(itemName)`**  
Toggles `.flipped` on the card's `.card-inner`. On first flip, calls `buildProductionChain()` and `renderChainTree()` to populate `.chain-content`. Subsequent flips reuse the cached HTML.

**`renderChainTree(node, depth)`**  
Converts a `buildProductionChain()` tree node into HTML. Raw materials get `.chain-raw` + a "raw" badge; crafted intermediates get `.chain-crafted` + run/yield info. Indented via CSS `--depth` custom property.

**`filterData()`**  
Reads search input, class dropdown, and exclusive-class checkbox. Filters `parsedCraftingData`, sorts selected items to the top, calls `renderTable()`. In location view delegates to `renderLocationTable()`; in inventory view delegates to `renderInventoryTable()`.

**`renderLocationTable()`**  
Builds `#locationTableBody`. Resolves location strings (including Abyssal Hunt mod expansion), collects matching ingredient pairs, populates `locSourceMap`, rebuilds the Filter Items checklist, filters by `locExcluded`, groups by ingredient, renders one `<tr>` per ingredient.

**`renderInventoryTable()`**  
Builds the All Resources table. Aggregates every ingredient across all of `parsedCraftingData` with gathered amounts from `appState.locGathered`.

### State management

**`appState`**
```js
{
  selectedItems: new Set(),  // item names in the shopping list
  progress: {},              // legacy — read from localStorage, never written
  locGathered: {},           // { ingredientName: rawNumber } — primary gathered tracking
  abyssalMods: new Set(),    // which Abyssal Hunt mods are checked
  locExcluded: new Set(),    // items excluded from gathering ledger totals
  view: 'cards',             // 'cards' | 'location' | 'inventory'
  panelMode: 'shop',         // 'shop' | 'craft'
  craftQty: {}               // { itemName: number } — Craft Planner quantities
}
```

**`setLocGathered(ingName, value)`**  
Stores raw value in `appState.locGathered`, performs targeted DOM updates (`.done` on ingredient rows, progress counters on affected cards), saves, calls `updatePanel()`, updates location table visuals.

**`toggleSelected(itemName)`**  
Adds/removes from `appState.selectedItems`, saves, calls `filterData()` to re-sort.

**`setCraftQty(itemName, qty)`**  
Updates `appState.craftQty[itemName]`, saves, re-renders the craft panel if active.

### Shopping list panel

**`buildShoppingList()`**  
Aggregates ingredients across `selectedItems`. Returns `{ ingredientName: { qty, locations, collected } }`. `collected` is capped at `qty` per ingredient from `locGathered`.

**`buildCraftList()`**  
Similar to `buildShoppingList()` but multiplies each ingredient qty by `craftQty[itemName]` and returns `{ needed, have }` without capping — used for the feasibility check.

**`updatePanel()`**  
Calls `buildShoppingList()` (shop mode) or `renderCraftPanel()` (craft mode) and renders into `#shoppingContent`. Sort order: incomplete first → by location → alphabetically.

**`renderCraftPanel()`**  
Renders two sections: craft order inputs (item name + qty input per selected item) and a feasibility table (ingredient → needed → have → ✓/✗). Footer shows "All ingredients covered" or "Short on N ingredient(s)".

**`togglePanelMode()`**  
Switches `appState.panelMode` between `'shop'` and `'craft'`, updates the toggle button label, calls `updatePanel()`.

### Mobile-specific

**`showIngredientUses(name)`**  
On desktop: populates `#lookupContent` in the right panel. On mobile (when `.lookup-panel` is `display:none`): populates and opens `#ingredientModal` (a slide-up sheet). `closeIngredientModal(e)` handles backdrop tap and × button.

**`toggleHeaderControls()`**  
Toggles `open` class on `#headerControlsWrap` and `#headerCollapseBtn`. The controls div (`display:none` → `display:block`) contains the search, class filter, and view buttons.

**`togglePanelCollapse()`**  
Toggles `open` class on `#panelBodyWrap`. The panel body (`display:none` → `display:flex`) contains `#shoppingContent` and `.panel-footer`.

**`toggleCard(itemName)`**  
Toggles `.expanded` on `div.item-card[data-item]`. The `.card-body` is `display:none` by default on mobile and `display:block` when `.expanded` is present.

### View switching

**`toggleView()`**  
Cycles between card view and location view. Shows/hides `#itemsGrid`, `#locationTableWrap`, `#locationFilterWrap`. Swaps right sidebar between `#lookupSection` and `#locItemSection`.

**`toggleInventoryView()`**  
Toggles the All Resources inventory table. Manages `appState.view` and visibility of `#inventoryTableWrap`.

### Export / Backup

**`exportRows()`** — calls `buildShoppingList()`, returns sorted rows + metadata. Used by all three exporters.

**`exportTxt()` / `exportMd()` / `exportCsv()`** — format and download the shopping list.

**`exportBackup()` / `importBackup(input)`** — JSON round-trip for `selectedItems`, `locGathered`, and `craftQty`.

---

## CSS Organisation

All styles live in the single `<style>` block, ordered:

| Section | What it covers |
|---|---|
| `:root` variables | `--parchment`, `--ink`, `--gold`, `--crimson`, `--card-bg` |
| Body / layout | `body`, `.ledger-container`, `.main-content` |
| Shopping panel body wrapper | `.panel-body-wrap` (desktop: always flex; mobile: collapsible) |
| Shopping panel collapse btn | `.panel-collapse-btn` (desktop: hidden; mobile: visible) |
| **`@media (max-width: 800px)`** | All mobile overrides in one block — see below |
| Inputs / selects | `input`, `select` global styles |
| Item grid / cards | `.items-grid`, `.item-card`, `.card-inner`, `.card-front`, `.card-back` |
| Card flip | `.card-inner.flipped`, `backface-visibility`, chain tree styles |
| Card header / body | `.card-header`, `.card-body`, `.ingredient-row`, `.qty`, `.name-btn` |
| Colour coding | `.color-orange`, `.color-purple`, `.color-blue` |
| Done state | `.ingredient-row.done` overrides |
| Shopping panel chrome | `.panel-header`, `.panel-title-row`, `.panel-content`, `.panel-footer` |
| Shopping panel mode btn | `.panel-mode-btn` (Craft toggle) |
| Craft planner | `.craft-order-row`, `.craft-qty-input`, `.craft-feasibility`, `.craft-summary` |
| Export / backup buttons | `.export-btn-row`, `.backup-btn-row` |
| Ingredient lookup panel | `.lookup-panel`, `.lookup-header`, `.lookup-item-row` |
| Location table | `.loc-table`, `.loc-needed`, `.loc-ingredient`, `.counter-input` |
| Location item labels | `.loc-item-label`, `.loc-item-met`, `.loc-item-sep` |
| Help modal | `.help-modal`, `.help-modal-box` |
| Ingredient modal (mobile) | `.ingredient-modal`, `.ingredient-modal-box` (slide-up sheet) |
| Site footer | `.site-footer` |

### Mobile media query block (`@media (max-width: 800px)`)

Contains all responsive overrides in one place:
- Single-column layout (shopping panel → main content)
- Right sidebar (`lookup-panel`) hidden
- Header collapse: `.header-collapse-btn` shown; `.header-controls-wrap` hidden by default
- Shopping panel collapse: `.panel-collapse-btn` shown; `.panel-body-wrap` hidden by default
- Card collapse: `.card-body` hidden by default; `.item-card.expanded .card-body` shown; `.item-title::after` chevron indicator

---

## Adding New Features

### Adding a new view
1. Add a button to `.view-btn-row` in the HTML
2. Add a new `appState.view` value
3. Add a toggle function following the pattern of `toggleView()` / `toggleInventoryView()`
4. Add the table/content wrapper HTML inside `.main-content`
5. Update `filterData()` to delegate to the new renderer when the view is active

### Adding a new panel mode
1. Add a state value to `appState.panelMode`
2. Add a rendering function following `renderCraftPanel()`
3. Add a branch in `updatePanel()` to call it

### Updating crafting data
Edit the `craftingData` array directly in `crafting-ledger.html`. Item names are primary keys — changing a name orphans existing shopping list saves for that item. Ingredient names must match the MW Recipes sheet (after `normKey()` normalisation) for the production chain to resolve correctly.
