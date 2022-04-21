# Vimeo: Omeka S module

In a freshly-installed Omeka S setup, Vimeo videos can be embedded via oEmbed. This module builds on that functionality by providing an interactive transcript UI and ingesting higher resolution thumbnails.

## Account Requirement

As of version 3.0, you must have a Vimeo plan that includes third-party player support (Pro or higher). If this is not feasible for your project, previous versions are still available for download, albeit with no long-term support.

## Installation

Use the zipped releases provided on GitHub for a standard install.

You may also clone the git repository, rename the folder to `VimeoEmbed`, and build from source with:

```
composer install --no-dev
npm install
gulp
```

## Interoperability

This module is compatible with the [CSVImport](https://github.com/omeka-s-modules/CSVImport) and [AmazonS3](https://github.com/Daniel-KM/Omeka-S-module-AmazonS3) modules. In order to use the latter, you must [setup a CORS policy](https://docs.aws.amazon.com/AmazonS3/latest/userguide/enabling-cors-examples.html) on your buckets.

## License

This module uses a GPLv3 license.