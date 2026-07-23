# Contribution Guide

Thank you for considering contributing to Adminer Bridge! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- This package doesn't use independent SemVer — versions track Adminer's own release cycle. See [Versioning](../README.md#versioning) in the README before proposing a version bump.

## Setup

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```
