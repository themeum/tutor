# 0001. Bound the Learning Area within an enabled theme shell

## Status

Accepted

## Context

Learning Area previously used viewport-fixed course chrome. Rendering an active
theme header or footer around it allowed the sidebar to overlap either element,
and changing the course header to sticky left desktop content with a duplicate
top offset. Theme header heights and positioning vary by theme.

## Decision

When either Learning Area theme-shell setting is enabled, the Learning Area uses
an in-flow grid with a sticky sidebar bounded by the Learning Area. A small
client controller measures any fixed or sticky theme header and exposes its
viewport obstruction through a CSS custom property. Themes with non-standard
header markup can override the selector with
`tutor_learning_area_theme_header_selector`.

The isolated Learning Area retains its existing fixed layout. Fixed theme
footers are outside this compatibility contract; regular document-flow footers
follow the bounded Learning Area.

## Consequences

The layout works with ordinary theme footers without measuring their height and
does not require a hard-coded theme-header offset. Theme authors using unusual
header markup have one explicit integration point.
