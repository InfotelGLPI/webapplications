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
  DROP `create_appweb`;
ALTER TABLE `glpi_plugin_appweb_profiles`
  DROP `update_appweb`;
ALTER TABLE `glpi_plugin_appweb_profiles`
  DROP `delete_appweb`;
ALTER TABLE `glpi_plugin_appweb_profiles`
  CHANGE `is_default` `is_default` SMALLINT(6) NOT NULL DEFAULT '0';
UPDATE `glpi_plugin_appweb_profiles`
SET `is_default` = '0'
WHERE `is_default` = '1';
UPDATE `glpi_plugin_appweb_profiles`
SET `is_default` = '1'
WHERE `is_default` = '2';

ALTER TABLE `glpi_plugin_appweb`
  ADD `location` INT(4) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_entities` INT(11) NOT NULL DEFAULT '0'
  AFTER `ID`;
ALTER TABLE `glpi_plugin_appweb`
  CHANGE `id_editor` `FK_enterprise` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_appweb`
  CHANGE `notes` `notes` LONGTEXT;
ALTER TABLE `glpi_plugin_appweb`
  CHANGE `deleted` `deleted` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_appweb`
  CHANGE `target` `target` SMALLINT(6) NOT NULL DEFAULT '0';

UPDATE `glpi_plugin_appweb`
SET `deleted` = '0'
WHERE `deleted` = '1';
UPDATE `glpi_plugin_appweb`
SET `deleted` = '1'
WHERE `deleted` = '2';
UPDATE `glpi_plugin_appweb`
SET `target` = '0'
WHERE `target` = '1';
UPDATE `glpi_plugin_appweb`
SET `target` = '1'
WHERE `target` = '2';

DROP TABLE `glpi_plugin_appweb_setup`;

ALTER TABLE `glpi_dropdown_plugin_appweb_type`
  ADD `FK_entities` INT(11) NOT NULL DEFAULT '0'
  AFTER `ID`;

INSERT INTO glpi_documents_items (documents_id, items_id, itemtype) SELECT
                                                                      FK_documents,
                                                                      FK_applications,
                                                                      '1300'
                                                                    FROM glpi_plugin_appweb_documents;

DROP TABLE `glpi_plugin_appweb_documents`;

ALTER TABLE `glpi_plugin_appweb_device`
  CHANGE `device_type` `device_type` INT(11) NOT NULL DEFAULT '0';

ALTER TABLE `glpi_plugin_appweb`
  ADD `FK_glpi_enterprise` SMALLINT(6) NOT NULL DEFAULT '0'
  AFTER `FK_enterprise`;


INSERT INTO `glpi_displaypreferences` VALUES (NULL, '1300', '2', '2', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, '1300', '6', '3', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, '1300', '7', '4', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, '1300', '8', '5', '0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL, '1300', '12', '6', '0');