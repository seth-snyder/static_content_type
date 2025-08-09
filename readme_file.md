# Static Content Type Module

The Static Content Type module provides a way to author and create content
outside of the Drupal environment using any available tools and AI to create
it. This module allows you to get off the "Drupal Island" and use modern
frontend technologies like React, Vue, Angular, Svelte, etc.

## Features

- **Static Content Nodes**: Create content pages that render HTML from external
  files
- **Static Blocks**: Create blocks that display external HTML content
- **Static Paragraphs**: Create paragraph entities that render external HTML
- **Twig Extensions**: Use static content in any Twig template
- **Single Directory Components (SDC)**: Component-based static content
  rendering
- **Multiple Rendering Options**: Proxied, Raw, or iFrame rendering modes

## Installation

1. Copy the module to your `modules/custom/` directory
2. Enable the module: `drush en static_content_type`
3. Configure permissions at `/admin/people/permissions`
4. Configure rendering options at
   `/admin/config/content/settings/static_content_type`

## Directory Structure

The module creates the following directories in your public files area:

```
sites/default/files/
├── static-content-nodes/
├── static-content-blocks/
├── static-content-paragraphs/
├── static-content-sdc/
├── static-content-twig/
└── static-content-pages/
```

## Usage

### Static Content Nodes

1. Create a new "Static Content" node
2. Save it and note the node ID (e.g., 1234)
3. Create directory: `static-content-nodes/1234/`
4. Place your HTML file: `static-content-nodes/1234/index.html`
5. Add any assets (CSS, JS, images) in the same directory

### Static Blocks

1. Create a new "Static Block" custom block
2. Save it and note the block ID
3. Create directory: `static-content-blocks/[ID]/`
4. Place your HTML file: `static-content-blocks/[ID]/index.html`
5. Use the "Static Block" block plugin to display it

### Static Paragraphs

1. Create a new "Static Paragraph" paragraph
2. Save it and note the paragraph ID
3. Create directory: `static-content-paragraphs/[ID]/`
4. Place your HTML file: `static-content-paragraphs/[ID]/index.html`

### Twig Extension Usage

Use the Twig function in any template:

```twig
{# Basic usage #}
{{ static_content_type_loader('1234', 'proxied', 'static-content-nodes') }}

{# In a wrapper div #}
<div class="content-display">
  {{ static_content_type_loader('my-content', 'raw', 'static-content-twig') }}
</div>
```

### Single Directory Component (SDC) Usage

```twig
{# Include the component #}
{{ include('static_content_type:static_content', { 
    id: '1234', 
    option: 'proxied', 
    location: 'static-content-sdc' 
}) }}

{# Embed the component #}
{% embed 'static_content_type:static_content' with { 
    id: 'my-custom-id', 
    option: 'iframe', 
    location: 'static-content-pages' 
} %}
{% endembed %}
```

## Rendering Options

### Proxied (Default)
- Automatically modifies relative paths in HTML
- Adds the full path to images, stylesheets, and links
- Best for content developed outside of Drupal

### Raw  
- Uses HTML content exactly as-is
- No path modifications
- Best for content developed directly in the Drupal directory structure

### iFrame
- Renders content in an iframe
- Auto-resizes to content height when possible
- Isolated from the main page but links won't navigate the main browser window

## Configuration

Configure rendering options for each content type at:
`/admin/config/content/settings/static_content_type`

Each directory type can have its own default rendering option.

## File Structure Example

```
sites/default/files/static-content-nodes/1234/
├── index.html          (required)
├── styles.css
├── script.js
├── images/
│   ├── hero.jpg
│   └── logo.png
└── assets/
    └── font.woff2
```

## Error Handling

- **Anonymous users**: See "Temporarily Unavailable" message
- **Admin users**: See detailed error messages and paths
- **Logging**: Errors are logged to the Drupal log system

## Permissions

- `administer static content type`: Configure module settings and see detailed
  error messages

## Requirements

- Drupal 11
- Paragraphs module (for paragraph functionality)
- Custom Block module (for block functionality)

## Development Workflow

1. Create your static content using any tools/framework
2. Build/export to plain HTML, CSS, JS
3. Create corresponding Drupal entity (node/block/paragraph)
4. Upload content to the numbered directory
5. Content is immediately available on your Drupal site

This enables rapid development using modern tools while leveraging Drupal's
content management capabilities.
