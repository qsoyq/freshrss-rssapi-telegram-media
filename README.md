# FreshRSS RSSAPI Telegram Media

FreshRSS user extension that rewrites expired Telegram CDN image URLs to the stable RSSAPI Telegram media proxy at display time.

## Requirements

- FreshRSS with user extensions enabled
- RSSAPI deployed with `GET /api/rss/telegram/media/{channel}/{post_id}/{image_index}`
- The FreshRSS server must be able to reach the RSSAPI service and Telegram public embed pages through RSSAPI

## Install

Copy the complete `xRssapiTelegramMedia` directory into the FreshRSS `extensions/` directory:

```bash
cp -R xRssapiTelegramMedia /path/to/FreshRSS/extensions/
```

Then open **Administration > Extensions** in FreshRSS and enable **RSSAPI Telegram Media** for the affected user.

The extension derives the RSSAPI origin from the current subscription URL. For example, a subscription at `https://rss.example.com/api/rss/telegram/channel?channels=meizitu3` produces media URLs on `https://rss.example.com/api/rss/telegram/media/...`. No environment variable is required.

## Behavior

Only entries linked to public `https://t.me/<channel>/<post_id>` messages are handled. Existing article content is rewritten in the `entry_before_display` hook; the database, read state, stars, labels, and original article links are not modified. Content images, list thumbnails, and image enclosures are rewritten.

## Development

```bash
php -l xRssapiTelegramMedia/extension.php
```

This repository contains only the FreshRSS extension. The RSSAPI server implementation lives in [qsoyq/rssapi](https://github.com/qsoyq/rssapi).

## License

MIT. See [LICENSE](LICENSE).
