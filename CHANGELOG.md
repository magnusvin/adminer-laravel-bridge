# Release Notes

## [Unreleased](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.2.0.0...HEAD)

No unreleased changes yet. See [Versioning](README.md#versioning) for how release version numbers are chosen.

## [6.2.0.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.1.2.0...6.2.0.0) - 2026-10-24

Ships **Adminer 6.2.0** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.2.0/CHANGELOG.md)).

### Adminer 6.2.0

- Use the page font instead of monospace in &lt;code> except SQL
- Keep the leading newline of values in &lt;textarea>
- Select: Don't link a URL followed by text
- Edit: Fix the SQL function with a syntax highlighted textarea, keep newlines
- Edit: Fix JavaScript error with a nullable boolean column
- Alter table: Label the auto-increment column A_I instead of AI (bug #1360)
- Alter table: Set the default value as an SQL expression by DEFAULT SQL
- Create database: Search collations (bug #1362)
- Database schema: Don't widen tables by others in the column (regression from 6.0.1)
- Foreign key: Hide system databases and schemas
- Foreign key: Set the name
- SQL textarea: Keep the indentation on Enter
- SQL command: Mark the line of the first error
- MySQL, MariaDB: Keep the precision of ON UPDATE CURRENT_TIMESTAMP
- PostgreSQL: Import \connect
- PostgreSQL: Set collation in Create database
- PostgreSQL: Edit rows with a column of an extension type outside search_path
- PostgreSQL: Create and drop indexes concurrently
- PostgreSQL: Drop a unique index without a constraint
- PostgreSQL: Save binary data with standard_conforming_strings off
- PostgreSQL: Fix inserting the CURRENT_DATE default
- PostgreSQL: Don't report SELECT without columns as affected rows (fix #1365)
- PostgreSQL PDO: Compute the number of tables in databases
- PostgreSQL PDO: Export databases from the server level
- PostgreSQL, MS SQL, Oracle: Mark and recreate invalid indexes
- SQLite: Name checks and keep their names when altering the table
- SQLite: Fix recreating a table referenced by foreign keys or triggers
- SQLite: Keep the declared column types in Alter table
- SQLite: Offer current_date and current_timestamp in Insert
- SQLite: Display and edit the comments of tables and columns written as -- after the definitions
- SQLite: Don't report the affected rows of the previous statement after DROP TABLE and other statements
- SQLite 3.53+: Alter checks without recreating the table
- MS SQL: Alter an index without renaming it
- MS SQL: Drop a unique constraint in Alter indexes
- Oracle: Link documentation of data dictionary views
- Oracle: Fix inserting the CURRENT_TIMESTAMP default
- Firebird: Support PDO_Firebird, drop the unmaintained InterBase extension
- ClickHouse, Elasticsearch, IGDB, SimpleDB: Don't escape PHP errors twice with html_errors
- Redis: Report a closed connection and a timeout
- Editor: Hide the hint for now()
### Plugins
- Plugin::$optional to let users switch the plugin in AdminerConfig, Plugin::disable() to disable it by default
- Plugin config: Switch the optional plugins
- Drivers: Field key 'doc' to link documentation from column names in select and table structure
- Drivers: Field key 'default_sql' to mark the default value as an SQL expression
- Method Driver::errorLine() to mark the line of an error in SQL command
- Attribute data-error-line of the SQL &lt;textarea> for syntax highlighters to mark the line with an error
### Internal
- ESLint: Check the formatting by @stylistic/eslint-plugin
### Changed in the bridge

Nothing. This release moves the `vrana/adminer` pin and nothing else.
It was cut automatically once the full test matrix and the asset smoke
test passed against Adminer 6.2.0. Those cover the package's wiring,
routing and asset serving; they do not exercise Adminer's own UI behind a
database connection, so read the upstream changelog above for what changed
inside Adminer itself.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.1.2.0...6.2.0.0

## [6.1.2.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.1.1.0...6.1.2.0) - 2026-10-11

Ships **Adminer 6.1.2** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.1.2/CHANGELOG.md)).

### Adminer 6.1.2

- Select: Quote again the quoted values of IN
- Select: Accept the unquoted values of IN in parentheses (bug #1350, regression from 6.0.1)
- Select: Edit the stored value by Ctrl+click on a non-original value (bug #1344)
- Select: Search by BETWEEN (bug #1346)
- Select: Report an unknown column in search instead of a fatal error
- Export: Print the error also in CSV
- Export: Skip routines with a hidden definition instead of creating them without the body
- Login: Report invalid credentials for a wrong password required by Adminer\Password (bug #1351)
- Alter foreign key: Keep the original key if altering it fails (regression from 5.0.0)
- MySQL, SQLite: Search by & (bug #1346)
- PostgreSQL: List the triggers of a trigger function instead of calling it
- PostgreSQL: Alter only the changed parts of a column
- PostgreSQL, MS SQL: Display, edit and export the schema comment
- CockroachDB: Keep the original view, type or check if altering it fails
- MS SQL: Save the comments of new and renamed columns
- MS SQL: Save the table comment
- MS SQL: List the tables also if a table has another extended property than the comment
- MS SQL: Export the comments of tables and columns, the column comments made the export invalid
- IGDB, SimpleDB: Fix export of nested values
- Editor: Offer the row with the typed ID first when searching a foreign key, fix the search in MS SQL and Oracle
### Plugins
- Method formatSizeValue() to format the sizes of tables, indexes and databases
- Method Driver::routineTriggers()
- Method selectColumnInput() to customize choosing a column in select
- Method selectSearchCondition() to customize the SQL of a search operator
- Methods commentValue() and commentInput() with type 'SCHEMA'
- Method sqlUrlLength() to limit the length of URLs with the query from SQL command (bug #1358)
- New plugin select-expression: Type the columns in select with suggestions, also expressions like a+b
- New plugin sql-url-length: Limit the length of URLs with the query from SQL command, some servers reject them
### Changed in the bridge

Nothing. This release moves the `vrana/adminer` pin and nothing else.
It was cut automatically once the full test matrix and the asset smoke
test passed against Adminer 6.1.2. Those cover the package's wiring,
routing and asset serving; they do not exercise Adminer's own UI behind a
database connection, so read the upstream changelog above for what changed
inside Adminer itself.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.1.1.0...6.1.2.0

## [6.1.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.1.0.0...6.1.1.0) - 2026-09-25

Ships **Adminer 6.1.1** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.1.1/CHANGELOG.md)).

### Adminer 6.1.1

- Fix editing identity (PostgreSQL, Oracle, Firebird), rowversion (MS SQL), key (ClickHouse)
- Select: Fix modifying rows with a NULL value (uncompiled only, regression from 6.1.0)
- Edit: Do not round big numbers in json (MySQL) and jsonb (PostgreSQL) when saving the row (regression from 5.4.2)
- DB overview: Keep the table names on the screen when scrolling the list of tables horizontally
- Select: Keep the checkboxes on the screen when scrolling horizontally
- Select: Enable saving the modified values again after an error (regression from 6.0.0)
- Select: Highlight the searched text
- Select: Search anywhere by IS NULL and IS NOT NULL without a value
- Show documentation links as 📖, link it from the heading
- Menu: Group system databases and schemas
- Trigger: Name a new trigger with more events by all of them
- PDO: Preserve the exact value of double (bug #1214)
- PostgreSQL, MS SQL, Oracle: Do not highlight a valid date or time value as too long
- PostgreSQL: Alter the trigger function from table structure
- PostgreSQL: Do not highlight an array value as too long
- PostgreSQL: Fix renaming a column and making it a foreign key at the same time
- PostgreSQL: Export and alter the default values of enum and array columns (bug #1342)
- PostgreSQL: Edit the enum values containing an apostrophe
- PostgreSQL: Alter table: Offer and quote the user types with uppercase letters
- SQLite: Disallow ATTACH and VACUUM INTO prefixed by BOM (GHSA-r9r5-j5q8-8c59)
- SQLite: Deny writing files by an authorizer (PHP 8.0+, PDO PHP 8.5+)
- MS SQL: Export and alter the precision of datetime2, time and datetimeoffset
- MS SQL: Keep fractional seconds and the time zone offset when saving a row with the sqlsrv extension
- MS SQL Dblib: Display uniqueidentifier (bug #1339)
- MS SQL Dblib: Show the server version
- MS SQL Dblib: Fix creating generated columns and indexed views
- MS SQL Dblib: SQL command: Rollback an unfinished transaction
- Oracle: Treat SYS and SYSTEM as read-only
- Oracle: Support identity columns
- Oracle: Alter timestamp and interval columns, display their type without a duplicate precision
- ClickHouse: Offer the geometry types, Time and Time64, group the types as in the other drivers
- ClickHouse: Display the number of rows to a read-only user
- ClickHouse: Shorten the links to rows with a long string or geometry by its MD5 hash
- ClickHouse: Do not round big integers and decimals, e.g. UInt64 or Decimal128, display empty maps and maps with numeric keys as objects
- ClickHouse: Save the decimals in Array, Map and Tuple exactly, save a Map with other than String keys
- ClickHouse: Do not turn \b into a backspace (regression from 6.0.0)
- Editor: Describe foreign keys by the first string column, not only varchar
- Editor: Shorten long descriptions of foreign keys
### Plugins
- Driver plugins: Driver::disconnect() to forget the logged user
- Driver plugins: Connect to the privileged ports of the driver's own protocol (ClickHouse, Elasticsearch, IMAP)
- Driver plugins: Driver::hasEstimatedRows() to recount the rows only after an estimate
- Driver plugins: Driver::md5() to shorten the links to rows with a long value
- Driver plugins: Driver::isSystem() to group the system databases and schemas
- Method namePattern() to customize the names of new indexes, foreign keys, checks and triggers
- Plugin igdb: Log in by the client secret instead of an access token obtained manually
- New plugin select-foreign: Display the first string column of the referenced row instead of a foreign key value, same as in Adminer Editor
- New plugin name-patterns: Name new indexes, foreign keys, checks and triggers by your own convention
### Changed in the bridge

Nothing. This release moves the `vrana/adminer` pin and nothing else.
It was cut automatically once the full test matrix and the asset smoke
test passed against Adminer 6.1.1. Those cover the package's wiring,
routing and asset serving; they do not exercise Adminer's own UI behind a
database connection, so read the upstream changelog above for what changed
inside Adminer itself.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.1.0.0...6.1.1.0

## [6.1.0.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.2.0...6.1.0.0) - 2026-09-24

Ships **Adminer 6.1.0** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.1.0/CHANGELOG.md)).

### Adminer 6.1.0

The Oracle driver is out of beta and now uses schemas as databases, with triggers, database creation, well-known-text geometries and ISO dates. MS SQL can display, create, alter, drop and call procedures and functions. PostgreSQL shows PostGIS geometries as EWKT and lists installed extensions on the database page. Export can qualify all names by schema or export the schema itself. Adminer can be installed as a web application, and SQL command results can be modified in place by Ctrl+click.

### Changed in the bridge

Nothing in the code — but two upstream changes are visible through it:

- **`adminer/static/logo.png` is now `logo.svg`.** The bridge serves whatever Adminer links to and never names the file, so no change was needed here. If your application asserts on `/adminer/static/logo.png`, that path now returns 404 and you should expect `logo.svg` (`image/svg+xml`) instead. This is the kind of drift the exact `vrana/adminer` pin introduced in 6.0.0.1 exists to keep out of your `composer update` — see [Versioning](https://github.com/magnusvin/adminer-laravel-bridge#versioning) for picking a constraint that matches how much movement you want.
- **A web app manifest** is served by Adminer at `?manifest=`, through the main route, so it inherits your configured middleware and guard. Its `start_url`, `scope` and icon are all relative and resolve under your configured prefix.

The service worker added in 6.0.2 still is not registered: that is gated on `Adminer\DIR` being undefined, and the bridge serves the development sources.

### Tests

The asset routes — the part of this bridge upstream keeps moving under it — are now covered directly: every static file the pinned release ships must serve, CSS and JS carry asserted content types, the logo must serve as an image without naming its extension, multi-segment route prefixes are exercised, and a configured guard must not lock the assets away from the login form it renders.

Verified against a running workbench: every linked asset serves, `logo.svg` comes back as `image/svg+xml`, and the manifest is valid JSON with `application/manifest+json`.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.2.0...6.1.0.0

## [6.0.2.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.1.0...6.0.2.0) - 2026-09-24

Ships **Adminer 6.0.2** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.0.2/CHANGELOG.md)). **Security release — upgrading is recommended.**

### Adminer 6.0.2

Two security fixes:

- [GHSA-rwxg-xph9-82cj](https://github.com/vrana/adminer/security/advisories/GHSA-rwxg-xph9-82cj) — not all parts of the server name were verified before connecting (a regression introduced in Adminer 5.5.1).
- [GHSA-7c5h-q78c-fpxx](https://github.com/vrana/adminer/security/advisories/GHSA-7c5h-q78c-fpxx) — XSS through the connection id returned by the server.

Also: numbers are formatted according to the conventions of the selected language, routines are altered through a form instead of by editing the definition, autocomplete got faster and reaches further, RTL layouts mirror borders and spacing, and MySQL check constraint clauses display without `information_schema`'s extra escaping.

### Changed in the bridge

Nothing. 6.0.2 adds `adminer/static/worker.js` to cache the assets of the compiled build, but `service_worker()` skips registration whenever `Adminer\DIR` is defined — which it is here, since the bridge serves the development sources — so nothing requests it.

Verified against a running workbench: every asset the page links still serves, and no service worker is registered.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.1.0...6.0.2.0

## [6.0.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.1...6.0.1.0) - 2026-09-24

Ships **Adminer 6.0.1** ([upstream changelog](https://github.com/vrana/adminer/blob/v6.0.1/CHANGELOG.md)).

### Adminer 6.0.1

Security and session handling: the CSRF token is now verified when logging in and before killing a client-side timed-out query, the session cookie is sent with `SameSite=lax`, and an invalid CSRF token or oversized POST returns an error status instead of a normal page. Elasticsearch and MS SQL got substantial driver work, OpenSearch is supported, the database schema page arranges tables by their foreign keys, and Select can modify or delete several rows in a transaction.

### Changed in the bridge

Adminer 6.0.1 made its development version runnable from the `adminer/` directory under any name, which changed every hardcoded asset link from `../adminer/static/…` to `./static/…`, and moved jush out of `externals/` into `adminer/static/jush`. Both needed handling here:

- **The static asset route moved under the configured prefix.** It used to be anchored one level above it to match Adminer's `../adminer/static/…` links. For the default `adminer` prefix the resulting URL is unchanged. If you run Adminer under a nested prefix such as `tools/db`, assets now resolve correctly instead of pointing at a sibling directory.
- **jush is served through that same route.** The dedicated `adminer-bridge.jush` route is gone, along with the output rewriting in `head()` and `syntaxHighlighting()` that used to redirect Adminer's `../externals/jush/…` links. If you referenced `route('adminer-bridge.jush', …)` directly, use `route('adminer-bridge.static', ['file' => 'jush/…'])` instead.

Note that `adminer/static/jush` is a Git submodule in Adminer's tree, so the Composer package ships it empty — requests under that prefix are resolved against the separate `vrana/jush` package. That indirection is why this needed bridge work rather than just a version bump.

Verified against a running workbench: `default.css`, `dark.css`, `functions.js`, `editing.js`, `logo.png` and the jush CSS and modules all serve with the expected content types.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.0.1...6.0.1.0

## [6.0.0.1](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/6.0.0.0...6.0.0.1) - 2026-09-24

### Changed

- **`vrana/adminer` is now pinned to an exact version** (`6.0.0` instead of `^6.0`), and `vrana/jush` is constrained to patch releases only (`~3.1.0`).

This package's version scheme documents the first three segments as the Adminer release it ships, but `^6.0` let a consumer's `composer update` pull a newer Adminer minor or patch into an unchanged bridge release — so the mapping only held at the moment of install, and the bridge could silently end up running an Adminer it was never tested against.

That is not hypothetical. Adminer 6.1.0 renames `adminer/static/logo.png` to `logo.svg`, moves jush into `adminer/static/jush/`, and adds `worker.js` — enough to 404 static assets served through this bridge without a single line changing here.

Installing `magnusvin/adminer-laravel-bridge:6.0.0.*` now always gives you Adminer 6.0.0. Upstream Adminer releases reach you through a matching bridge release instead of arriving on their own.

No code or API changes — constraints and documentation only.

**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/6.0.0.0...6.0.0.1

## [6.0.0.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.1...6.0.0.0) - 2026-08-10

<!-- Release notes generated using configuration in .github/release.yml at 6.0.0.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.1...6.0.0.0

## [5.5.1.1](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/5.5.1.0...5.5.1.1) - 2026-07-30

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.1 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/compare/5.5.1.0...5.5.1.1

## [5.5.1.0](https://github.com/magnusvin/adminer-laravel-bridge/commits/main/compare/main...5.5.1.0) - 2026-07-23

<!-- Release notes generated using configuration in .github/release.yml at 5.5.1.0 -->
**Full Changelog**: https://github.com/magnusvin/adminer-laravel-bridge/commits/5.5.1.0
