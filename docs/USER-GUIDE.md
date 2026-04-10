# User Guide — Masterwork Crafting Ledger

Made by the **Synergy Guild** for Neverwinter players.  
Data compiled by **Ivydora** & **Asura**.  
Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

---

## What is this?

The Masterwork Crafting Ledger is a tool that helps you plan and track the materials needed for high-end Masterwork crafting recipes. Instead of juggling spreadsheets, you can:

- Browse all recipes by class
- Build a shopping list for the items you want to craft
- Track how many of each material you've gathered using the gathering ledger
- See at a glance how many more of each ingredient you still need
- Filter by farming location so you know exactly what to pick up during a run
- Export your shopping list to share with guildmates
- Back up and restore your progress if you re-download or move the file

---

## Getting Started

Open `crafting-ledger.html` in any modern web browser — Chrome, Firefox, Edge, or similar. No installation required. The tool works entirely in your browser, and your progress is saved automatically.

> **Note:** Keep the file in the same location on your computer. Moving it to a different folder will start a fresh save (browser storage is tied to the file's location). Use **Export Backup** before moving it.

---

## Browsing Items

### Search bar
Type any part of an item name or class name to filter the cards in real time.  
Example: typing `"amulet"` shows only amulet recipes. Typing `"cleric"` shows all Cleric gear.

### Class filter
Use the dropdown to narrow results to a single class. When a class is selected a **"this class only"** checkbox appears — tick it to hide items shared with other classes.

> The "this class only" checkbox is only available in card view. It is hidden when using the gathering ledger.

### Ingredient colours
Ingredients are colour-coded by rarity/source type to help you quickly spot what you need:

| Colour | Type | Examples |
|---|---|---|
| **Orange** | Rare drops | Druegarsteel Scrap, Fallen God's Ore, Abyssal Crystal |
| **Purple** | Farmable materials | Mushroom Log, Goristro Hide, Luminescent Darklake Water |
| **Blue** | Stronghold | Terebinth |
| Black | Uncategorised | Any other material |

### Ingredient lookup
Click any ingredient name to see a list of all crafting items that require it, shown in the **Used In** panel on the right side of the page.

---

## Shopping List

The **Shopping List** panel on the left side of the page is your planning workspace.

### Adding items
Each item card has a **+ Add to List** button in the top-right corner. Click it to add that item to your shopping list. Added items move to the top of the card grid so they're easy to find. The button turns red to show the item is selected.

### What the panel shows
When you have items selected, the shopping list panel shows a combined ingredient list sorted by:
1. **Incomplete ingredients first**, then fully gathered ones at the bottom
2. Within incomplete: **grouped by farming location**, then alphabetically

For each ingredient you'll see:
- **Total quantity** needed across all selected items
- **Still needed** — how many more you have left to gather (shown in bold red)
- **How much you've gathered** and out of how much total
- **Where to farm** it

Ingredients you've fully gathered are crossed out and dimmed at the bottom of the list.

### Exporting your list
Three export buttons sit at the bottom of the shopping list panel:
- **TXT** — plain text with fixed columns, paste into Discord or a text file
- **MD** — Markdown table format, works in GitHub, Notion, Obsidian, etc.
- **CSV** — opens in Excel or Google Sheets

All exports are sorted to match the panel (incomplete first, by location) and include: ingredient name, total needed, how much gathered, and source location.

> The export buttons shake red if your shopping list is empty.

---

## Tracking Gathered Materials

All gathering is tracked through the **Gathering Ledger by Location**. There are no per-item counters on the cards — gathering is always tracked as a shared pool per ingredient, so entering a number once updates everything that needs that ingredient.

When you've gathered enough of an ingredient to satisfy all your selected items, its row turns **green** and it sinks to the bottom of the shopping list. Item cards also reflect this — ingredient rows turn green based on what you've entered in the ledger.

See the [Gathering Ledger](#gathering-ledger-by-location) section below for full details.

---

## Saving and Backup

### How your data is saved automatically

Every time you update the gathered counter or shopping list, it saves instantly in your browser using `localStorage`. No account or internet connection is needed — everything stays on your machine.

**Your data survives:**
- Refreshing the page
- Closing and reopening the browser
- Restarting your computer

### When saving breaks

| Situation | What happens |
|---|---|
| You clear browser cookies or site data | All progress and selections are permanently wiped |
| You move or rename the HTML file | The browser treats it as a different site — a fresh empty save starts; the old data is orphaned |
| You open the file from a different folder | Same as above — new save slot, old data not visible |
| You open the file on a different computer | Data does not transfer between machines |
| You use a private / incognito window | Data is lost the moment the window closes |
| Safari with strict privacy settings | Falls back to in-memory only — data is lost on refresh |

### Backup and Restore

The **Export Backup** and **Import Backup** buttons at the bottom of the shopping list panel let you save and restore all your progress.

**Export Backup** downloads a `crafting-backup-YYYY-MM-DD.json` file containing:
- Your shopping list selections
- All gathered amounts

**Import Backup** opens a file picker. Select your `.json` backup and all your data is restored immediately.

**When to use it:**
- Before re-downloading a new version of the tool
- Before moving the HTML file to a different folder
- To transfer progress to another computer

Keep the backup file somewhere permanent (Documents, Desktop) — not in the same temp folder as the HTML file.

### Resetting progress

- **Reset Gathered** — clears all gathered amounts but keeps your shopping list selections. Useful when starting a new farming session from scratch.
- **Reset All Progress** — clears everything: gathered amounts and shopping list. Both buttons ask for confirmation (click once to see the prompt, click again to confirm within 2.5 seconds).

---

## Gathering Ledger by Location

The gathering ledger is designed for active farming sessions. Instead of scrolling through full item cards, you get a focused table showing exactly what you need from one specific place.

### Switching to the gathering ledger
Click the **⊞ Gathering ledger by location** button in the controls row. The card grid is replaced by a location dropdown and a grouped ingredient table. Click **⊠ Item cards** to go back.

### Using the table
1. Select a location from the dropdown
2. The table shows one row per **ingredient** at that location, sorted alphabetically
3. Each row shows: **total quantity needed**, **how many gathered** (editable), **ingredient name**, and a **comma-separated list of crafting items** that need it

As you type a gathered amount:
- Item names in the list turn **green** when your gathered total meets their individual requirement
- The row dims when you've gathered enough for all items
- The shopping list panel updates in real time

You can still use the search bar and class filter to narrow the table.

### Abyssal Hunt mods
Selecting **Abyssal Hunt** from the dropdown shows a set of checkboxes — one per mod (e.g. Tricky Reversal, Itty Bitty). Tick the mods you are running that session; the table combines ingredients from all ticked mods.

### Filtering by crafting item
When the gathering ledger is active the right-hand panel switches to **Filter Items** — a checklist of every crafting item that has ingredients at the selected location. Uncheck an item to remove its quantities from the Needed totals. Useful when you are only crafting a subset of the available items. The filter resets each time you change location.

### How gathered amounts work
The gathered amount you enter is a **shared pool per ingredient** — not per crafting item. If three of your selected items all need Luminescent Darklake Water and you enter 40 gathered, the shopping list will show 40 collected toward the total across all three items. The "still needed" counter tells you what remains.

---

## Tips

**Before a farming run:**
1. Add all items you want to craft to the Shopping List
2. Export to TXT or CSV to keep on your phone or second screen
3. Switch to the gathering ledger, select your farming destination, and use the **Filter Items** panel to tick only the items you are crafting this run

**During a run:**
- Update gathered counters as you pick up materials
- The table dims completed rows so you can see at a glance what's still missing
- The shopping list panel's "still needed" numbers update live

**Planning multiple crafts:**
- Add several items to the Shopping List to see the combined ingredient totals
- Check where the totals overlap — materials needed for multiple items can be farmed in one pass
- Use the ingredient lookup (click any ingredient name in card view) to quickly see which of your target items share a common material

**Sharing your list:**
- Export to TXT and paste directly into the guild Discord
- Export to CSV and share the spreadsheet with your crafting partner
