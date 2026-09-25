<?php

declare(strict_types=1);

/**
 * Insert a released version into CHANGELOG.md and re-point the Unreleased link.
 *
 * The update-changelog workflow does this for releases published by a person,
 * but it listens for the `release` event and GitHub does not raise those for a
 * release created with GITHUB_TOKEN - the same rule that stops a workflow from
 * triggering itself. So a release cut by automation has to write its own entry.
 *
 * Usage: php changelog-entry.php <version> <previous version> <date> <notes file>
 */
if ($argc < 5) {
    fwrite(STDERR, "Usage: php changelog-entry.php <version> <previous> <date> <notes file>\n");
    exit(1);
}

[, $version, $previous, $date, $notesFile] = $argv;

$repository = 'https://github.com/magnusvin/adminer-laravel-bridge';
$changelogFile = 'CHANGELOG.md';

$changelog = file_get_contents($changelogFile);
$notes = trim((string) file_get_contents($notesFile));

if ($changelog === false || $notes === '') {
    fwrite(STDERR, "Could not read the changelog or the notes\n");
    exit(1);
}

if (str_contains($changelog, "## [{$version}]")) {
    echo "CHANGELOG already has an entry for {$version}\n";
    exit(0);
}

// Re-point Unreleased at the version being released. The heading is rewritten
// wholesale rather than pattern-matched in place: the versions here contain dots
// and the URL contains slashes and colons, and a regex spanning both is more
// ways to be subtly wrong than this is worth.
$lines = explode("\n", $changelog);
$unreleasedAt = null;
$previousAt = null;

foreach ($lines as $index => $line) {
    if ($unreleasedAt === null && str_starts_with($line, '## [Unreleased]')) {
        $unreleasedAt = $index;
    }

    if ($previousAt === null && str_starts_with($line, "## [{$previous}]")) {
        $previousAt = $index;
    }
}

if ($unreleasedAt === null) {
    fwrite(STDERR, "Could not find the Unreleased heading\n");
    exit(1);
}

if ($previousAt === null) {
    fwrite(STDERR, "Could not find the {$previous} heading to insert above\n");
    exit(1);
}

$lines[$unreleasedAt] = "## [Unreleased]({$repository}/commits/main/compare/{$version}...HEAD)";

$entry = [
    "## [{$version}]({$repository}/commits/main/compare/{$previous}...{$version}) - {$date}",
    '',
    ...explode("\n", $notes),
    '',
];

array_splice($lines, $previousAt, 0, $entry);

file_put_contents($changelogFile, implode("\n", $lines));

echo "Added {$version} to CHANGELOG.md\n";
