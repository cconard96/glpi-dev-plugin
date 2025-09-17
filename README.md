# GLPI Development Helper Plugin
[![CodeFactor](https://www.codefactor.io/repository/github/cconard96/glpi-dev-plugin/badge)](https://www.codefactor.io/repository/github/cconard96/glpi-dev-plugin)

## /!\ This plugin is no longer supported /!\
As of GLPI 10.0.18, most of the functionality from this plugin was migrated into GLPI itself in the new debug toolbar.
The remaining main feature of listing search options was integrated into GLPI's debug toolbar for GLPI 11.

Provides a few tools to help GLPI and GLPI plugin developers.

## Requirements
- GLPI 10.0.X
- Your GLPI user must have debug mode turned on

## Tools
 - Class viewer
   - Display general information for a class such as the display name (based on current language) and icon.
   - Display search options for a class. Since search options can be added in many places including plugins, it is nice to have a single place to view all the options for a class.
   - Ability to jump to the related Table (DB table schema viewer tool).
 - DB table schema viewer
   - List all GLPI and plugin tables and their schema.
   - Ability to jump to the related Class/Itemtype (Class viewer tool).
   - Provides helpful text for polymorphic relation fields (`items_id`). This lets developers know that the table/itemtype it links to depends on the `itemtype` column.
   - Allows developers to quickly jump to the schema or class view for any foreign key fields.
 - Plugin creator
   - Provides a minimalist way to initialize a new plugin from the UI.
 - Profiler
    - Adds a PluginDevProfiler class that can be used to profile sections of code.
    - Adds a dashboard for displaying the statistics of profiled code sections.
 - DOM Validation
    - Adds a continual DOM validation checker that will alert the user if the DOM has any elements that violate specific rules.
      Currently, these rules include:
      - Elements with a duplicate ID
      - Elements with a backslash in the ID, Name or Class attributes (This can cause unexpected issues with selectors)
