<?php

class xRssapiTelegramMediaExtension extends Minz_Extension {
    public function init(): void {
        $this->registerHook('entry_before_display', [$this, 'rewriteEntry']);
    }

    public function rewriteEntry($entry) {
        $feed = method_exists($entry, 'feed') ? $entry->feed() : null;
        $feedUrl = $feed !== null && method_exists($feed, 'url') ? $feed->url(false) : null;
        $base = $this->rssapiOrigin($feedUrl);
        if ($base === null) {
            return $entry;
        }

        $link = method_exists($entry, 'link') ? $entry->link() : null;
        if (!is_string($link) || !preg_match('~^https?://t\.me/([A-Za-z0-9_]+)/([0-9]+)~', $link, $post)) {
            return $entry;
        }

        $index = 0;
        $content = method_exists($entry, 'content') ? $entry->content(false) : null;
        if (is_string($content)) {
            $content = preg_replace_callback(
                '~https?://cdn[0-9]+\.telesco\.pe/[^"\'<>\s]+~i',
                function ($match) use ($base, $post, &$index) {
                    return $base . '/api/rss/telegram/media/' . $post[1] . '/' . $post[2] . '/' . $index++;
                },
                $content
            );
            if (method_exists($entry, '_content')) {
                $entry->_content($content);
            }
        }

        $thumbnail = method_exists($entry, 'attributeArray') ? $entry->attributeArray('thumbnail') : null;
        if (is_array($thumbnail) && preg_match('~https?://cdn[0-9]+\.telesco\.pe/~i', (string) ($thumbnail['url'] ?? ''))) {
            $thumbnail['url'] = $base . '/api/rss/telegram/media/' . $post[1] . '/' . $post[2] . '/0';
            if (method_exists($entry, '_attribute')) {
                $entry->_attribute('thumbnail', $thumbnail);
            }
        }

        if (method_exists($entry, 'attributeArray') && method_exists($entry, '_attribute')) {
            $enclosures = $entry->attributeArray('enclosures');
            if (is_array($enclosures)) {
                foreach ($enclosures as &$enclosure) {
                    if (is_array($enclosure) && preg_match('~https?://cdn[0-9]+\.telesco\.pe/~i', (string) ($enclosure['url'] ?? ''))) {
                        $enclosure['url'] = $base . '/api/rss/telegram/media/' . $post[1] . '/' . $post[2] . '/' . $index++;
                    }
                }
                unset($enclosure);
                $entry->_attribute('enclosures', $enclosures);
            }
        }

        return $entry;
    }

    private function rssapiOrigin($feedUrl): ?string {
        if (!is_string($feedUrl)) {
            return null;
        }

        $parts = parse_url(html_entity_decode($feedUrl));
        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? '';
        if (!is_string($scheme) || !is_string($host) || !is_string($path)) {
            return null;
        }
        if (!preg_match('~^/api/rss/telegram/channel/?$~i', $path)) {
            return null;
        }

        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        return $scheme . '://' . $host . $port;
    }

}
