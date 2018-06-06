# ACF Gallery Zipper

![release](https://img.shields.io/github/release/bond-agency/acf-gallery-zipper.svg)
![license](https://img.shields.io/github/license/bond-agency/acf-gallery-zipper.svg)

WordPress plugin for zipping ACF Gallery field contents.

## Requirements

This plugin assumes that you have [ACF](https://www.advancedcustomfields.com/) installed.

## Installation

Download the [zip](https://github.com/bond-agency/acf-gallery-zipper/archive/master.zip) and install the plugin. Use the [hooks](#filters--actions-api) to define the post type and field you want to zip.

## Usage

The plugin creates a REST endpoint for downloading zip files from the given post. You can get the ACF gallery field contents as zip by accessing that url:

```
<your-website-url>/wp-json/acf-gallery-zipper/v1/zip/<post-id>
```

## Filters & Actions API

The plugin provides a simple API for interacting with the plugin via WordPress hooks.

### Filters

#### acf_gallery_zipper_post_type

Use this filter to change the default post type. Default: `post`.

Example:

```php
add_filter( 'acf_gallery_zipper_post_type', 'acf_gallery_zipper_change_default_post_type' );
function acf_gallery_zipper_change_default_post_type( $post_type ) {
  return 'my-new-post-type';
}
```

#### acf_gallery_zipper_field_name

Use this filter to change the default field name that the plugin uses for zipping. Default: `gallery`.

Example:

```php
add_filter( 'acf_gallery_zipper_field_name', 'acf_gallery_zipper_change_default_field_name' );
function acf_gallery_zipper_change_default_field_name( $field_name ) {
  return 'my_gallery_field_name';
}
```

#### acf_gallery_zipper_filename

Use this filter to change the zip filename. Default: `<post slug>`.

Example:

```php
add_filter( 'acf_gallery_zipper_filename', 'acf_gallery_zipper_change_default_filename' );
function acf_gallery_zipper_change_default_filename( $filename ) {
  return $filename . '-suffix'; // .zip will be added automatically.
}
```

#### acf_gallery_zipper_use_cache

Use this filter to change the cache usage. By default the zip files are not stored anywhere but deleted right after they are sent to the user. Default: `false`.

Example:

```php
add_filter( 'acf_gallery_zipper_use_cache', 'acf_gallery_zipper_change_default_use_cache' );
function acf_gallery_zipper_change_default_use_cache( $use_cache ) {
  return true;
}
```

#### acf_gallery_zipper_removal_recurrence

Use this filter to change the cache removal recurrence. Default: `daily`. Possible values: `hourly`, `twicedaily` and `daily`.

Example:

```php
add_filter( 'acf_gallery_zipper_removal_recurrence', 'acf_gallery_zipper_removal_change_default_recurrence' );
function acf_gallery_zipper_removal_recurrence( $recurrence ) {
  return 'hourly';
}
```

## Contributing

We are open for suggestions so open an issue if you have any feature requests or bug reports. Please do not create a pull request without creating related issue first.