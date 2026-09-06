ONLINE RADIO HIT — THEME v38 FINAL

Based on the approved v37 design. No redesign.
Only final media-display correction: the main player cover now uses a real IMG element so the Featured Image cannot be hidden by the CSS background shorthand. The sticky player also receives the current cover on initial page load.

EMBED PLAYER — BATCH 10

The production WordPress theme includes page-embed.php, a standalone template for the branded «Онлайн Радио Хит» embed player. Create a WordPress page with slug «embed» and assign the template «Онлайн Радио Хит — Embed Player».

The embed page uses the same current/last uploaded song logic as the site player: real title, artist, cover and audio file. It does not expose a Yandex or other third-party player. The iframe source must be served over HTTPS.

For the public static Preview, /embed/ is a visual/demo version because Cloudflare Pages/Workers Assets does not execute WordPress PHP. The Preview also contains /embed-code/, which generates the iframe HTML and provides a copy button.

EMBED CODE

Recommended default code:
<iframe src="https://online-radio-hit-theme.musickinoproducer.workers.dev/embed/" width="100%" height="250" frameborder="0" allow="autoplay" title="Онлайн Радио Хит"></iframe>

Production sites should replace the src with the production WordPress /embed/ URL.
