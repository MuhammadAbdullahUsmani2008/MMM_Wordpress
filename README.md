# Local WordPress Demo Site

A fully working WordPress site running locally in Docker.

## URLs

| What | URL |
| --- | --- |
| Site | http://localhost:8080 |
| Admin dashboard | http://localhost:8080/wp-admin/ |
| Login page | http://localhost:8080/wp-login.php |

## Admin credentials

Credentials live in a local `.env` file that is **not** committed to git.

```powershell
Copy-Item .env.example .env   # then edit .env with your own values
```

| Field | Where it comes from |
| --- | --- |
| Username | `WORDPRESS_ADMIN_USER` in `.env` |
| Password | `WORDPRESS_ADMIN_PASSWORD` in `.env` |
| Email | `WORDPRESS_ADMIN_EMAIL` in `.env` |

> Local demo only — do not reuse these passwords anywhere real.

## Running it

```powershell
Copy-Item .env.example .env    # first run only; fill in your values
.\start.ps1                    # starts Docker Desktop (if needed) + the containers
.\stop.ps1                     # stops the containers, keeps your content
```

`docker compose` reads `.env` automatically, so nothing secret needs to be
written into `docker-compose.yml`.

To wipe everything and start completely fresh:

```powershell
docker compose down -v
```

## What's installed

- **WordPress 7.1.3** on `wordpress:6.7-php8.2-apache`, upgraded to current during setup
- **MariaDB 11** for the database
- **WP-CLI** as a separate one-off container (`wpcli` service)
- **Theme:** Twenty Twenty-Five (active), Twenty Twenty-Four/Three available
- **Plugins:** Yoast SEO (active), Classic Editor (installed, inactive), Akismet & Hello Dolly (bundled, inactive)

## Site content

**Pages** (static front page + posts page configured):

- Home (front page)
- About
- Services
- Contact
- Blog (posts page)

**Posts:** 4 demo articles across the categories *News*, *Tutorials*, *Releases*.

Default WordPress sample content ("Hello world!", Sample Page, privacy policy page) was removed.

## Settings applied

- Pretty permalinks: `/%postname%/`
- Search engines discouraged (`blog_public = 0`) — appropriate for a local site
- Timezone UTC, date format `F j, Y`, time format `g:i a`
- Site title: **MMM Demo Site**

## Running WP-CLI commands

```powershell
docker compose run --rm wpcli <command>
```

Examples:

```powershell
docker compose run --rm wpcli plugin install akismet --activate
docker compose run --rm wpcli post list --post_type=page
docker compose run --rm wpcli search-replace http://localhost:8080 http://localhost:8080
docker compose run --rm wpcli db export /setup/backup.sql
```

## Re-running the content setup

The script is idempotent — it updates existing content rather than duplicating it:

```powershell
docker compose run --rm wpcli eval-file /setup/setup-content.php
```

## Files

| File | Purpose |
| --- | --- |
| `docker-compose.yml` | Container definitions (WordPress, MariaDB, WP-CLI) |
| `setup/setup-content.php` | Idempotent content + settings setup script |
| `start.ps1` / `stop.ps1` | Convenience scripts |

## Ports

- `8080` — WordPress (host) → `80` (container)
- MariaDB is internal only, not exposed to the host
