# GLPI Development Helper Plugin
[![CodeFactor](https://www.codefactor.io/repository/github/cconard96/glpi-dev-plugin/badge)](https://www.codefactor.io/repository/github/cconard96/glpi-dev-plugin)

Provides a few tools to help GLPI and GLPI plugin developers.

## Requirements
- GLPI >= 9.5.0
- GLPI must be in debug mode, and your user must have the Update privilege for 'Config'.

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
