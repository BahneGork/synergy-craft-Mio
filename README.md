# Masterwork Crafting Ledger

An interactive web tool for **Neverwinter** players to plan and track high-end Masterwork crafting. Built by the **Synergy Guild**.

> Data compiled by **Ivydora** & **Asura**. Layout/Design by **Elanor**. Enhanced features by **Mio**. Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

Live at: [tinygork.com/Synergy-Crafting/crafting-ledger.html](https://tinygork.com/Synergy-Crafting/crafting-ledger.html)

---

## Features

- **Browse recipes by class** — filter by Barbarian, Bard, Cleric, Fighter, Paladin, Ranger, Rogue, Warlock, or Wizard
- **Real-time search** — filter by item name or class as you type
- **Shopping list** — add items to a persistent left-side panel that aggregates all required ingredients across your selection; sorted by completion status then by farming location
- **Still needed counter** — each incomplete ingredient shows how many more you need to gather in bold, at a glance
- **Craft Planner** — switch the shopping panel into Craft mode to enter desired craft quantities per item and see a live feasibility check (green = have enough, red = short by N)
- **Production chain card flip** — click ⛓ Chain on any card to flip it and see the full MW Recipes production chain with run counts and yield-aware maths
- **Gathering ledger by location** — switch to a grouped table filtered by farming location; one row per ingredient with a single gathered counter and a list of which items need it; Abyssal Hunt mods can be selected individually or combined
- **All Resources view** — see every ingredient across all recipes in one sortable table with your current gathered amounts
- **Filter Items panel** — in the gathering ledger the right sidebar becomes a checklist of crafting items at that location; uncheck any item to remove its quantities from the totals
- **Ingredient lookup** — in card view, click any ingredient name to see every item that needs it (desktop: right panel; mobile: slide-up popup)
- **Colour-coded ingredients** — orange for rare drops, purple for farmable materials, blue for Stronghold resources
- **Export** — download your shopping list as TXT (Discord-friendly), Markdown, or CSV; sorted to match the on-screen panel
- **Backup / Restore** — export all progress and selections to a JSON file; import it back after moving or re-downloading the HTML file
- **Auto-save** — all progress and selections are saved in your browser automatically
- **Mobile optimised** — collapsible header controls, collapsible shopping panel, collapsible item cards (tap the name to expand), and a slide-up ingredient popup replacing the hidden right panel

---

## Quick Start

1. Open the [live site](https://tinygork.com/Synergy-Crafting/crafting-ledger.html) in any modern browser, or download `crafting-ledger.html` and open it locally
2. No installation, no server, no account required

See `docs/USER-GUIDE.md` for full usage instructions.

---

## How saving works

Progress and shopping list selections are stored in your browser using `localStorage`. No data is sent anywhere; everything stays on your machine.

**What is saved:**
- Gathered amounts per ingredient (from the gathering ledger)
- Which items are in your shopping list
- Craft quantities for the Craft Planner

**What survives:**
- Refreshing the page
- Closing and reopening the browser
- Restarting your computer

**What breaks it:**

| Cause | What happens |
|---|---|
| Clearing browser cookies / site data | All progress and selections are wiped |
| Moving or renaming the HTML file | Browser treats it as a different site — fresh save, old data orphaned |
| Opening the file from a different folder or device | New save slot; old data is not visible |
| Using a private/incognito window | Data is lost when the window closes |
| Safari on some systems with strict privacy settings | Falls back to in-memory storage — data is lost on refresh |

**Recommended:** use **Export Backup** (bottom of the shopping list panel) to save a `.json` file before moving the HTML file or re-downloading a new version. **Import Backup** restores everything in one click.

---

## Development

The entire application is a single HTML file — no build step, no package manager.

See `docs/DEVELOPER.md` for architecture details, data format, and how to add new recipes.

---

## Credits

- **Guild:** Synergy (Neverwinter)
- **Data:** Ivydora & Asura
- **Layout / Design:** Elanor
- **Enhanced features:** Mio
- **Original repo:** [jsynon/synergy-craft](https://github.com/jsynon/synergy-craft)
