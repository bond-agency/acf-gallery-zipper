# ACF Gallery Zipper

![release](https://img.shields.io/github/release/bond-agency/acf-gallery-zipper.svg)

WordPress plugin for zipping ACF Gallery field contents.

## Requirements

This plugin assumes that you have [ACF](https://www.advancedcustomfields.com/) installed.

## Installation

Download the [zip](https://github.com/bond-agency/acf-gallery-zipper/archive/master.zip) and install the plugin. Use the [hooks](#filters-actions-api) to define the post type and field you want to zip.

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
  rerurn 'my_gallery_field_name';
}
```

## Contributing

We are open for suggestions so open an issue if you have any feature requests or bug reports. Please do not create a pull request without creating related issue first.