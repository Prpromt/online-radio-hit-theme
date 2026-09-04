# Online Radio HIT — Cloudflare preview

This repository keeps the WordPress theme under `wordpress-theme/` for the final WordPress host and provides a Cloudflare Worker static preview under `public/`.

Cloudflare deployment:
- Build command: none / `exit 0`
- Deploy command: `npx wrangler deploy`
- Root directory: `/`
- `wrangler.toml` configures Worker + static assets from `public/`.

The preview is for design and browser-side UI/player validation. WordPress/PHP, database, AJAX, uploads and production radio stream require the final WordPress/PHP host.
