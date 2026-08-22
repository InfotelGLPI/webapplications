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

ALTER TABLE `glpi_plugin_appweb`
  ADD `recursive` TINYINT(1) NOT NULL DEFAULT '0'
  AFTER `FK_entities`;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_appweb_protocol`;
CREATE TABLE `glpi_dropdown_plugin_appweb_protocol` (
  `ID`       INT(11)                 NOT NULL AUTO_INCREMENT,
  `name`     VARCHAR(255)
             COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `comments` TEXT,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_appweb_protocol` (`ID`, `name`, `comments`) VALUES ('1', 'http', '');
INSERT INTO `glpi_dropdown_plugin_appweb_protocol` (`ID`, `name`, `comments`) VALUES ('2', 'https', '');