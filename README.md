# Awesome Events

WordPress plugin for the **event** custom post type. REST/MCP-ready meta, archive filters, and frontend templates.

## Override templates in your theme

Copy files from the plugin into your theme (child theme recommended):

**From:** `wp-content/plugins/awesome-events/templates/`  
**To:** `wp-content/themes/your-theme/awesome-events/`

Example:

```
awesome-events/archive-event.php
awesome-events/single-event.php
awesome-events/event-filters.php
awesome-events/event-calendar.php
awesome-events/event-card.php
```

WordPress loads the theme copy first; if a file is missing, the plugin version is used.

After copying, edit only the files in your theme — plugin updates will not overwrite them.

## Events Loop block

In the block editor, add **Events Loop** (`awesome-events/events-loop`) to any page. Set how many upcoming events to show (1–24), optional heading, city/type filters, and an optional link to the full archive. Uses the same card grid as `/events/`.

## Useful URLs

- Archive: `/events/`
- Past events: `/events/?past=1`
- Filter by city: `/events/?event_city=genoa`
- Filter by type: `/events/?event_type=festival`
- Calendar month: `/events/?cal_month=2026-07`
