--
-- -------------------------------------------------------------------------
-- webapplications plugin for GLPI
-- Copyright (C) 2015-2026 by the webapplications Development Team.
--
-- https://github.com/InfotelGLPI/webapplications
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of webapplications.
--
-- webapplications is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- webapplications is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with webapplications. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

ALTER TABLE `glpi_plugin_appweb_profiles`
  DROP COLUMN `interface`,
  DROP COLUMN `is_default`;
ALTER TABLE `glpi_plugin_appweb`
  DROP COLUMN `target`,
  DROP COLUMN `link_name`,
  DROP COLUMN `port`,
  DROP COLUMN `protocol`;
ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_groups` INT(11) NOT NULL DEFAULT '0';
DROP TABLE `glpi_dropdown_plugin_appweb_protocol`;
ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_users` INT(4);
ALTER TABLE `glpi_plugin_appweb_profiles`
  ADD `open_ticket` CHAR(1) DEFAULT NULL;