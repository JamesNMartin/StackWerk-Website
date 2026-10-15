<?php

namespace StackWerk;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Throwable;

/**
 * Looks up an app's newest release on GitHub so download buttons point
 * straight at the current installers.
 *
 * Results are cached (`stackwerk.website.releases.ttl` minutes). When GitHub
 * can't be reached the last good answer is used, and with none at all the
 * buttons fall back to the repository's "latest release" page.
 */
class Releases
{
    /** Minutes to wait before asking GitHub again after a failed lookup. */
    protected const RETRY_AFTER = 10;

    /**
     * The newest published release of `owner/repo`, or null if there is none
     * or GitHub couldn't be reached.
     *
     * @return array{version: string, name: string, url: string, published: int|null, assets: array<int, array{name: string, url: string, size: int}>}|null
     */
    public static function latest(string $repo): ?array
    {
        if (static::isRepo($repo) === false) {
            return null;
        }

        $cache = App::instance()->cache('stackwerk.website');
        $key   = 'release.' . strtolower(str_replace('/', '.', $repo));

        $cached = $cache->get($key);
        if (is_array($cached) === true) {
            return $cached['release'];
        }

        $release = static::fetch($repo);

        if ($release !== null) {
            $cache->set($key, ['release' => $release], (int)option('stackwerk.website.releases.ttl', 60));
            $cache->set($key . '.last', $release);
            return $release;
        }

        // GitHub is unreachable or rate-limited: keep showing the last release we saw
        $release = $cache->get($key . '.last');
        $cache->set($key, ['release' => $release], static::RETRY_AFTER);

        return $release;
    }

    /**
     * The page's download options with links resolved against its latest release.
     *
     * Each entry of the page's `downloads` structure names a file pattern such
     * as `Winston-Setup-*-x64.exe`; it links to the matching file of the latest
     * release, or to the release page when no file matches.
     *
     * @return array<int, array{label: string, text: string, kind: string, primary: bool, url: string, file: string|null, size: string|null}>
     */
    public static function downloads(Page $page): array
    {
        $repo    = trim((string)$page->github()->value());
        $release = static::latest($repo);
        $assets  = $release['assets'] ?? [];
        $links   = [];

        $fallback = $release['url'] ?? (static::isRepo($repo) ? 'https://github.com/' . $repo . '/releases/latest' : null);

        foreach ($page->downloads()->toStructure() as $download) {
            $asset = null;

            foreach ($assets as $candidate) {
                if (fnmatch($download->file()->value(), $candidate['name']) === true) {
                    $asset = $candidate;
                    break;
                }
            }

            $url = $asset['url'] ?? $fallback;

            if ($url === null) {
                continue;
            }

            $links[] = [
                'label'   => $download->label()->value(),
                'text'    => $download->text()->value(),
                'kind'    => $download->kind()->or('installer')->value(),
                'primary' => $download->primary()->toBool(),
                'url'     => $url,
                'file'    => $asset['name'] ?? null,
                'size'    => $asset !== null ? F::niceSize($asset['size'], 'en_US') : null,
            ];
        }

        return $links;
    }

    public static function isRepo(string $repo): bool
    {
        return preg_match('!^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$!', $repo) === 1;
    }

    /** Only links into github.com are used, so the page never links anywhere unexpected. */
    protected static function isGitHubUrl(mixed $url): bool
    {
        return is_string($url) === true && str_starts_with($url, 'https://github.com/') === true;
    }

    protected static function fetch(string $repo): ?array
    {
        try {
            $response = Remote::get('https://api.github.com/repos/' . $repo . '/releases/latest', [
                'agent'   => 'stackwerk.co (+https://stackwerk.co)',
                'timeout' => 5,
                'headers' => [
                    'Accept'               => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
            ]);
        } catch (Throwable) {
            return null;
        }

        if ($response->code() !== 200) {
            return null;
        }

        $data = $response->json();

        if (
            is_array($data) === false ||
            empty($data['tag_name']) === true ||
            static::isGitHubUrl($data['html_url'] ?? null) === false
        ) {
            return null;
        }

        $assets = [];

        foreach ($data['assets'] ?? [] as $asset) {
            if (empty($asset['name']) === false && static::isGitHubUrl($asset['browser_download_url'] ?? null) === true) {
                $assets[] = [
                    'name' => (string)$asset['name'],
                    'url'  => (string)$asset['browser_download_url'],
                    'size' => (int)($asset['size'] ?? 0),
                ];
            }
        }

        $published = strtotime($data['published_at'] ?? '');

        return [
            'version'   => ltrim((string)$data['tag_name'], 'vV'),
            'name'      => (string)($data['name'] ?: $data['tag_name']),
            'url'       => (string)$data['html_url'],
            'published' => $published === false ? null : $published,
            'assets'    => $assets,
        ];
    }
}
