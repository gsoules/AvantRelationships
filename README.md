# AvantRelationships (plugin for Omeka Classic)

**IMPORTANT: This plugin is being prepared and documented for sharing on GitHub, but please do not use it just yet. It should be ready by the end of September 2017. Thanks for your patience.**


The AvantRelationships plugin was developed for the [Southwest Harbor Public Library](http://swhplibrary.org) to visually display real-world relationships between items in an Omeka database. When you view an item, thumbnails of all its related items appear on the same page. The page also shows a graphical visualization depicting the relationships among items.

Visit the library's [Digital Archive](http://swhplibrary.net/archive) to see this plugin in action.

## Installation

1. Unzip AvantRelationships-master file into the plugin directory.

2. Rename the folder to AvantRelationships.

3. Activate the plugin from the Admin → Settings → Plugins page.

4. Configure the plugin or accept the defaults.

## Dependencies
The following open source libraries are included with this plugin. Click the links below to see copyrights and licenses for each. 
* [Cytoscape.js](http://js.cytoscape.org/) - Graph theory / network library for analysis and visualisation
* [Dagre](https://github.com/cytoscape/cytoscape.js-dagre) DAG (directed acyclic graph) for Cytoscape.js
* [CoSE Bilkent](https://github.com/cytoscape/cytoscape.js-cose-bilkent) layout for Cytoscape.js
* [Magnific Popup](https://github.com/dimsemenov/Magnific-Popup/) lightbox for jQuery

## Usage
*This section is in the process of being written*

### Filters and Hooks
* Hook: show_relationships_visualization
*     <?php fire_plugin_hook('show_relationships_visualization', array('view' => $this, 'item' => $item)); ?>
* Filter: custom_relationships
*       $nodes = apply_filters('custom_relationships', $nodes, array('item' => $this->primaryItem, 'tree' => $this));

### Relationship Types
#### Source and Target Rules
#### Source and Target Labels
#### Directives
#### Ancestry

### Relationship Rules
#### Determiner
#### Descriptions
#### Rule

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Copyright
---------

* Created by [gsoules](https://github.com/gsoules) for the Southwest Harbor Public Library [Digital Archive](http://swhplibrary.net/archive)
* Copyright George Soules, 2016-2017.
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information.

## Acknowledgments

* [John S. and James L. Knight Foundation](https://knightfoundation.org/) for funding development of this plugin.

