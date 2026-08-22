<?php

/**
 * -------------------------------------------------------------------------
 * webapplications plugin for GLPI
 * Copyright (C) 2015-2026 by the webapplications Development Team.
 *
 * https://github.com/InfotelGLPI/webapplications
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of webapplications.
 *
 * webapplications is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * webapplications is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Webapplications;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

use CommonDBTM;
use CommonGLPI;
use Contract;
use Contract_Item;
use Document;
use Document_Item;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use ManualLink;
use Supplier;

/**
 * Class Appliance
 */
class Appliance extends CommonDBTM
{
    public static $rightname = "plugin_webapplications_appliances";
    public static function getTypeName($nb = 0)
    {
        return _n('Web application', 'Web applications', $nb, 'webapplications');
    }

    /**
     * @param $params
     */
    public static function addFields($params)
    {
        $item = $params['item'];
        $webapp_appliance = new self();
        $webapp_database = new DatabaseInstance();
        if ($item->getType() == 'Appliance') {
            if ($item->getID()) {
                $webapp_appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
            } else {
                $webapp_appliance->getEmpty();
            }

            $hasPicture = $item->hasItemtypeOrModelPictures();
            $options = [];
            $options['hasPicture'] = $hasPicture;

            TemplateRenderer::getInstance()->display('@webapplications/webapplication_appliance_form.html.twig', [
                'item' => $webapp_appliance,
                'params' => $options,
                'nbusers' => self::getNbUsers(),
            ]);
        } elseif ($item->getType() == 'DatabaseInstance') {
            if ($item->getID()) {
                $webapp_database->getFromDBByCrit(['databaseinstances_id' => $item->getID()]);
            } else {
                $webapp_database->getEmpty();
            }

            $options = [];

            if (isset($params["options"]["appliances_id"])) {
                $options = ['appliances_id' => $params["options"]["appliances_id"]];
            }

            TemplateRenderer::getInstance()->display('@webapplications/webapplication_database_form.html.twig', [
                'item' => $webapp_database,
                'params' => $options,
            ]);
        }
        return true;
    }

    public static function getNbUsers()
    {
        return [
            0 => Dropdown::EMPTY_VALUE,
            1 => __('1 to 100', 'webapplications'),
            2 => __('100 to 500', 'webapplications'),
            3 => __('500 to 1000', 'webapplications'),
            4 => __('1000 to 5000', 'webapplications'),
            5 => __('All users', 'webapplications'),
        ];
    }

    public static function getNbUsersValue($value)
    {
        $nb = [
            0 => Dropdown::EMPTY_VALUE,
            1 => __('1 to 100', 'webapplications'),
            2 => __('100 to 500', 'webapplications'),
            3 => __('500 to 1000', 'webapplications'),
            4 => __('1000 to 5000', 'webapplications'),
            5 => __('All users', 'webapplications'),
        ];
        return $value > 0 ? $nb[$value] : Dropdown::EMPTY_VALUE;
    }

    /**
     * @param \Appliance $item
     *
     * @return false
     */
    public static function applianceAdd(\Appliance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setAppliance($item);
    }

    /**
     * @param \Appliance $item
     *
     * @return false
     */
    public static function applianceUpdate(\Appliance $item)
    {
        if (!is_array($item->input) || !count($item->input)) {
            // Already cancel by another plugin
            return false;
        }
        self::setAppliance($item);
    }

    /**
     * @param \Appliance $item
     */
    public static function setAppliance(\Appliance $item)
    {
        $appliance = new Appliance();
        if (!empty($item->fields) && $item->getType() == 'Appliance') {
            $appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);

            if (is_array($appliance->fields) && count($appliance->fields) > 0) {

                $address = "";
                if (isset($item->input['address'])) {
                    $address = $item->input['address'];
                } elseif (isset($appliance->fields['address'])) {
                    $address = $appliance->fields['address'];
                }

                $backoffice = "";
                if (isset($item->input['backoffice'])) {
                    $backoffice = $item->input['backoffice'];
                } elseif (isset($appliance->fields['backoffice'])) {
                    $backoffice = $appliance->fields['backoffice'];
                }

                $number_users = 0;
                if (isset($item->input['number_users'])) {
                    $number_users = $item->input['number_users'];
                } elseif (isset($appliance->fields['number_users'])) {
                    $number_users = $appliance->fields['number_users'];
                }

                $version = "";
                if (isset($item->input['version'])) {
                    $version = $item->input['version'];
                } elseif (isset($appliance->fields['version'])) {
                    $version = $appliance->fields['version'];
                }

                $editor = 0;
                if (isset($item->input['editor'])) {
                    $editor = $item->input['editor'];
                } elseif (isset($appliance->fields['editor'])) {
                    $editor = $appliance->fields['editor'];
                }

                $webapplicationservertypes_id = 0;
                if (isset($item->input['webapplicationservertypes_id'])) {
                    $webapplicationservertypes_id = $item->input['webapplicationservertypes_id'];
                } elseif (isset($appliance->fields['webapplicationservertypes_id'])) {
                    $webapplicationservertypes_id = $appliance->fields['webapplicationservertypes_id'];
                }

                $webapplicationtechnics_id = 0;
                if (isset($item->input['webapplicationtechnics_id'])) {
                    $webapplicationtechnics_id = $item->input['webapplicationtechnics_id'];
                } elseif (isset($appliance->fields['webapplicationtechnics_id'])) {
                    $webapplicationtechnics_id = $appliance->fields['webapplicationtechnics_id'];
                }

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                } elseif (isset($appliance->fields['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $appliance->fields['webapplicationexternalexpositions_id'];
                }

                $webapplicationreferringdepartmentvalidation = 0;
                if (isset($item->input['webapplicationreferringdepartmentvalidation'])) {
                    $webapplicationreferringdepartmentvalidation = $item->input['webapplicationreferringdepartmentvalidation'];
                } elseif (isset($appliance->fields['webapplicationreferringdepartmentvalidation'])) {
                    $webapplicationreferringdepartmentvalidation = $appliance->fields['webapplicationreferringdepartmentvalidation'];
                }

                $webapplicationciovalidation = 0;
                if (isset($item->input['webapplicationciovalidation'])) {
                    $webapplicationciovalidation = $item->input['webapplicationciovalidation'];
                } elseif (isset($appliance->fields['webapplicationciovalidation'])) {
                    $webapplicationciovalidation = $appliance->fields['webapplicationciovalidation'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                } elseif (isset($appliance->fields['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $appliance->fields['webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                } elseif (isset($appliance->fields['webapplicationintegrities'])) {
                    $webapplicationintegrities = $appliance->fields['webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                } elseif (isset($appliance->fields['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $appliance->fields['webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                } elseif (isset($appliance->fields['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $appliance->fields['webapplicationtraceabilities'];
                }

                $appliance->update([
                    'id' => $appliance->fields['id'],
                    'address' => $address,
                    'version' => $version,
                    'editor' => $editor,
                    'backoffice' => $backoffice,
                    'number_users' => $number_users,
                    'webapplicationservertypes_id' => $webapplicationservertypes_id,
                    'webapplicationtechnics_id' => $webapplicationtechnics_id,
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationreferringdepartmentvalidation' => $webapplicationreferringdepartmentvalidation,
                    'webapplicationciovalidation' => $webapplicationciovalidation,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                ]);
            } else {

                $address = "";
                if (isset($item->input['address'])) {
                    $address = $item->input['address'];
                }

                $backoffice = "";
                if (isset($item->input['backoffice'])) {
                    $backoffice = $item->input['backoffice'];
                }

                $number_users = 0;
                if (isset($item->input['number_users'])) {
                    $number_users = $item->input['number_users'];
                }

                $version = "";
                if (isset($item->input['version'])) {
                    $version = $item->input['version'];
                }

                $editor = 0;
                if (isset($item->input['editor'])) {
                    $editor = $item->input['editor'];
                }

                $webapplicationservertypes_id = 0;
                if (isset($item->input['webapplicationservertypes_id'])) {
                    $webapplicationservertypes_id = $item->input['webapplicationservertypes_id'];
                }

                $webapplicationtechnics_id = 0;
                if (isset($item->input['webapplicationtechnics_id'])) {
                    $webapplicationtechnics_id = $item->input['webapplicationtechnics_id'];
                }

                $webapplicationexternalexpositions_id = 0;
                if (isset($item->input['webapplicationexternalexpositions_id'])) {
                    $webapplicationexternalexpositions_id = $item->input['webapplicationexternalexpositions_id'];
                }

                $webapplicationreferringdepartmentvalidation = 0;
                if (isset($item->input['webapplicationreferringdepartmentvalidation'])) {
                    $webapplicationreferringdepartmentvalidation = $item->input['webapplicationreferringdepartmentvalidation'];
                }

                $webapplicationciovalidation = 0;
                if (isset($item->input['webapplicationciovalidation'])) {
                    $webapplicationciovalidation = $item->input['webapplicationciovalidation'];
                }

                $webapplicationavailabilities = 0;
                if (isset($item->input['webapplicationavailabilities'])) {
                    $webapplicationavailabilities = $item->input['webapplicationavailabilities'];
                }

                $webapplicationintegrities = 0;
                if (isset($item->input['webapplicationintegrities'])) {
                    $webapplicationintegrities = $item->input['webapplicationintegrities'];
                }

                $webapplicationconfidentialities = 0;
                if (isset($item->input['webapplicationconfidentialities'])) {
                    $webapplicationconfidentialities = $item->input['webapplicationconfidentialities'];
                }

                $webapplicationtraceabilities = 0;
                if (isset($item->input['webapplicationtraceabilities'])) {
                    $webapplicationtraceabilities = $item->input['webapplicationtraceabilities'];
                }

                $appliance->add([
                    'webapplicationservertypes_id' => $webapplicationservertypes_id,
                    'webapplicationtechnics_id' => $webapplicationtechnics_id,
                    'webapplicationexternalexpositions_id' => $webapplicationexternalexpositions_id,
                    'webapplicationreferringdepartmentvalidation' => $webapplicationreferringdepartmentvalidation,
                    'webapplicationciovalidation' => $webapplicationciovalidation,
                    'webapplicationavailabilities' => $webapplicationavailabilities,
                    'webapplicationintegrities' => $webapplicationintegrities,
                    'webapplicationconfidentialities' => $webapplicationconfidentialities,
                    'webapplicationtraceabilities' => $webapplicationtraceabilities,
                    'address' => $address,
                    'version' => $version,
                    'editor' => $editor,
                    'appliances_id' => $item->getID(),
                    'backoffice' => $backoffice,
                    'number_users' => $number_users,
                ]);
            }
        }
    }

    public function post_getEmpty()
    {
        $this->fields["webapplicationconfidentialities"] = 0;
    }

    /**
     * @param $item
     */
    public static function cleanRelationToAppliance($item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['appliances_id' => $item->getID()]);
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return __('Appliance');
    }

    public static function showSupportPartFromDashboard($appliance)
    {
        $ApplianceId = $appliance->getField('id');

        $supplier = new Supplier();

        $applianceplugin = new Appliance();
        $is_known = $applianceplugin->getFromDBByCrit(['appliances_id' => $ApplianceId]);

        $supplier_id = $applianceplugin->fields['editor'] ?? 0;
        $title = __('Support', 'webapplications');

        $refEditId = 0;
        $editorName = null;
        $editoremail = null;
        $editorephonenumber = null;

        if ($is_known) {
            $refEditId = $applianceplugin->fields['editor'];

            $editor = new Supplier();
            $editor->getFromDB($refEditId);
            $editorName = $editor->getName();
            $editoremail = $editor->getField('email');
            $editorephonenumber = $editor->getField('phonenumber');
        }

        $options['itemtype'] = 'Supplier';
        $options['items_id'] = $refEditId;
        $options['editorName'] = $editorName ?? NOT_AVAILABLE;
        $options['editoremail'] = $editoremail ?? NOT_AVAILABLE;
        $options['editorephonenumber'] = $editorephonenumber ?? NOT_AVAILABLE;

        ob_start();
        Dashboard::showTitleforDashboard($title, $supplier_id, $supplier, 'edit', 'editAppSupport');
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_support.html.twig', [
            'item' => $appliance,
            'params' => $options,
        ]);
        $content = ob_get_clean();

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_child33.html.twig', [
            'content' => $content,
        ]);
    }

    public static function showDocumentsAndContractsFromDashboard($appliance)
    {
        global $CFG_GLPI;

        $ApplianceId = $appliance->getField('id');

        $documentItemDBTM = new Document_Item();
        $docuItems = $documentItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);
        $docuDBTM = new Document();
        $doc_entries = [];
        foreach ($docuItems as $docuItem) {
            $docuDBTM->getFromDB($docuItem['documents_id']);
            $open = $CFG_GLPI["root_doc"] . "/front/document.send.php";
            $open .= (strpos($open, '?') ? '&' : '?') . 'docid=' . $docuItem['documents_id'];
            $doc_entries[] = ['url' => $open, 'label' => $docuDBTM->getName()];
        }

        $contractItemDBTM = new Contract_Item();
        $contractItems = $contractItemDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);
        $contractDBTM = new Contract();
        $contract_entries = [];
        foreach ($contractItems as $contractItem) {
            $contractDBTM->getFromDB($contractItem['contracts_id']);
            $open = $CFG_GLPI["root_doc"] . "/front/contract.form.php";
            $open .= (strpos($open, '?') ? '&' : '?') . 'id=' . $contractItem['contracts_id'];
            $contract_entries[] = ['url' => $open, 'label' => $contractDBTM->getName()];
        }

        $ManualLinkDBTM = new ManualLink();
        $ManualLinkItems = $ManualLinkDBTM->find(['items_id' => $ApplianceId, 'itemtype' => 'Appliance']);
        $link_entries = [];
        foreach ($ManualLinkItems as $ManualLinkItem) {
            $url = (string) $ManualLinkItem['url'];
            // Only allow http(s) or relative URLs; reject javascript:/data:/vbscript: schemes.
            if (preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
                $url = '';
            }
            $target = ($ManualLinkItem['open_window'] == 1) ? "_blank" : "_self";
            $icon_html = "";
            if (!empty($ManualLinkItem['icon'])) {
                $icon_html = "<i class='ti " . htmlescape($ManualLinkItem['icon'])
                    . "' aria-hidden='true' style='margin-right: 5px;'></i>";
            }
            $link_entries[] = [
                'url'       => $url,
                'target'    => $target,
                'icon_html' => $icon_html,
                'label'     => $ManualLinkItem['name'],
            ];
        }

        ob_start();
        Dashboard::showTitleforDashboard(
            _n('Associated document', 'Associated documents', count($docuItems), 'webapplications'),
            $ApplianceId,
            $documentItemDBTM,
        );
        $doc_title = ob_get_clean();

        ob_start();
        Dashboard::showTitleforDashboard(
            _n('Associated contract', 'Associated contracts', count($contractItems), 'webapplications'),
            $ApplianceId,
            $contractItemDBTM,
        );
        $contract_title = ob_get_clean();

        ob_start();
        Dashboard::showTitleforDashboard(
            _n('Associated link', 'Associated links', count($ManualLinkItems), 'webapplications'),
            $ApplianceId,
            $ManualLinkDBTM,
        );
        $link_title = ob_get_clean();

        ob_start();
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_linklist.html.twig', [
            'title_html'        => $doc_title,
            'entries'           => $doc_entries,
            'empty_message'     => __("No associated documents", 'webapplications'),
            'break_after_list'  => '<br>',
            'break_after_empty' => '<br><br>',
        ]);
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_linklist.html.twig', [
            'title_html'        => $contract_title,
            'entries'           => $contract_entries,
            'empty_message'     => __("No associated contracts", 'webapplications'),
            'break_after_list'  => '<br>',
            'break_after_empty' => '<br><br>',
        ]);
        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_linklist.html.twig', [
            'title_html'    => $link_title,
            'entries'       => $link_entries,
            'empty_message' => __("No associated links", 'webapplications'),
        ]);
        $content = ob_get_clean();

        TemplateRenderer::getInstance()->display('@webapplications/webapplication_dashboard_child33.html.twig', [
            'content' => $content,
        ]);
    }

    public static function getColorForDICT($field)
    {
        switch ($field) {
            case '1':
                $background = "#00FF00";
                break;
            case '2':
                $background = "#FFFF00";
                break;
            case '3':
                $background = "#FF9900";
                break;
            case '4':
                $background = "#FF0000";
                break;
            default:
                $background = "#999999";
                break;
        }
        return $background;
    }
}
