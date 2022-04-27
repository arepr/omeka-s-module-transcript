# Transcript: Omeka S module

This module provides an interactive transcript UI for subtitled video and audio material. It also features a custom media player which can be used without the transcript. The supported configurations are:

1. Vimeo
    - Video and subtitles uploaded to Vimeo
    - Utilizes adaptive bitrate streaming and multiple resolutions
    - The subtitles and thumbnail will be captured and stored locally
    - Thumbnails are higher resolution compared to oEmbed
2. WebVTT
    - Audio or video uploaded locally alongside subtitles
    - Source resolution only; not recommended for large files

## Installation

Use the zipped releases provided on GitHub for a standard install.

You may also clone the git repository, rename the folder to `Transcript`, and build from source with:

```
composer install --no-dev
npm install
gulp
```

## Getting Started

### Vimeo

As of version 3.0, you must have a Vimeo plan that includes third-party player support (Pro or higher). If this is not feasible for your project, previous versions are still available for download, albeit with no long-term support.

Create an API key at [developer.vimeo.com](https://developer.vimeo.com) with the `video_files` permission, and paste it into the module configuration form.

Before ingesting items into Omeka, upload your subtitles via Vimeo dashboard > Manage videos > Distribution > Subtitles. You should also set your custom video thumbnail at this time, if desired.

### WebVTT

No additional setup necessary, just start importing!

## Interoperability

This module is compatible with [CSVImport](https://github.com/omeka-s-modules/CSVImport), but only for Vimeo configurations.

In order to use this module in conjunction with [AmazonS3](https://github.com/Daniel-KM/Omeka-S-module-AmazonS3), you must [setup a CORS policy](https://docs.aws.amazon.com/AmazonS3/latest/userguide/enabling-cors-examples.html) on your buckets.

## License

This module uses a GPLv3 license.