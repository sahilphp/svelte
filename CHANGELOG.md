# Changelog

## [Unreleased]

### Fixed

- The package is explicitly a library and refuses
  `composer create-project sahilphp/svelte` through
  `post-create-project-cmd`, exiting 1 with installation guidance.
