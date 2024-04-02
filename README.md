Linked Open Data Resolver
=========================

This is the initial code for looking up related links based on LOD identifiers

Status
------
This service is still very much work in progress.

As a proof of concept, it provides a SeeAlso-Service (https://verbundwiki.gbv.de/display/VZG/SeeAlso)
based on Entity Facts (https://www.dnb.de/EN/Professionell/Metadatendienste/Datenbezug/Entity-Facts/entityFacts_node.html)
at /seealso/entityfacts/gnd?id={GND]

Installation
------------
### Requirements

- PHP 8.1 or higher

### Fetch dependencies

- composer install

### Adjust Local Settings

- vi .env.local (not commited)

### Directory Permissions for cache and logs
On Ubuntu (or similar systems), you can set

- sudo setfacl -R -m u:www-data:rwX ./var
- sudo setfacl -dR -m u:www-data:rwX ./var

License
-------
    Linked Open Data Resolver

    (C) 2024 Daniel Burckhardt

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
