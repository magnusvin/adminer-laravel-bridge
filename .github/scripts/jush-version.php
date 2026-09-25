<?php

declare(strict_types=1);

/**
 * Print the vrana/jush release that matches a given Adminer version.
 *
 * Adminer carries jush as a Git submodule pinned to an exact commit, and that
 * commit is frequently ahead of the newest tagged jush release - 6.1.0 and
 * 6.1.1 both bundle untagged commits. The Composer package can only depend on
 * releases, so the closest honest answer is the newest jush release that is not
 * newer than the commit Adminer pins.
 *
 * Getting this wrong is quiet: jush is served as static files, so a mismatched
 * version still returns 200 for every request and only misbehaves once the
 * browser runs it. This package shipped jush 3.1.0 alongside Adminer releases
 * bundling 3.2.x for exactly that reason.
 *
 * Usage: gh auth token | php jush-version.php <adminer version>
 */
if ($argc < 2) {
    fwrite(STDERR, "Usage: php jush-version.php <adminer version>\n");
    exit(1);
}

$adminerVersion = $argv[1];
$token = trim((string) stream_get_contents(STDIN));

/**
 * @return array<mixed>
 */
function api(string $path, string $token): array
{
    $context = stream_context_create(['http' => [
        'header' => implode("\r\n", array_filter([
            'User-Agent: adminer-laravel-bridge',
            'Accept: application/vnd.github+json',
            $token !== '' ? "Authorization: Bearer {$token}" : null,
        ])),
        'ignore_errors' => true,
    ]]);

    $body = @file_get_contents("https://api.github.com{$path}", false, $context);

    if ($body === false) {
        fwrite(STDERR, "Request failed: {$path}\n");
        exit(1);
    }

    $decoded = json_decode($body, true);

    if (! is_array($decoded)) {
        fwrite(STDERR, "Unexpected response for {$path}: {$body}\n");
        exit(1);
    }

    return $decoded;
}

function commitDate(string $sha, string $token): ?string
{
    $commit = api("/repos/vrana/jush/commits/{$sha}", $token);

    return $commit['commit']['committer']['date'] ?? null;
}

// The submodule entry reports the commit of jush that this Adminer tag pins.
$staticDirectory = api("/repos/vrana/adminer/contents/adminer/static?ref=v{$adminerVersion}", $token);
$submoduleSha = null;

foreach ($staticDirectory as $entry) {
    if (($entry['name'] ?? null) === 'jush') {
        $submoduleSha = $entry['sha'] ?? null;
        break;
    }
}

if ($submoduleSha === null) {
    fwrite(STDERR, "Adminer {$adminerVersion} has no adminer/static/jush submodule\n");
    exit(1);
}

$submoduleDate = commitDate($submoduleSha, $token);

if ($submoduleDate === null) {
    fwrite(STDERR, "Could not resolve the jush commit {$submoduleSha}\n");
    exit(1);
}

$best = null;

foreach (api('/repos/vrana/jush/tags', $token) as $tag) {
    $name = ltrim((string) ($tag['name'] ?? ''), 'v');

    if (preg_match('/^\d+\.\d+\.\d+$/', $name) !== 1) {
        continue;
    }

    $date = commitDate((string) $tag['commit']['sha'], $token);

    if ($date === null || strtotime($date) > strtotime($submoduleDate)) {
        continue;
    }

    if ($best === null || version_compare($name, $best, '>')) {
        $best = $name;
    }
}

if ($best === null) {
    fwrite(STDERR, "No jush release is old enough for Adminer {$adminerVersion}\n");
    exit(1);
}

echo $best;
