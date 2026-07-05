# Smart Food Waste Analytics

A web application built for the PromptQuest Hackathon that helps restaurants track food waste, calculate financial losses in real time, and surface data-driven insights to reduce waste going forward.

## The Problem

Restaurants lose money every day from untracked food waste — expired stock, overproduction, spoilage, and prep mistakes — but most have no systematic way to measure where the losses are actually happening. Without visibility, there's no way to fix it.

## Our Solution

Smart Food Waste Analytics lets kitchen staff log wasted ingredients as they happen — quantity, ingredient, and reason — and the system automatically calculates the financial loss and rolls it up into manager-facing analytics. Instead of guesswork, managers get concrete numbers: what's being wasted, why, how much it's costing, and whether it's getting better or worse over time.

## Category

Food & Beverage / Sustainability

## Tech Stack

- **Frontend:** HTML, CSS, Vanilla JavaScript
- **Backend:** PHP (native, no framework)
- **Database:** MySQL (via WampServer / phpMyAdmin)
- **Charts:** Chart.js

## Core Features

**User Management**
- Role-based accounts (Staff / Manager)
- Secure authentication — hashed passwords, prepared statements, session regeneration on login, session cache-control on protected pages

**Core Business — Waste Logging**
- Staff log wasted ingredients with quantity and reason
- Financial loss calculated automatically and server-side (never trusted from client input)
- Duplicate ingredient prevention, input validation, and unit field restrictions (no numeric units)

**Dashboard (Manager only)**
- Total financial loss, total logs, top waste reason, and week-over-week trend
- Two live charts: top wasted ingredients by cost, waste breakdown by reason
- Rule-based insight banner that automatically flags the single biggest loss driver (whichever is higher — a dominant reason or a dominant ingredient)

**Reports (Manager only)**
- Filterable log history by ingredient, reason, and date range
- CSV export of filtered results with a running total
- Individual log deletion and a bulk "Delete All Logs" reset option, both with confirmation prompts

**Settings (Manager only)**
- Add/delete ingredients with cost-per-unit tracking
- Ingredients can be safely deleted while historical waste logs referencing them are preserved for record-keeping

## Security Measures

- All database queries use prepared statements (SQL injection protected)
- All user-facing output is escaped (XSS protected)
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- Session ID regenerated on login (session fixation protection)
- Destructive actions (delete) require POST requests, not GET
- Cache-control headers prevent back-button access to protected pages after logout

## Setup Instructions

1. Import `food_waste_tracker.sql` into MySQL via phpMyAdmin
2. Place the project folder inside `www` (WampServer)
3. Start Apache and MySQL in WampServer
4. Visit `http://localhost/FoodWasteTracker/` in your browser
5. Register an account (Staff or Manager) to get started

## Teammates

- Muzh
- Hakimkal

## Hackathon

Built for the PromptQuest Hackathon — "Innovate for Business: Build a Solution Companies Would Buy"

## Screenshots

**Login**
![Login](screenshots/login.png)

**Log Waste (Staff View)**
![Log Waste](screenshots/logwaste.png)

**Dashboard (Manager View)**
![Dashboard](screenshots/dashboard.png)

**Reports & Filtering**
![Reports](screenshots/reports.png)

**Settings — Manage Ingredients**
![Settings](screenshots/settings.png)
