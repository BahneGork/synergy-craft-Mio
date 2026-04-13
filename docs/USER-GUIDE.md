# User Guide — Masterwork Crafting Ledger

Made by the **Synergy Guild** for Neverwinter players.  
Data compiled by **Ivydora** & **Asura**. Layout/Design by **Elanor**. Enhanced features by **Mio**.  
Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

---

## What is this?

The Masterwork Crafting Ledger is a tool that helps you plan and track the materials needed for high-end Masterwork crafting recipes. Instead of juggling spreadsheets, you can:

- Browse all recipes by class
- Build a shopping list for the items you want to craft
- Track how many of each material you've gathered using the gathering ledger
- See at a glance how many more of each ingredient you still need
- Use the Craft Planner to check whether your current inventory can fill a set of craft orders
- Flip any item card to see its full production chain
- Filter by farming location so you know exactly what to pick up during a run
- Export your shopping list to share with guildmates
- Back up and restore your progress if you re-download or move the file

---

## Getting Started

Open the tool in any modern web browser — Chrome, Firefox, Edge, or similar. No installation required. Your progress is saved automatically.

> **Note (local file):** If running the HTML file locally, keep it in the same location. Moving it to a different folder will start a fresh save (browser storage is tied to the file's path). Use **Export Backup** before moving it.

---

## Browsing Items

### Search bar
Type any part of an item name or class name to filter the cards in real time.  
Example: typing `"amulet"` shows only amulet recipes. Typing `"cleric"` shows all Cleric gear.

### Class filter
Use the dropdown to narrow results to a single class. When a class is selected a **"this class only"** checkbox appears — tick it to hide items shared with other classes.

> The "this class only" checkbox is only available in card view. It is hidden when using the gathering ledger.

### Ingredient colours
Ingredients are colour-coded by rarity/source type:

| Colour | Type | Examples |
|---|---|---|
| **Orange** | Rare drops | Druegarsteel Scrap, Fallen God's Ore, Abyssal Crystal |
| **Purple** | Farmable materials | Mushroom Log, Goristro Hide, Luminescent Darklake Water |
| **Blue** | Stronghold | Terebinth |
| Black | Uncategorised | Any other material |

### Ingredient lookup
Click any ingredient name to see all crafting items that require it.

- **Desktop:** results appear in the **Used In** panel on the right side of the page
- **Mobile:** a slide-up popup appears at the bottom of the screen; tap the backdrop or × to dismiss

---

## Item Cards

Each item card shows the class badges, the item name, an ingredient list with progress, and two action buttons.

### Production chain (⛓ Chain)
Click **⛓ Chain** to flip the card over. The back face shows the full MW Recipes production chain — every intermediate crafted item and raw material, with run counts and yield-aware maths (e.g. Purified Darklake Water yields 3 per run, so the chain shows how many runs are needed and any surplus). Click **↩ Back** to flip back.

> The chain data is loaded live from the MW Recipes Google Sheet. If it says "still loading", wait a moment and flip again.

### Add to List
The **+ Add to List** button adds the item to your shopping list. It turns red and changes to **✓ In List** when selected. Selected items float to the top of the card grid.

### On mobile
Item cards are **collapsed by default** to save screen space. Tap the item name to expand the full ingredient list. A small ▼/▲ indicator shows the state.

---

## Shopping List Panel

The **Order List** panel on the left is your planning workspace.

### Adding items
Click **+ Add to List** on any item card. Selected items move to the top of the grid. The button turns red.

### What the panel shows
The shopping list aggregates ingredients across all selected items, sorted by:
1. **Incomplete ingredients first**, then fully gathered ones at the bottom
2. Within incomplete: **grouped by farming location**, then alphabetically

For each ingredient:
- **Total quantity** needed across all selected items
- **Still needed** — how many more you have left to gather (bold red)
- **Gathered / total** progress
- **Where to farm** it

Fully gathered ingredients are crossed out and dimmed at the bottom.

### On mobile
The shopping panel is collapsible — tap **Order List ▼** in the panel header to hide or show the list content.

### Exporting your list
Three export buttons at the bottom of the panel:
- **TXT** — plain text with fixed columns, paste into Discord
- **MD** — Markdown table format (GitHub, Notion, Obsidian)
- **CSV** — opens in Excel or Google Sheets

All exports include: ingredient name, total needed, gathered, and source location.

> The export buttons shake red if your shopping list is empty.

---

## Craft Planner

The **⚒ Craft** button in the top-right corner of the shopping panel switches to Craft mode. Use this when you are a guild crafter checking whether your current inventory can fill a set of craft orders.

### How it works
1. Add the items you intend to craft to the shopping list
2. Click **⚒ Craft** to enter Craft mode
3. Set the desired quantity for each item using the number inputs
4. The feasibility table shows every ingredient with:
   - How much is **needed** (multiplied by craft quantities)
   - How much you **have** (from your gathered amounts)
   - A ✓ / ✗ indicator and the surplus or deficit

The ingredient pool is shared correctly across all craft orders — if two items share Druegarsteel Scrap, the totals are combined before checking.

Click **📦 Shop** to return to the normal shopping list view.

---

## Tracking Gathered Materials

All gathering is tracked through the **⊞ Gathering Ledger by Location**. Gathering is always a **shared pool per ingredient** — entering a number once updates everything that needs that ingredient.

When you've gathered enough to satisfy all your selected items, the ingredient row turns **green** and sinks to the bottom of the shopping list.

---

## Gathering Ledger by Location

Designed for active farming sessions — one focused table per location instead of scrolling full item cards.

### Switching views
Click **⊞ Gathering ledger by location** in the controls. Click **⊠ Item cards** to go back.  
The **⊞ All Resources** button shows every ingredient across all items in one table.

### Using the table
1. Select a location from the dropdown
2. The table shows one row per ingredient at that location
3. Each row: **total quantity needed**, **gathered counter** (editable), **ingredient name**, **crafting items** that need it

As you type a gathered amount:
- Item names turn **green** when your total meets their individual requirement
- The row dims when you've gathered enough for all items
- The shopping list panel updates live

### Abyssal Hunt mods
Selecting **Abyssal Hunt** shows checkboxes for each mod (e.g. Tricky Reversal, Itty Bitty). Tick the mods you are running — the table combines ingredients from all ticked mods.

### Filter Items (right panel)
When the gathering ledger is active the right panel shows **Filter Items** — a checklist of every crafting item with ingredients at the selected location. Uncheck an item to remove its quantities from the Needed totals. Resets each time you change location.

---

## Saving and Backup

### Auto-save
Every counter update or shopping list change saves instantly in your browser. No account or internet connection needed.

### When saving breaks

| Situation | What happens |
|---|---|
| Clear browser cookies / site data | All progress permanently wiped |
| Move or rename the HTML file | New save slot; old data orphaned |
| Open from a different folder or device | New save slot |
| Private / incognito window | Data lost when window closes |
| Safari with strict privacy settings | In-memory fallback — lost on refresh |

### Backup and Restore
**Export Backup** (bottom of shopping panel) downloads a `.json` file with your shopping list and all gathered amounts.  
**Import Backup** restores everything in one click.

Use it before re-downloading a new version, moving the file, or switching computers.

### Resetting progress
- **Reset Gathered** — clears all gathered amounts, keeps shopping list
- **Reset All Progress** — clears everything

Both ask for confirmation (click once to arm, again within 2.5 seconds to confirm).

---

## Mobile Tips

- The **Filters & View** bar collapses by default — tap it to show the search, class filter, and view buttons
- The **Order List ▼** header collapses the shopping panel to save space
- **Item cards are collapsed** by default — tap the item name to expand ingredients
- Tap any **ingredient name** to open a slide-up "Used In" popup
- The ⛓ Chain flip works on mobile too — tap the button, scroll the back face if needed
