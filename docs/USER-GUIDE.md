# User Guide — Masterwork Crafting Ledger

Made by the **Synergy Guild** for Neverwinter players.  
Data compiled by **Ivydora** & **Asura**.  
Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

---

## What is this?

The Masterwork Crafting Ledger is a tool that helps you plan and track the materials needed for high-end Masterwork crafting recipes. Instead of juggling spreadsheets, you can:

- Browse all recipes by class
- Build a shopping list for the items you want to craft
- Track how many of each material you've already gathered
- Filter by farming location so you know exactly what to pick up during a run
- Export your shopping list to share with guildmates

---

## Getting Started

Open `crafting-ledger.html` in any modern web browser — Chrome, Firefox, Edge, or similar. No installation required. The tool works entirely in your browser, and your progress is saved automatically.

> **Note:** Keep the file in the same location on your computer. Moving it to a different folder will start a fresh save (browser storage is tied to the file's location).

---

## Browsing Items

### Search bar
Type any part of an item name or class name to filter the cards in real time.  
Example: typing `"amulet"` shows only amulet recipes. Typing `"cleric"` shows all Cleric gear.

### Class filter
Use the dropdown to narrow results to a single class.

### Ingredient colours
Ingredients are colour-coded by rarity/source type to help you quickly spot what you need:

| Colour | Type | Examples |
|---|---|---|
| **Orange** | Rare drops | Druegarsteel Scrap, Fallen God's Ore, Abyssal Crystal |
| **Purple** | Farmable materials | Mushroom Log, Goristro Hide, Luminescent Darklake Water |
| **Blue** | Stronghold | Terebinth |
| Black | Uncategorised | Any other material |

### Ingredient lookup
Click any ingredient name to see a list of all crafting items that require it, shown in the **Used In** panel on the right side of the page. Click the same ingredient again to see a different one.

---

## Shopping List

The **Shopping List** panel on the left side of the page is your planning workspace.

### Adding items
Each item card has a **+ Add to List** button in the top-right corner. Click it to add that item to your shopping list. Added items move to the top of the card grid so they're easy to find. The button turns red to show the item is selected.

### What the panel shows
When you have items selected, the shopping list panel shows a combined ingredient list:
- **Total quantity** needed across all selected items
- **How much you've gathered** (pulled from your progress tracking)
- **Where to farm** each ingredient

Ingredients you've fully gathered are crossed out and dimmed.

### Exporting your list
Three export buttons sit at the bottom of the shopping list panel:
- **TXT** — plain text with fixed columns, paste into Discord or a text file
- **MD** — Markdown table format, works in GitHub, Notion, Obsidian, etc.
- **CSV** — opens in Excel or Google Sheets

All exports include: ingredient name, total needed, how much gathered, and source location.

> The export buttons shake red if your shopping list is empty.

---

## Progress Tracking

Every ingredient row has a **counter** on the right side showing `[gathered] / needed`.

- Type a number to set how many you've collected
- When the gathered amount reaches the needed amount, the row turns **green** and crosses out
- The card header shows a summary: **"X / N ingredients complete"**

### Your progress is saved automatically
Every time you update a counter, it saves instantly in your browser. Your progress will still be there after:
- Refreshing the page
- Closing the browser
- Shutting down your computer

It will **only** be lost if you clear your browser's cookies and site data, or open the file from a different location.

### Resetting progress
The **Reset All Progress** button at the bottom of the shopping list panel clears all your gathered counts and your shopping list selection. It asks for confirmation — click it once to see the prompt, then once more to confirm.

---

## Location View

Location View is designed for active farming sessions. Instead of scrolling through full item cards, you get a simple table showing exactly what you need from one specific place.

### Switching to Location View
Click the **⊞ Location View** button in the controls row. The card grid is replaced by a location dropdown and a flat table.

### Using the table
1. Select a location from the dropdown (e.g. "Abyssal Hunt Mod: Tricky Reversal")
2. The table shows every ingredient sourced there, sorted alphabetically
3. Each row shows: **how many needed**, **how many gathered** (editable), **ingredient name**, and **which crafting item needs it**

You can still use the search bar and class filter to narrow the table down.

### Updating counts
The gathered counter in the table works the same as in card view — type in what you've picked up. The numbers sync automatically: if you gather 5 Mushroom Logs in Location View, the card for Shroomwood Amulet will show the updated count when you switch back.

### Switching back
Click **⊠ Card View** to return to the normal card grid.

---

## Tips

**Before a farming run:**
1. Add all items you want to craft to the Shopping List
2. Open the export panel and download a CSV or TXT to keep on your phone or second screen
3. Switch to Location View and select your farming destination to see exactly what to look for

**During a run:**
- Use Location View with the location selected — update counters as you gather materials
- The table dims out completed rows so you can see at a glance what's still missing

**Planning multiple crafts:**
- Add several items to the Shopping List to see the combined ingredient totals
- Check where the totals overlap — materials needed for multiple items can be farmed in one pass
- Use the ingredient lookup (click any ingredient name) to quickly see which of your target items share a common material

**Sharing your list:**
- Export to TXT and paste directly into the guild Discord
- Export to CSV and share the spreadsheet with your crafting partner so they can track their own progress
