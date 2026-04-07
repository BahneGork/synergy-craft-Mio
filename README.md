# Masterwork Crafting Ledger

An interactive web tool for **Neverwinter** players to plan and track high-end Masterwork crafting. Built by the **Synergy Guild**.

> Data compiled by **Ivydora** & **Asura**. Join us on [Discord](https://discord.gg/ZAEfYwdjc8).

---

## Features

- **Browse recipes by class** — filter by Barbarian, Bard, Cleric, Fighter, Paladin, Ranger, Rogue, Warlock, or Wizard
- **Real-time search** — filter by item name or class as you type
- **Shopping list** — add items to a persistent left-side panel that aggregates all required ingredients across your selection
- **Progress tracking** — enter how many of each ingredient you've gathered; completed rows turn green and cross out automatically
- **Location View** — switch to a grouped table filtered by farming location; one row per ingredient, with a single gathered counter and a list of which items need it. Abyssal Hunt mods can be selected individually or combined
- **Filter Items panel** — in Location View the right sidebar becomes a checklist of crafting items at that location; uncheck any item to remove its quantities from the totals
- **Ingredient lookup** — in Card View, click any ingredient name to see every item that needs it in the right panel
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

## Roadmap

### Stable persistence for hosted deployments

The current `localStorage` approach is fragile when the tool is hosted: any URL change orphans all existing saves, and there is no way to migrate or back up data short of exporting to CSV. The goal is a server-side persistence layer that survives URL changes and works across devices.

**Planned approach:**
- Add a lightweight companion backend (a small Node.js or PHP service, or a hosted option like Supabase/Firebase) that stores progress against a user-chosen identifier (e.g. a guild tag or username)
- Keep the front end as a single HTML file; the backend is an optional extra layer — the tool still works without it using the existing `localStorage` fallback
- Support files would live in a `server/` directory alongside the HTML; a README explains setup for self-hosters
- The save/load functions (`saveProgress`, `saveSelected`, `loadState`) would be adapted to call the API when available and fall back to `localStorage` when not

**What this solves:**
- Progress survives URL changes and server migrations
- Multiple devices can share the same save (log in on your phone during a farming run, log in on your PC at home)
- Guild members could optionally share a save or each have their own account under one hosted instance

---

### Android app

The tool is already a self-contained web page, which makes an Android app achievable without a full rewrite.

**Two viable approaches:**

**Option A — Progressive Web App (PWA):** Add a `manifest.json` and a service worker to the existing HTML. Users can then install it directly from their browser to their home screen. It runs offline, looks like a native app, and requires no app store. This is the lowest-effort path and works on Android and iOS.

**Option B — WebView wrapper:** Package the HTML file inside a native Android shell using Android WebView (or a tool like Capacitor or Cordova). This produces an actual `.apk` that can be sideloaded or published to the Play Store. Gives more control over permissions, splash screen, and offline behaviour but requires a build environment.

**Preference:** Start with the PWA approach (Option A) — it requires only a few additional files (`manifest.json`, `sw.js`, an icon set) and no change to the core HTML logic. If a Play Store listing becomes a goal, wrap the PWA in a Trusted Web Activity (TWA), which is the recommended Android-native path for PWAs and requires minimal extra code.

---

## Development

The entire application is a single HTML file — no build step, no package manager.

See `docs/DEVELOPER.md` for architecture details, data format, and how to add new recipes.

---

## Credits

- **Guild:** Synergy (Neverwinter)
- **Data:** Ivydora & Asura
- **Original repo:** [jsynon/synergy-craft](https://github.com/jsynon/synergy-craft)
