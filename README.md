# Masterwork Crafting Ledger

An interactive web tool for **Neverwinter** players to plan and track high-end Masterwork crafting. Built by the **Synergy Guild**.

> Data compiled by **Ivydora** & **Asura**. Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

---

## Features

- **Browse recipes by class** — filter by Barbarian, Bard, Cleric, Fighter, Paladin, Ranger, Rogue, Warlock, or Wizard
- **Real-time search** — filter by item name or class as you type
- **Shopping list** — add items to a persistent left-side panel that aggregates all required ingredients across your selection
- **Progress tracking** — enter how many of each ingredient you've gathered; completed rows turn green and cross out automatically
- **Location View** — switch to a flat table filtered by farming location to see exactly what to pick up during a run
- **Ingredient lookup** — click any ingredient name to see every item that needs it, listed in the right panel
- **Colour-coded ingredients** — orange for rare drops, purple for farmable materials, blue for Stronghold resources
- **Export** — download your shopping list as TXT (Discord-friendly), Markdown, or CSV
- **Auto-save** — all progress and selections are saved in your browser automatically

---

## Quick Start

1. Download `crafting-ledger.html`
2. Open it in any modern browser (Chrome, Firefox, Edge)
3. No installation, no server, no account required

See `docs/USER-GUIDE.md` for full usage instructions.

---

## How saving works

Progress and shopping list selections are stored in your browser using `localStorage` — the same mechanism sites use to remember your preferences without an account. No data is sent anywhere; everything stays on your machine.

**What is saved:**
- Every ingredient counter (how many you've gathered)
- Which items are in your shopping list

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

**In short:** keep the file in the same place, use a normal (non-incognito) window, and don't clear site data for the file. If you need to share progress or move to another machine, export to CSV first.

**If you host the tool on a domain:**

- Safari's local-file restriction no longer applies — saving is reliable in all browsers
- Each visitor has their own independent save; users do not share progress
- The URL must stay the same forever — changing the domain, subdomain, or switching from `http` to `https` creates a new storage slot and leaves existing saves behind
- Moving from the local file to a hosted URL also starts fresh — export to CSV first

---

## Development

The entire application is a single HTML file — no build step, no package manager.

See `docs/DEVELOPER.md` for architecture details, data format, and how to add new recipes.

---

## Credits

- **Guild:** Synergy (Neverwinter)
- **Data:** Ivydora & Asura
- **Original repo:** [jsynon/synergy-craft](https://github.com/jsynon/synergy-craft)
