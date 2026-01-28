<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z
 -------------------------------------------------------------------------
 Webapplications plugin for GLPI
 Copyright (C) 2009-2025 by the Webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Webapplications.

 Webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
namespace GlpiPlugin\Webapplications;

use AllowDynamicProperties;
use Appliance;
use Appliance_Item;
use ApplianceEnvironment;
use ApplianceType;
use Certificate_Item;
use Contract;
use Contract_Item;
use ContractCost;
use ContractType;
use DateTime;
use Document;
use Document_Item;
use Fpdf\Fpdf;
use Group;
use Group_Item;
use KnowbaseItem;
use KnowbaseItem_Item;
use Location;
use Manufacturer;
use Plugin;
use PluginFieldsContainer;
use PluginFieldsField;
use State;
use Supplier;
use Toolbox;
use User;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}
require '../vendor/autoload.php';

/**
 * Class Pdf
 */
#[AllowDynamicProperties]
class Pdf extends Fpdf
{

    /* Constantes pour paramétrer certaines données. */
    public $line_height = 6;     // Hauteur d'une ligne simple.
    public $multiline_height = 6;     // Hauteur d'un textarea
    public $linebreak_height = 6;     // Hauteur d'une break.
    public $bgcolor = 'grey';
    public $value_width = 45;
    public $pol_def = 'Helvetica'; // Police par défaut;
    public $title_size = 13;      // Taille du titre.
    public $subtitle_size = 12;      // Taille du titre de bloc.
    public $font_size = 10;      // Taille des champs.
    public $margin_top = 10;      // Marge du haut.
    public $margin_bottom = 10;      // Marge du bas.
    public $margin_left = 10;       // Marge de gauche et de droite accessoirement.
    public $big_width_cell = 210;     // Largeur d'une cellule qui prend toute la page.
    public $page_height = 297;
    public $header_height = 30;
    public $footer_height = 10;
    public $page_width;
    public $fields;
    public $title;
    public $subtitle;
    public $headernumber = 1;
    public $title_height;
    public $appliance;
    public $config;
    public $webappAppliance;

    /**
     * PluginMetaDemandsMetaDemandPdf constructor.
     *
     * @param $title
     * @param $subtitle
     */
    public function __construct($title, $subtitle, $id)
    {
        parent::__construct('P', 'mm', 'A4');

        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->id = $id;
        $this->page_width = $this->big_width_cell - ($this->margin_left * 2);
        $this->title_width = $this->page_width;
        $quarter = ($this->page_width / 4);
        $this->label_width = $quarter;
        $this->value_width = ($quarter * 3);
        $this->appliance = new Appliance();
        $this->appliance->getFromDB($id);
        $this->config = new Config();
        $this->config->getFromDB(1);
        $this->webappAppliance = new \GlpiPlugin\Webapplications\Appliance();

        //      $this->label_width = $quarter * 2;
        //      $this->value_width = $quarter * 2;
        // Set font size
        $this->SetFontSize($this->font_size);
        // Select our font family
        $this->SetFont('Helvetica', '');
    }

    /**
     * Fonctions permettant définir la couleur du texte
     */
    public function SetFontGrey()
    {
        $this->SetTextColor(205, 205, 205);
    }

    public function SetFontRed()
    {
        $this->SetTextColor(255, 0, 0);
    }

    public function SetFontBlue()
    {
        $this->SetTextColor(153, 204, 255);
    }

    public function SetFontDarkBlue()
    {
        $this->SetTextColor(0, 0, 255);
    }

    public function SetFontBlack()
    {
        $this->SetTextColor(0, 0, 0);
    }

    /**
     * @param $color
     */
    public function SetFontColor($color)
    {
        switch ($color) {
            case 'grey':
                $this->SetFontGrey();
                break;
            case 'red':
                $this->SetFontRed();
                break;
            case 'blue':
                $this->SetFontBlue();
                break;
            case 'darkblue':
                $this->SetFontDarkBlue();
                break;
            default:
                $this->SetFontBlack();
                break;
        }
    }

    /**
     * Fonctions permettant remplir la couleur d'une cellule
     */
    public function SetBackgroundGrey()
    {
        $this->SetFillColor(225, 225, 215);
    }

    public function SetBackgroundHardGrey()
    {
        $this->SetFillColor(192, 192, 192);
    }

    public function SetBackgroundBlue()
    {
        $this->SetFillColor(185, 218, 255);
    }

    public function SetBackgroundRed()
    {
        $this->SetFillColor(255, 0, 0);
    }

    public function SetBackgroundYellow()
    {
        $this->SetFillColor(255, 255, 204);
    }

    public function SetBackgroundWhite()
    {
        $this->SetFillColor(255, 255, 255);
    }

    /**
     * @param $color
     */
    public function SetBackgroundColor($color)
    {
        switch ($color) {
            case 'grey':
                $this->SetBackgroundGrey();
                break;
            case 'hardgrey':
                $this->SetBackgroundHardGrey();
                break;
            case 'red':
                $this->SetBackgroundRed();
                break;
            case 'blue':
                $this->SetBackgroundBlue();
                break;
            case 'yellow':
                $this->SetBackgroundYellow();
                break;
            default:
                $this->SetBackgroundWhite();
                break;
        }
    }

    public function Header()
    {

        $this->SetXY($this->margin_left, $this->margin_top-5);

        $largeurCoteTitre = round($this->page_width / 3, 1);
        $largeurCaseTitre = $this->page_width - $largeurCoteTitre;

        //Cellule contenant le titre
        $title = str_replace("’", "'", $this->title);
        $subtitle = Toolbox::stripTags($this->subtitle);
        $this->SetY($this->GetY());


        $this->SetX($this->margin_left);

        $this->CellTitleValue($this->page_width, 5, Toolbox::decodeFromUtf8(htmlspecialchars_decode($title)), '', 'R', '', 0, $this->title_size - 6, 'black');
        $this->SetY($this->GetY() + 5);
        $this->SetX($this->margin_left);

        if ($this->headernumber > 1) {
            $this->SetY($this->GetY() + 1);
            $this->CellTitleValue($this->page_width, 9, Toolbox::decodeFromUtf8(htmlspecialchars_decode($subtitle)) . ' (' . __('page', 'webapplications') . $this->headernumber . ')', 'TLRB', 'C', 'hardgrey', 0, $this->title_size - 2, 'black');
            $this->SetY($this->GetY() + 9);
            $this->SetX($this->margin_left);
        }

        $this->headernumber = $this->headernumber + 1;
    }

    /**
     * Permet de dessiner une cellule.
     *
     * @param type $w
     * @param type $h
     * @param type $value
     * @param string $border
     * @param string $align
     * @param string $color
     * @param bool $bold
     * @param int $size
     * @param string $fontColor
     */
    public function CellTitleValue($w, $h, $value, $border = 'LRB', $align = 'L', $color = '', $bold = false, $size = 12, $fontColor = '')
    {
        if (empty($size)) {
            $size = $this->font_size;
        }
        $this->SetBackgroundColor($color);
        $this->SetFontNormal($fontColor, $bold, $size);
        $this->Cell($w, $h, $value, $border, 0, $align, 1);
    }


    /**
     * Redéfinit une fonte
     *
     * @param type $color
     * @param type $bold
     * @param type $size
     */
    public function SetFontNormal($color, $bold, $size)
    {
        $this->SetFontColor($color);
        if ($bold) {
            $this->SetFont($this->pol_def, 'B', $size);
        } else {
            $this->SetFont($this->pol_def, '', $size);
        }
    }

    /**
     *
     */
    public function Footer()
    {
        $this->SetY($this->page_height - $this->margin_top - $this->header_height);
    }

    /**
     * @param $form
     * @param $fields
     */
    public function drawPdf()
    {
        $this->AliasNbPages();
        $this->AddPage("P");

        $this->SetAutoPageBreak(true, $this->margin_bottom);

        $this->firstpage();

        $this->AddPage("P");

        $this->secondpage();

        $this->AddPage("P");

        $this->thirdpage();

        $this->Output('D', $this->cleanTitle($this->appliance->fields['name']) . '.pdf');
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function cleanTitle($string)
    {
        $string = str_replace(['[\', \']'], '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string); // pour les ligatures e.g. '&oelig;'
        $string = preg_replace('#&[^;]+;#', '', $string); // supprime les autres caractères
        $string = preg_replace(['/[^a-z0-9]/i', '/[-]+/'], '-', $string);
        return strtolower(trim($string, '-'));
    }

    private function firstpage()
    {
        global $CFG_GLPI, $DB;
        foreach ($this->webappAppliance->find(['appliances_id' => $this->id]) as $row) {
            $this->webappAppliance->getFromDB($row['id']);
        }

        $supplier = new Supplier();
        if (!empty($this->webappAppliance->fields['editor']) && $this->webappAppliance->fields['editor'] > 0) {
            $supplier->getFromDB($this->webappAppliance->fields['editor']);
        }

        $documentItem = new Document_Item();
        $documentItemDatas = $documentItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $knowbaseItem = new KnowbaseItem_Item();
        $knowbaseItemDatas = $knowbaseItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $contractItem = new Contract_Item();
        $contractItemDatas = $contractItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $this->SetX($this->margin_left);
//        $this->SetFontNormal('black', 1, 10);
        $this->CellTitleValue($this->page_width, '15', Toolbox::decodeFromUtf8($this->appliance->fields['name']), 'TLR', 'C', 'hardgrey', 1, '20', 'black');
        $this->SetXY($this->margin_left, $this->GetY()+15);
        $this->SetFontNormal('black', false, 10);

        if ($this->config->fields['use_fields_description']) {
            $description = '';
            if ($this->config->fields['fields_description_table'] == 'Appliance') {
                if ($this->config->fields['fields_description_name'] == 'name') {
                    $description = $this->appliance->fields['name'];
                } else {
                    $description = $this->appliance->fields['comment'];
                }
            } elseif ($this->config->fields['fields_description_table'] == 'Fields') {
                $plugin = new Plugin();
                if ($plugin->isActivated('fields')) {
                    $fieldsfield = new PluginFieldsField();
                    $fieldsfield->getFromDB($this->config->fields['fields_description_name']);
                    $fieldsContainer = new PluginFieldsContainer();
                    $fieldsContainer->getFromDB($fieldsfield->fields['plugin_fields_containers_id']);
                    if (strpos($fieldsContainer->fields['itemtypes'], 'Appliance') !== false) {
                        $itemtype = json_decode($fieldsContainer->fields['itemtypes']);
                        $firsttable = 'glpi_plugin_fields_' . strtolower($itemtype[0]) . strtolower($fieldsContainer->fields['name']) . 's';

                        $fieldsDatas = $DB->request(['FROM' => $firsttable, 'WHERE' => ['itemtype' => 'Appliance', 'items_id' => $this->id]]);

                        foreach ($fieldsDatas as $fieldDatum) {
                            $description = $fieldDatum[$fieldsfield->fields['name']];
                        }
                    }
                }
            }
            $this->MultiCell($this->page_width, '3', '', 'TLR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 12);
            $this->MultiCell($this->page_width, '5', __('Appliance description', 'webapplications'), 'LR', 'C', '', 0, '', 'black');
            $this->MultiCell($this->page_width, '3', '', 'LR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 10);
            $this->MultiCell($this->page_width, '5', Toolbox::decodeFromUtf8($description), 'LR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 10);
            $this->MultiCell($this->page_width, '3', '', 'LR', 'C', '', 0, '', 'black');
        }
        $yligne = $this->GetY();
        $number_users = $this->webappAppliance->fields['number_users'] ?? 0;
        $this->MultiCell($this->page_width /4, 10, User::getTypeName(2) . PHP_EOL . Toolbox::decodeFromUtf8(htmlspecialchars_decode($this->webappAppliance::getNbUsersValue($number_users))), 'LRBT', 'C', '', 0, '', 'black');
        $yligne2 = $this->GetY();
        $this->title_height = ($yligne2 - $yligne) /2;
        $this->setXY($this->margin_left + ($this->page_width /4), $yligne);
        $this->MultiCell($this->page_width - ($this->page_width/4), $this->title_height, __('Project leader', 'webapplications') . ' : ' . User::getFriendlyNameById($this->appliance->fields['users_id_tech']), 'BRT', 'C', '', 0, '', 'black');
        $this->setX($this->margin_left + ($this->page_width /4));
        $groupsitem = new Group_Item();
        $groups = '';
        foreach ($groupsitem->find(['itemtype' => 'Appliance', 'items_id' => $this->id, 'type' => 2]) as $group) {
            $groups .= Group::getFriendlyNameById($group['groups_id']) . ' ';
        }
        $this->MultiCell($this->page_width - ($this->page_width/4), $this->title_height, __('Project team', 'webapplications') . ' : ' . $groups, 'BR', 'C', '', 0, '', 'black');

        if (!empty($this->webappAppliance->fields['editor']) && $this->webappAppliance->fields['editor'] > 0) {
            $this->setY($this->GetY() + 2);
            $this->SetFillColor(230);
            $this->MultiCell($this->page_width, $this->title_height, __('Support', 'webapplications'), 'TLRB', 'C', true, 0, '', 'black');

            $yligne3 = $this->GetY();
            $this->MultiCell($this->page_width/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Referent editor', 'webapplications'))), 'LRB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($this->page_width/3), $yligne3);
            $this->MultiCell($this->page_width/3, 7, __('Mail support', 'webapplications'), 'RB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($this->page_width/3)*2, $yligne3);
            $this->MultiCell($this->page_width/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Phone support', 'webapplications'))), 'RB', 'C', '', 0, '', 'black');

            $yligne3 = $this->GetY();
            $this->MultiCell($this->page_width/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['name'] ?? '')), 'LRB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($this->page_width/3), $yligne3);
            $this->MultiCell($this->page_width/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['email'] ?? '')), 'RB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($this->page_width/3)*2, $yligne3);
            $this->MultiCell($this->page_width/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['phonenumber'] ?? '')), 'RB', 'C', '', 0, '', 'black');
        }

        $this->setY($this->GetY() + 2);
        $this->SetFillColor(230);
        $yligne3 = $this->GetY();
        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Associated document', 'Associated documents', 2, 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $yligne3 = $this->GetY();

        $document = new Document();
        foreach ($documentItemDatas as $documentItemData) {
            $document->getFromDB($documentItemData['documents_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/document.send.php?docid=" . $documentItemData['documents_id'];
            $this->Cell($this->page_width, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($document->fields['name'])), 'LR', 1, 'C', false, $docurl, 'black');
            $this->setXY($this->margin_left, $this->GetY());
        }
        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($this->page_width, 1, '', 'LRB', 'C', false, 0, '', 'black');

        $yligne4 = $this->GetY();
        $this->setXY($this->margin_left, $yligne4);
        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Knowledge base'))), 'TLRB', 'C', true, 0, '', 'black');

        $knowbase = new KnowbaseItem();
        $this->setXY($this->margin_left, $this->GetY());
        foreach ($knowbaseItemDatas as $knowbaseItemData) {
            $knowbase->getFromDB($knowbaseItemData['knowbaseitems_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/knowbaseitem.form.php?id=" . $knowbaseItemData['knowbaseitems_id'];
            $this->Cell($this->page_width, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($knowbase->fields['name'])), 'LR', 1, 'C', false, $docurl, 'black');
            $this->setXY($this->margin_left, $this->GetY());
        }

        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($this->page_width, 1, '', 'LRB', 'C', false, 0, '', 'black');

        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Associated contract', 'Associated contracts', 2, 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $yligne3 = $this->GetY();

        $contract = new Contract();
        foreach ($contractItemDatas as $contractItemData) {
            $contract->getFromDB($contractItemData['contracts_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/contract.form.php?id=" . $contractItemData['contracts_id'];
            $this->Cell(($this->page_width/9)*2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['name'])), 'L', 1, 'L', false, $docurl, 'black');
            $yligne4 = $this->GetY();
            $this->setXY($this->margin_left + (($this->page_width/9)*2), $yligne3);
            $contractType = new ContractType();
            $contractType->getFromDB($contract->fields['contracttypes_id']);
            $typename = '';
            if (isset($contractType->fields['name'])) {
                $typename = $contractType->fields['name'];
            }

            $contratCost = new ContractCost();
            $costcontract = 0;
            $datenow = new DateTime();
            foreach ($contratCost->find(['contracts_id' => $contractItemData['contracts_id']]) as $cost) {
                $begindate = new DateTime($cost['begin_date']);
                $enddate = new DateTime($cost['end_date']);
                if ($begindate < $datenow && $enddate >= $datenow) {
                    $costcontract += $cost['cost'];
                }
            }

            $this->Cell(($this->page_width/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($typename)), '', 1, 'C', false);
            $this->setXY($this->margin_left + (($this->page_width/9)*4), $yligne3);
            $this->Cell(($this->page_width/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['num'])), '', 1, 'C', false);
            $this->setXY($this->margin_left + (($this->page_width/9)*6), $yligne3);

            $datebegin = isset($contract->fields['begin_date']) ? new DateTime($contract->fields['begin_date']) : '';
            $this->Cell(($this->page_width/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode(isset($contract->fields['begin_date']) ? $datebegin->format('Y-m-d') : '')), '', 1, 'C', false);
            $this->setXY($this->margin_left + (($this->page_width/9)*7), $yligne3);
            $this->Cell(($this->page_width/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['duration'] . ' months')), '', 1, 'C', false);
            $this->setXY($this->margin_left + (($this->page_width/9)*8), $yligne3);
            $this->Cell(($this->page_width/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($costcontract) . ' EUR'), 'R', 1, 'C', false);
            $yligne3 = $this->GetY();
        }
        $this->MultiCell($this->page_width, 1, '', 'RBL', 'C', false, 0, '', 'black');
    }

    private function secondpage()
    {
        global $DB;
        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Summary', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');


        //inclure ici le résumé des champs
        $statut = new State();
        $statut->getFromDB($this->appliance->fields['states_id']);
        $this->adddataonfourcolomn(__('Name'), $this->appliance->fields['name'] ?? '', __('Status'), $statut->fields['name'] ?? '');

        $location = new Location();
        $location->getFromDB($this->appliance->fields['locations_id']);
        $answer = $this->appliance->fields['is_helpdesk_visible'] ? __('Yes') : __('No');
        $this->adddataonfourcolomn(__('Associable to a ticket'), $answer, __('Location'), $location->fields['name'] ?? '');

        $applianceType = new ApplianceType();
        $applianceType->getFromDB($this->appliance->fields['appliancetypes_id']);
        $user = new User();
        $user->getFromDB($this->appliance->fields['users_id_tech']);
        $this->adddataonfourcolomn(_n('Type', 'Types', 1), $applianceType->fields['name'] ?? '', __('Technician in charge'), $user->fields['name'] ?? '');

        $manufacturer = new Manufacturer();
        $manufacturer->getFromDB($this->appliance->fields['manufacturers_id']);
        $groupsitem = new Group_Item();
        $groups = '';
        foreach ($groupsitem->find(['itemtype' => 'Appliance', 'items_id' => $this->id, 'type' => 2]) as $group) {
            $groups .= Group::getFriendlyNameById($group['groups_id']) . ' ';
        }
        $this->adddataonfourcolomn(Manufacturer::getTypeName(1), $manufacturer->fields['name'] ?? '', __('Group in charge'), $groups ?? '');

        $this->adddataonfourcolomn(__('Alternate username number'), $this->appliance->fields['contact_num'] ?? '', __('Serial number'), $this->appliance->fields['serial'] ?? '');

        $this->adddataonfourcolomn(__('Alternate username'), $this->appliance->fields['contact'] ?? '', __('Inventory number'), $this->appliance->fields['otherserial'] ?? '');

        $this->setXY($this->margin_left + ($largeurdispo/2), $this->GetY());
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2), $yligne4 - $this->GetY(), '', 'R', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($largeurdispo/2), $yligne5 - $yligne4, '', 'L', 'C', false, 0, '', 'black');
        }

        $user = new User();
        $user->getFromDB($this->appliance->fields['users_id']);
        $groupsitem = new Group_Item();
        $groups = '';
        foreach ($groupsitem->find(['itemtype' => 'Appliance', 'items_id' => $this->id, 'type' => 1]) as $group) {
            $groups .= Group::getFriendlyNameById($group['groups_id']) . ' ';
        }
        $this->adddataonfourcolomn(User::getTypeName(1), $user->fields['name'] ?? '', Group::getTypeName(1), $groups ?? '');




        //commentaire
        if (!$this->config->fields['use_fields_description'] ||
            $this->config->fields['fields_description_table'] != 'Appliance' ||
            $this->config->fields['fields_description_name'] != 'comment') {
            $yligne3 = $this->GetY();
            $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Comment', 'Comments', 2) . ' : ')), 'L', 'L', false, 0, '', 'black');
            $yligne35 = $this->GetY();
            $this->setXY($this->margin_left + ($this->page_width/4), $yligne3);
            $this->MultiCell(($this->page_width/4)*3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($this->appliance->fields['comment'] ?? '')), 'R', 'L', false, 0, '', 'black');
            $this->setXY($this->margin_left, $this->GetY());

            $yligne4 = $this->GetY();
            if ($yligne35 != $yligne4) {
                $this->setXY($this->margin_left, $yligne35);
                $this->MultiCell(($this->page_width/4), $yligne4 - $yligne35, '', 'L', 'C', false, 0, '', 'black');
                $this->SetXY($this->margin_left, $yligne4);
            }
        }


        $applianceenvironnement = new ApplianceEnvironment();
        $applianceenvironnement->getFromDB($this->appliance->fields['applianceenvironments_id']);
        $yligne3 = $this->GetY();
        $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Environment', 'Environments', 1) . ' : ')), 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/4), $yligne3);
        $this->MultiCell(($this->page_width/4)*3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($applianceenvironnement->fields['name'] ?? '')), 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $this->adddataonfourcolomn(__('URL', 'webapplications'), $this->webappAppliance->fields['address'] ?? '', __('Backoffice URL', 'webapplications'), $this->webappAppliance->fields['backoffice'] ?? '');


        $webapplicationServertype = new Webapplicationservertype();
        if (isset($this->webappAppliance->fields['webapplicationservertypes_id'])) {
            $webapplicationServertype->getFromDB($this->webappAppliance->fields['webapplicationservertypes_id']);
        }
        $this->adddataonfourcolomn(__('Installed version', 'webapplications'), $this->webappAppliance->fields['version'] ?? '', _n('Type of treatment server', 'Types of treatment server', 1, 'webapplications'), $webapplicationServertype->fields['name'] ?? '');


        $webapplicationtechnics = new Webapplicationtechnic();
        if (isset($this->webappAppliance->fields['webapplicationtechnics_id'])) {
            $webapplicationtechnics->getFromDB($this->webappAppliance->fields['webapplicationtechnics_id']);
        }
        $webapplicationexposition = new Webapplicationexternalexposition();
        if (isset($this->webappAppliance->fields['webapplicationexternalexpositions_id'])) {
            $webapplicationexposition->getFromDB($this->webappAppliance->fields['webapplicationexternalexpositions_id']);
        }
        $this->adddataonfourcolomn(_n('Language of treatment', 'Languages of treatment', 1, 'webapplications'), $webapplicationtechnics->fields['name'] ?? '', _n('External exposition', 'External exposition', 1, 'webapplications'), $webapplicationexposition->fields['name'] ?? '');

        //mettre les champs sup

        $plugin = new Plugin();
        if ($plugin->isActivated('fields')) {
            $fieldsContainer = new PluginFieldsContainer();
            foreach ($fieldsContainer->find(['type' => 'dom']) as $row) {
                if (strpos($row['itemtypes'], 'Appliance') !== false) {
                    $row['itemtype'] = json_decode($row['itemtypes']);
                    $firsttable = 'glpi_plugin_fields_' . strtolower($row['itemtype'][0]) . strtolower($row['name']) . 's';

                    $fieldsDatas = $DB->request(['FROM' => $firsttable, 'WHERE' => ['itemtype' => 'Appliance', 'items_id' => $this->id]]);

                    $fieldsfields = new PluginFieldsField();
                    $compteurfields = 0;
                    $yligne3 = $this->GetY();
                    $yligne4 = $this->GetY();
                    foreach ($fieldsDatas as $fieldsData) {
                        $rowfields = $fieldsfields->find(['plugin_fields_containers_id' => $row['id']], ['ranking ASC']);
                        foreach ($rowfields as $rowfield) {
                            switch ($rowfield['type']) {
                                case 'yesno':
                                    $fieldsData[$rowfield['name']] = $fieldsData[$rowfield['name']] == 1 ? __('Yes') : __('No');
                                    break;
                                case 'dropdown':
                                    $fieldsdropdown = $DB->request(['SELECT' => 'name', 'FROM' => 'glpi_plugin_fields_' . strtolower($rowfield['name']) . 'dropdowns', 'WHERE' => ['id' => $fieldsData['plugin_fields_' . $rowfield['name'] . 'dropdowns_id']]]);
                                    foreach ($fieldsdropdown as $dropdown) {
                                        $fieldsData[$rowfield['name']] = $dropdown['name'];
                                    }
                                    break;
                                case 'glpi_item':
                                    if (isset($fieldsData['itemtype_' . $rowfield['name']])) {
                                        $item = new $fieldsData['itemtype_' . $rowfield['name']]();
                                        $item->getFromDB($fieldsData['items_id_' . $rowfield['name']]);
                                        $fieldsData[$rowfield['name']] = $item::getTypeName() . ' - ' . $item->fields['name'];
                                    } else {
                                        $fieldsData[$rowfield['name']] = '';
                                    }
                                default:
                                    break;
                            }
                            if (!str_starts_with($rowfield['type'], 'dropdown-') && (!$this->config->fields['use_fields_description'] || $this->config->fields['fields_description_table'] != 'Fields' || $this->config->fields['fields_description_name'] != $rowfield['id'] )) {
                                if ($compteurfields == 0 || $compteurfields%2 == 0) {
                                    //impair (a gauche)
                                    $this->SetX($this->margin_left);
                                    $yligne3 = $this->GetY();
                                    $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($rowfield['label'] . ' : ')), 'L', 'L', false, 0, '', 'black');
                                    $yligne35 = $this->GetY();
                                    $this->setXY($this->margin_left + ($this->page_width/4), $yligne3);
                                    $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($fieldsData[$rowfield['name']])), '', 'L', false, 0, '', 'black');

                                    $compteurfields ++;
                                    $yligne4 = $this->GetY();
                                    if ($yligne35 != $yligne4) {
                                        $this->setXY($this->margin_left, $yligne35);
                                        $this->MultiCell(($this->page_width/4), $yligne4 - $yligne35, '', 'L', 'C', false, 0, '', 'black');
                                        $this->SetXY($this->margin_left + ($this->page_width/4)*2, $yligne4);
                                    }
                                } else {
                                    //pair à droite
                                    $this->setXY($this->margin_left + ($this->page_width/4)*2, $yligne3);
                                    $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($rowfield['label'] . ' : ')), '', 'L', false, 0, '', 'black');
                                    $this->setXY($this->margin_left + ($this->page_width/4)*3, $yligne3);
                                    $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($fieldsData[$rowfield['name']])), 'R', 'L', false, 0, '', 'black');
                                    $compteurfields ++;

                                    // Ajuster le tableau

                                    $this->setXY($this->margin_left + ($this->page_width/2), $this->GetY());
                                    if ($this->GetY() < $yligne4) {
                                        $this->MultiCell(($this->page_width/2), $yligne4 - $this->GetY(), '', 'R', 'C', false, 0, '', 'black');
                                    } elseif ($this->GetY() > $yligne4) {
                                        $yligne5 = $this->GetY();
                                        $this->setXY($this->margin_left, $yligne4);
                                        $this->MultiCell(($this->page_width/2), $yligne5 - $yligne4, '', 'L', 'C', false, 0, '', 'black');
                                    }
                                }
                            }
                        }
                    }
                    if ($compteurfields %2 == 1) {
                        $this->setXY($this->margin_left + ($this->page_width/2), $yligne3);
                        $this->MultiCell(($this->page_width/2), $yligne4 - $yligne3, '', 'R', 'C', false, 0, '', 'black');
                    }
                }
            }
        }





        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($this->page_width, 1, '', 'LRB', 'L', false, 0, '', 'black');




        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Security Needs', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');

        $yligne3 = $this->GetY();
        $value = $this->webappAppliance->fields['webapplicationavailabilities'] ?? '';
        $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Availability', 'webapplications') . ' : ' . $value)), 'LRB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/4), $yligne3);
        $value = $this->webappAppliance->fields['webapplicationintegrities'] ?? '';
        $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Integrity', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/4)*2, $yligne3);
        $value = $this->webappAppliance->fields['webapplicationconfidentialities'] ?? '';
        $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Confidentiality', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/4)*3, $yligne3);
        $value = $this->webappAppliance->fields['webapplicationtraceabilities'] ?? '';
        $this->MultiCell($this->page_width/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Traceability', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');

        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($this->page_width, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');

        $yligne3 = $this->GetY();
        $answer = isset($this->webappAppliance->fields['webapplicationreferringdepartmentvalidation']) && $this->webappAppliance->fields['webapplicationreferringdepartmentvalidation'] == 0 ? __('No') : __('Yes');
        $this->MultiCell($this->page_width/2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation of the request by the referring Department', 'webapplications') . ' : ')) . $answer, 'LRB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/2), $yligne3);
        $answer = isset($this->webappAppliance->fields['webapplicationciovalidation']) && $this->webappAppliance->fields['webapplicationciovalidation'] == 0 ? __('No') : __('Yes');
        $this->MultiCell($this->page_width/2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation by CISO', 'webapplications') . ' : ')) . $answer, 'RB', 'C', false, 0, '', 'black');

    }

    private function adddataonfourcolomn($titleleft, $dataleft, $titleright, $dataright){
        $yligne3 = $this->GetY();
        $this->MultiCell($this->page_width / 4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($titleleft . ' : ')), 'L', 'L');
        $yligne35 = $this->GetY();
        $this->setXY($this->margin_left + ($this->page_width / 4), $yligne3);
        $this->MultiCell($this->page_width / 4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($dataleft ?? '')), '', 'L');
        $yligne4 = $this->GetY();
        if ($yligne35 != $yligne4) {
            $this->setXY($this->margin_left, $yligne35);
            $this->MultiCell(($this->page_width / 4), $yligne4 - $yligne35, '', 'L', 'C');
            $this->SetXY($this->margin_left + ($this->page_width / 4) * 2, $yligne4);
        }
        $this->setXY($this->margin_left + ($this->page_width / 4) * 2, $yligne3);
        $this->MultiCell($this->page_width / 4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($titleright . ' : ')), '', 'L');
        $yligne45 = $this->GetY();
        $this->setXY($this->margin_left + ($this->page_width / 4) * 3, $yligne3);
        $this->MultiCell($this->page_width / 4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($dataright ?? '')), 'R', 'L');

        $this->setXY($this->margin_left + ($this->page_width / 2), $this->GetY());
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($this->page_width / 2), $yligne4 - $this->GetY(), '', 'R', 'C');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width / 2), $yligne5 - $yligne4, '', 'L', 'C');
        } elseif ($yligne45 > $this->GetY()) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width / 2), $yligne45 - $yligne4, '', 'L', 'C');
            $this->setXY($this->margin_left + (($this->page_width / 4) * 3), $yligne5);
            $this->MultiCell(($this->page_width / 4), $yligne45 - $yligne5, '', 'R', 'C');
        }
        $this->setXY($this->margin_left, $this->GetY());
    }

    private function thirdpage()
    {
        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Ecosystem', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne3);
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Process', 'Processes', 1))), 'TLRB', 'C', true, 0, '', 'black');


        $webapplicationentities = new Entity();

        $webapplicationprocesses = new Process();

        $webapplicationentitiesDatas = $webapplicationentities->find();
        $webapplicationprocessesDatas = $webapplicationprocesses->find();

        $yligne3 = $this->GetY();

        foreach ($webapplicationentitiesDatas as $webapplicationentitiesData) {
            $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationentitiesData['name'])), 'LR', 'C');
            $this->setXY($this->margin_left, $this->GetY());
        }

        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne3);
        foreach ($webapplicationprocessesDatas as $webapplicationprocessesData) {
            $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationprocessesData['name'])), 'LR', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($this->page_width/2)-1, $yligne4 - $this->GetY() +1, '', 'LRB', 'C');
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, $yligne5 - $yligne4 + 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C',);
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $this->GetY());
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        }



        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Physical Infrastructure', 'webapplications'))), 'TLRB', 'C', true);
        $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne3);
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(\DatabaseInstance::getTypeName(2))), 'TLRB', 'C', true);


        $applicationItems = new Appliance_Item();



        $yligne3 = $this->GetY();

        foreach (["Computer", "Printer", "Phone", "NetworkEquipment"] as $itemtype) {
            $physicalinfraDatas = $applicationItems->find(['appliances_id' => $this->id, 'itemtype' => $itemtype]);
            foreach ($physicalinfraDatas as $physicalinfraData) {
                $item = new $physicalinfraData['itemtype']();
                $item->getFromDB($physicalinfraData['items_id']);
                $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($item->fields['name'])), 'LR', 'C');
                $this->setXY($this->margin_left, $this->GetY());
            }
        }


        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne3);

        $databasesInstanceDatas = $applicationItems->find(['appliances_id' => $this->id, 'itemtype' => 'DatabaseInstance']);
        foreach ($databasesInstanceDatas as $databasesInstanceData) {
            $databaseInstance = new DatabaseInstance();
            $databaseInstance->getFromDB($databasesInstanceData['items_id']);
            $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($databaseInstance->fields['name'])), 'LR', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($this->page_width/2)-1, $yligne4 - $this->GetY() +1, '', 'LRB', 'C');
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, $yligne5 - $yligne4 + 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $this->GetY());
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        }




        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n("Certificate", 'Certificates', 2))), 'TLRB', 'C', true);
        $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne3);
        $this->MultiCell(($this->page_width/2) -1, $this->title_height, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Stream', 'Streams', 2, 'webapplications'))), 'TLRB', 'C', true);

        $certificatItem = new Certificate_Item();
        $certificatItemDatas = $certificatItem->find(['items_id'=>$this->id, 'itemtype'=>'Appliance']);

        $webapplicationstream = new Stream();
        $webapplicationstreamDatas = $webapplicationstream->find();

        $yligne3 = $this->GetY();

        foreach ($certificatItemDatas as $certificatItemData) {
            $certificat = new \Certificate();
            $certificat->getFromDB($certificatItemData['certificates_id']);
            $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($certificat->fields['name'])), 'LR', 'C');
            $this->setXY($this->margin_left, $this->GetY());
        }

        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne3);
        foreach ($webapplicationstreamDatas as $webapplicationstreamData) {
            $this->MultiCell(($this->page_width/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationstreamData['name'])), 'LR', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($this->page_width/2)-1, $yligne4 - $this->GetY() +1, '', 'LRB', 'C');
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($this->page_width/2) -1, $yligne5 - $yligne4 + 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) + 1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $this->GetY());
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
            $this->setXY($this->margin_left + ($this->page_width/2) +1, $yligne5);
            $this->MultiCell(($this->page_width/2) -1, 1, '', 'LRB', 'C');
        }
    }
}
