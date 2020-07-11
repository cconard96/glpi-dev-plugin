# GLPI Development Helper Plugin

Provides a few tools to help GLPI and GLPI plugin developers.

## Requirements
- GLPI >= 9.5.0
- GLPI must be in debug mode, and your user must have the Update privilege for 'Config'.

## Tools
 - Class viewer
   - Display search options for a class. Since search options can be added in many different places including plugins, it is nice to have a single place to view all of the options for a class.
 - Plugin creator
   - Provides a minimalist way to initialize a new plugin from the UI.