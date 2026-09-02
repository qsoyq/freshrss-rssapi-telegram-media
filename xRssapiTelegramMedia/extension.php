<?php

class xRssapiTelegramMediaExtension extends Minz_Extension {
    public function init(): void {
        $this->registerHook('entry_before_display', [$this, 'rewriteEntry']);
    }

    public function rewriteEntry($entry) {
        $link = method_exists($entry, 'link') ? $entry->link() : null;
        if (!is_string($link) || !preg_match('~^https?://t\.me/([A-Za-z0-9_]+)/([0-9]+)~', $link, $post)) {
            return $entry;
        }

        $base = rtrim((string) getenv('RSSAPI_URL'), '/');
        if ($base === '') {
            $base = 'https://p.19940731.xyz';
        }
        if ($base === '') {
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

}
