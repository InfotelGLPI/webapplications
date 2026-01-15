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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginWebapplicationsPdf
 */
#[AllowDynamicProperties]
class PluginWebapplicationsPdf extends Fpdf\Fpdf
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

        $largeurdispo = $this->big_width_cell - ($this->margin_left * 2);
        $largeurCoteTitre = round($largeurdispo / 3, 1);
        $largeurCaseTitre = $largeurdispo - $largeurCoteTitre;



        //Cellule contenant le titre
        $title = str_replace("’", "'", $this->title);
        $subtitle = Toolbox::stripTags($this->subtitle);
        $this->SetY($this->GetY());


        $this->SetX($this->margin_left);

        $this->CellTitleValue($largeurdispo, 5, Toolbox::decodeFromUtf8(Toolbox::stripslashes_deep($title)), '', 'R', '', 0, $this->title_size - 6, 'black');
        $this->SetY($this->GetY() + 5);
        $this->SetX($this->margin_left);

        if ($this->headernumber > 1) {
            $this->SetY($this->GetY() + 1);
            $this->CellTitleValue($largeurdispo, 9, Toolbox::decodeFromUtf8(Toolbox::stripslashes_deep($subtitle)) . ' (' . __('page', 'webapplications') . $this->headernumber . ')', 'TLRB', 'C', 'hardgrey', 0, $this->title_size - 2, 'black');
            $this->SetY($this->GetY() + 9);
            $this->SetX($this->margin_left);
        }

        $this->headernumber = $this->headernumber + 1;
    }


    /**
     * ImageResize
     *
     * @param int $width
     * @param int $height
     * @param int $target
     *
     * @return array
     */
    public function imageResize($width, $height, $target)
    {
        if ($width > $height) {
            $percentage = ($target / $width);
        } else {
            $percentage = ($target / $height);
        }

        $width = round($width * $percentage);
        $height = round($height * $percentage);

        return [$width, $height];
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


        global $CFG_GLPI, $DB;
        $this->AliasNbPages();
        $this->AddPage("P");

        $this->SetAutoPageBreak(true, $this->margin_bottom);
        $largeurdispo = $this->big_width_cell - ($this->margin_left * 2);

//
//        $this->SetFont('Arial', 'B', 16);
//        $this->Cell(40, 10, 'Hello FPDF!');

        $appliance = new Appliance();
        $appliance->getFromDB($this->id);



        $webappAppliance = new PluginWebapplicationsAppliance();
        foreach ($webappAppliance->find(['appliances_id' => $this->id]) as $row) {
            $webappAppliance->getFromDB($row['id']);
        }

        $supplier = new Supplier();
        if (!empty($webappAppliance->fields['editor']) && $webappAppliance->fields['editor'] > 0) {
            $supplier->getFromDB($webappAppliance->fields['editor']);
        }

        $documentItem = new Document_Item();
        $documentItemDatas = $documentItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $knowbaseItem = new KnowbaseItem_Item();
        $knowbaseItemDatas = $knowbaseItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $contractItem = new Contract_Item();
        $contractItemDatas = $contractItem->find(['itemtype' => 'Appliance', 'items_id' => $this->id]);

        $this->SetX($this->margin_left);
//        $this->SetFontNormal('black', 1, 10);
        $this->CellTitleValue($largeurdispo, '15', Toolbox::decodeFromUtf8($appliance->fields['name']) , 'TLR', 'C', 'hardgrey', 1, '20', 'black');
        $this->SetXY($this->margin_left, $this->GetY()+15);
        $this->SetFontNormal('black', false, 10);

        $config = new PluginWebapplicationsConfig();
        $config->getFromDB(1);
        if ($config->fields['use_fields_description']) {
            $description = '';
            if ($config->fields['fields_description_table'] == 'Appliance') {
                if ($config->fields['fields_description_name'] == 'name') {
                    $description = $appliance->fields['name'];
                } else {
                    $description = $appliance->fields['comment'];
                }
            } elseif ($config->fields['fields_description_table'] == 'Fields') {
                $plugin = new Plugin();
                if ($plugin->isActivated('fields')) {
                    $fieldsfield = new PluginFieldsField();
                    $fieldsfield->getFromDB($config->fields['fields_description_name']);
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
            $this->MultiCell($largeurdispo, '3', '' , 'TLR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 12);
            $this->MultiCell($largeurdispo, '5', __('Appliance description', 'webapplications') , 'LR', 'C', '', 0, '', 'black');
            $this->MultiCell($largeurdispo, '3', '' , 'LR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 10);
            $this->MultiCell($largeurdispo, '5', Toolbox::decodeFromUtf8($description) , 'LR', 'C', '', 0, '', 'black');
            $this->SetFontNormal('black', false, 10);
            $this->MultiCell($largeurdispo, '3', '' , 'LR', 'C', '', 0, '', 'black');
        }
        $yligne = $this->GetY();
        $number_users = $webappAppliance->fields['number_users'] ?? 0;
        $this->MultiCell($largeurdispo /4, 10, User::getTypeName(2) . PHP_EOL . Toolbox::decodeFromUtf8(htmlspecialchars_decode($webappAppliance::getNbUsersValue($number_users))), 'LRBT', 'C', '', 0, '', 'black');
        $yligne2 = $this->GetY();
        $this->setXY($this->margin_left + ($largeurdispo /4), $yligne);
        $this->MultiCell($largeurdispo - ($largeurdispo/4), ($yligne2 - $yligne) /2, __('Project leader', 'webapplications') . ' : ' . User::getFriendlyNameById($appliance->fields['users_id_tech']) , 'BRT', 'C', '', 0, '', 'black');
        $this->setX($this->margin_left + ($largeurdispo /4));
        $this->MultiCell($largeurdispo - ($largeurdispo/4), ($yligne2 - $yligne) /2, __('Project team', 'webapplications') . ' : ' . Group::getFriendlyNameById($appliance->fields['groups_id_tech']) , 'BR', 'C', '', 0, '', 'black');

        if (!empty($webappAppliance->fields['editor']) && $webappAppliance->fields['editor'] > 0) {
            $this->setY($this->GetY() + 2);
            $this->SetFillColor(230);
            $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, __('Support', 'webapplications') , 'TLRB', 'C', true, 0, '', 'black');

            $yligne3 = $this->GetY();
            $this->MultiCell($largeurdispo/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Referent editor', 'webapplications'))) , 'LRB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/3),$yligne3);
            $this->MultiCell($largeurdispo/3, 7, __('Mail support', 'webapplications'), 'RB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/3)*2,$yligne3);
            $this->MultiCell($largeurdispo/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Phone support', 'webapplications'))) , 'RB', 'C', '', 0, '', 'black');

            $yligne3 = $this->GetY();
            $this->MultiCell($largeurdispo/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['name'])) , 'LRB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/3),$yligne3);
            $this->MultiCell($largeurdispo/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['email'])) , 'RB', 'C', '', 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/3)*2,$yligne3);
            $this->MultiCell($largeurdispo/3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($supplier->fields['phonenumber'])) , 'RB', 'C', '', 0, '', 'black');
        }

        $this->setY($this->GetY() + 2);
        $this->SetFillColor(230);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Associated document', 'Associated documents', 2, 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $yligne3 = $this->GetY();

        $document = new Document();
        foreach ($documentItemDatas as $documentItemData) {
            $document->getFromDB($documentItemData['documents_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/document.send.php?docid=" . $documentItemData['documents_id'];
            $this->Cell($largeurdispo, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($document->fields['name'])) , 'LR', 1, 'C', false, $docurl, 'black');
            $this->setXY($this->margin_left,$this->GetY());
        }
        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($largeurdispo, 1, '', 'LRB', 'C', false, 0, '', 'black');

        $yligne4 = $this->GetY();
        $this->setXY($this->margin_left, $yligne4);
        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Knowledge base'))), 'TLRB', 'C', true, 0, '', 'black');


        $knowbase = new KnowbaseItem();
        $this->setXY($this->margin_left, $this->GetY());
        foreach ($knowbaseItemDatas as $knowbaseItemData) {
            $knowbase->getFromDB($knowbaseItemData['knowbaseitems_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/knowbaseitem.form.php?id=" . $knowbaseItemData['knowbaseitems_id'];
            $this->Cell($largeurdispo, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($knowbase->fields['name'])) , 'LR', 1, 'C', false, $docurl, 'black');
            $this->setXY($this->margin_left,$this->GetY());
        }


        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($largeurdispo, 1, '', 'LRB', 'C', false, 0, '', 'black');

        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Associated contract', 'Associated contracts', 2, 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $yligne3 = $this->GetY();

        $contract = new Contract();
        foreach ($contractItemDatas as $contractItemData) {
            $contract->getFromDB($contractItemData['contracts_id']);
            $docurl = $CFG_GLPI["url_base"] . "/front/contract.form.php?id=" . $contractItemData['contracts_id'];
            $this->Cell(($largeurdispo/9)*2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['name'])) , 'L', 1, 'L', false, $docurl, 'black');
            $yligne4 = $this->GetY();
            $this->setXY($this->margin_left + (($largeurdispo/9)*2),$yligne3);
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
                if($begindate < $datenow && $enddate >= $datenow){
                    $costcontract += $cost['cost'];
                }
            }

            $this->Cell(($largeurdispo/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($typename)) , '', 1, 'C', false);
            $this->setXY($this->margin_left + (($largeurdispo/9)*4),$yligne3);
            $this->Cell(($largeurdispo/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['num'])) , '', 1, 'C', false);
            $this->setXY($this->margin_left + (($largeurdispo/9)*6),$yligne3);

            $datebegin = isset($contract->fields['begin_date']) ? new DateTime($contract->fields['begin_date']) : '';
            $this->Cell(($largeurdispo/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode(isset($contract->fields['begin_date']) ? $datebegin->format('Y-m-d') : '')) , '', 1, 'C', false);
            $this->setXY($this->margin_left + (($largeurdispo/9)*7),$yligne3);
            $this->Cell(($largeurdispo/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($contract->fields['duration'] . __(' months', 'webapplications'))) , '', 1, 'C', false);
            $this->setXY($this->margin_left + (($largeurdispo/9)*8),$yligne3);
            $this->Cell(($largeurdispo/9), $yligne4 - $yligne3, Toolbox::decodeFromUtf8(htmlspecialchars_decode($costcontract) . ' EUR') , 'R', 1, 'C', false);
            $yligne3 = $this->GetY();
        }
        $this->MultiCell($largeurdispo,  1, '' , 'RBL', 'C', false, 0, '', 'black');

        $this->AddPage("P");

        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Summary', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');


        //inclure ici le résumé des champs
        $statut = new State();
        $statut->getFromDB($appliance->fields['states_id']);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Name', 'webapplications') . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['name'])) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Status', 'webapplications') . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($statut->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());



        $location = new Location();
        $location->getFromDB($appliance->fields['locations_id']);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Associable to a ticket', 'webapplications') . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['is_helpdesk_visible'] ? __('Yes') : __('No'))) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Location', 'webapplications') . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($location->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $applianceType = new ApplianceType();
        $applianceType->getFromDB($appliance->fields['appliancetypes_id']);
        $user = new User();
        $user->getFromDB($appliance->fields['users_id_tech']);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Type', 'Types', 1)  . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($applianceType->fields['name'] ?? '')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Technician in charge') . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($user->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $manufacturer = new Manufacturer();
        $manufacturer->getFromDB($appliance->fields['manufacturers_id']);
        $group = new Group();
        $group->getFromDB($appliance->fields['groups_id_tech']);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(Manufacturer::getTypeName(1) . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($manufacturer->fields['name'] ?? '')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Group in charge') . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($group->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Alternate username number') . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['contact_num'] ?? '')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Serial number') . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['serial'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Alternate username') . ' : ')), 'L', 'L', false, 0, '', 'black');
        $yligne35 = $this->GetY();
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['contact'] ?? '')), '', 'L', false, 0, '', 'black');
        $yligne4 = $this->GetY();
        if ($yligne35 != $yligne4) {
            $this->setXY($this->margin_left, $yligne35);
            $this->MultiCell(($largeurdispo/4), $yligne4 - $yligne35, '', 'L', 'C', false, 0, '', 'black');
            $this->SetXY($this->margin_left + ($largeurdispo/4)*2, $yligne4);
        }
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Inventory number') . ' : ')), '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['otherserial'] ?? '')), 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

        $this->setXY($this->margin_left + ($largeurdispo/2), $this->GetY());
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2), $yligne4 - $this->GetY(), '', 'R', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($largeurdispo/2), $yligne5 - $yligne4, '', 'L', 'C', false, 0, '', 'black');
        }

        $user = new User();
        $user->getFromDB($appliance->fields['users_id']);
        $group = new Group();
        $group->getFromDB($appliance->fields['groups_id']);
        $this->setXY($this->margin_left, $this->GetY());
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(User::getTypeName(1) . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($user->fields['name'] ?? '')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(Group::getTypeName(1) . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($group->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());




        //commentaire
        if (!$config->fields['use_fields_description'] ||
            $config->fields['fields_description_table'] != 'Appliance' ||
            $config->fields['fields_description_name'] != 'comment') {
            $yligne3 = $this->GetY();
            $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Comment', 'Comments', 2) . ' : ')) , 'L', 'L', false, 0, '', 'black');
            $yligne35 = $this->GetY();
            $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
            $this->MultiCell(($largeurdispo/4)*3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($appliance->fields['comment'] ?? '')) , 'R', 'L', false, 0, '', 'black');
            $this->setXY($this->margin_left , $this->GetY());

            $yligne4 = $this->GetY();
            if ($yligne35 != $yligne4) {
                $this->setXY($this->margin_left, $yligne35);
                $this->MultiCell(($largeurdispo/4), $yligne4 - $yligne35 , '' , 'L', 'C', false, 0, '', 'black');
                $this->SetXY($this->margin_left, $yligne4);
            }
        }


        $applianceenvironnement = new ApplianceEnvironment();
        $applianceenvironnement->getFromDB($appliance->fields['applianceenvironments_id']);
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Environment', 'Environments', 1) . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell(($largeurdispo/4)*3, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($applianceenvironnement->fields['name'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left , $this->GetY());

        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('URL', 'webapplications') . ' : ')) , 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webappAppliance->fields['address'] ?? '')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Backoffice URL', 'webapplications')  . ' : ')) , '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webappAppliance->fields['backoffice'] ?? '')) , 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());


        $webapplicationServertype = new PluginWebapplicationsWebapplicationServerType();
        if (isset($webappAppliance->fields['webapplicationservertypes_id'])) {
            $webapplicationServertype->getFromDB($webappAppliance->fields['webapplicationservertypes_id']);
        }
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Installed version', 'webapplications') . ' : ')), 'L', 'L', false, 0, '', 'black');
        $yligne35 = $this->GetY();
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webappAppliance->fields['version'] ?? '')), '', 'L', false, 0, '', 'black');
        $yligne4 = $this->GetY();
        if ($yligne35 != $yligne4) {
            $this->setXY($this->margin_left, $yligne35);
            $this->MultiCell(($largeurdispo/4), $yligne4 - $yligne35, '', 'L', 'C', false, 0, '', 'black');
            $this->SetXY($this->margin_left + ($largeurdispo/4)*2, $yligne4);
        }
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Type of treatment server', 'Types of treatment server', 1, 'webapplications') . ' : ')), '', 'L', false, 0, '', 'black');
        $yligne45 = $this->GetY();
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationServertype->fields['name'] ?? '')), 'R', 'L', false, 0, '', 'black');

        $this->setXY($this->margin_left + ($largeurdispo/2), $this->GetY());
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2), $yligne4 - $this->GetY(), '', 'R', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($largeurdispo/2), $yligne5 - $yligne4, '', 'L', 'C', false, 0, '', 'black');
        } elseif ($yligne45 > $this->GetY()) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left, $yligne4);
            $this->MultiCell(($largeurdispo/2), $yligne45 - $yligne4, '', 'L', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + (($largeurdispo/4)*3), $yligne5);
            $this->MultiCell(($largeurdispo/4), $yligne45 - $yligne5, '', 'R', 'C', false, 0, '', 'black');
        }
        $this->setXY($this->margin_left, $this->GetY());

        $webapplicationtechnics = new PluginWebapplicationsWebapplicationTechnic();
        if (isset($webappAppliance->fields['webapplicationtechnics_id'])) {
            $webapplicationtechnics->getFromDB($webappAppliance->fields['webapplicationtechnics_id']);
        }
        $webapplicationexposition = new PluginWebapplicationsWebapplicationExternalExposition();
        if (isset($webappAppliance->fields['webapplicationexternalexpositions_id'])) {
            $webapplicationexposition->getFromDB($webappAppliance->fields['webapplicationexternalexpositions_id']);
        }
        $yligne3 = $this->GetY();
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Language of treatment', 'Languages of treatment', 1, 'webapplications') . ' : ')), 'L', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationtechnics->fields['name'] ?? '')), '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('External exposition', 'External exposition', 1, 'webapplications') . ' : ')), '', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationexposition->fields['name'] ?? '')), 'R', 'L', false, 0, '', 'black');
        $this->setXY($this->margin_left, $this->GetY());

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
                            if (!str_starts_with($rowfield['type'], 'dropdown-') && (!$config->fields['use_fields_description'] || $config->fields['fields_description_table'] != 'Fields' || $config->fields['fields_description_name'] != $rowfield['id'] )) {
                                if ($compteurfields == 0 || $compteurfields%2 == 0) {
                                    //impair (a gauche)
                                    $this->SetX($this->margin_left);
                                    $yligne3 = $this->GetY();
                                    $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($rowfield['label'] . ' : ')) , 'L', 'L', false, 0, '', 'black');
                                    $yligne35 = $this->GetY();
                                    $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
                                    $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($fieldsData[$rowfield['name']])) , '', 'L', false, 0, '', 'black');

                                    $compteurfields ++;
                                    $yligne4 = $this->GetY();
                                    if ($yligne35 != $yligne4) {
                                        $this->setXY($this->margin_left, $yligne35);
                                        $this->MultiCell(($largeurdispo/4), $yligne4 - $yligne35 , '' , 'L', 'C', false, 0, '', 'black');
                                        $this->SetXY($this->margin_left + ($largeurdispo/4)*2, $yligne4);
                                    }
                                } else {
                                    //pair à droite
                                    $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
                                    $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($rowfield['label'] . ' : ')) , '', 'L', false, 0, '', 'black');
                                    $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
                                    $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($fieldsData[$rowfield['name']])) , 'R', 'L', false, 0, '', 'black');
                                    $compteurfields ++;

                                    // Ajuster le tableau

                                    $this->setXY($this->margin_left + ($largeurdispo/2),$this->GetY());
                                    if ($this->GetY() < $yligne4) {
                                        $this->MultiCell(($largeurdispo/2), $yligne4 - $this->GetY() , '' , 'R', 'C', false, 0, '', 'black');
                                    } elseif ($this->GetY() > $yligne4) {
                                        $yligne5 = $this->GetY();
                                        $this->setXY($this->margin_left,$yligne4);
                                        $this->MultiCell(($largeurdispo/2), $yligne5 - $yligne4, '' , 'L', 'C', false, 0, '', 'black');

                                    }

                                }
                            }
                        }
                    }
                    if ($compteurfields %2 == 1) {
                        $this->setXY($this->margin_left + ($largeurdispo/2),$yligne3);
                        $this->MultiCell(($largeurdispo/2), $yligne4 - $yligne3 , '' , 'R', 'C', false, 0, '', 'black');
                    }
                }
            }
        }





        $this->setXY($this->margin_left, $this->GetY());
        $this->MultiCell($largeurdispo, 1, '' , 'LRB', 'L', false, 0, '', 'black');




        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Security Needs', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');

        $yligne3 = $this->GetY();
        $value = $webappAppliance->fields['webapplicationavailabilities'] ?? '';
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Availability', 'webapplications') . ' : ' . $value)), 'LRB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4), $yligne3);
        $value = $webappAppliance->fields['webapplicationintegrities'] ?? '';
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Integrity', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*2, $yligne3);
        $value = $webappAppliance->fields['webapplicationconfidentialities'] ?? '';
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Confidentiality', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/4)*3, $yligne3);
        $value = $webappAppliance->fields['webapplicationtraceabilities'] ?? '';
        $this->MultiCell($largeurdispo/4, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Traceability', 'webapplications') . ' : ' . $value)), 'RB', 'C', false, 0, '', 'black');

        $this->setXY($this->margin_left, $this->GetY() + 2);
        $this->MultiCell($largeurdispo, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');

        $yligne3 = $this->GetY();
        $answer = isset($webappAppliance->fields['webapplicationreferringdepartmentvalidation']) && $webappAppliance->fields['webapplicationreferringdepartmentvalidation'] == 0 ? __('No') : __('Yes');
        $this->MultiCell($largeurdispo/2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation of the request by the referring Department', 'webapplications') . ' : ')) . $answer, 'LRB', 'C', false, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/2), $yligne3);
        $answer = isset($webappAppliance->fields['webapplicationciovalidation']) && $webappAppliance->fields['webapplicationciovalidation'] == 0 ? __('No') : __('Yes');
        $this->MultiCell($largeurdispo/2, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Validation by CISO', 'webapplications') . ' : ')) . $answer, 'RB', 'C', false, 0, '', 'black');

        $this->AddPage("P");

        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Ecosystem', 'webapplications'))), 'TLRB', 'C', true, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/2) + 1, $yligne3);
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(_n('Process', 'Processes', 1))), 'TLRB', 'C', true, 0, '', 'black');

        $webapplicationentities = new PluginWebapplicationsEntity();

        $webapplicationprocesses = new PluginWebapplicationsProcess();

        $webapplicationentitiesDatas = $webapplicationentities->find();
        $webapplicationprocessesDatas = $webapplicationprocesses->find();

        $yligne3 = $this->GetY();

        foreach ($webapplicationentitiesDatas as $webapplicationentitiesData) {
            $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationentitiesData['name'])) , 'LR',  'C', false, $docurl, 'black');
            $this->setXY($this->margin_left,$this->GetY());
        }

        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne3);
        foreach ($webapplicationprocessesDatas as $webapplicationprocessesData) {
            $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationprocessesData['name'])) , 'LR',  'C', false, $docurl, 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2)-1, $yligne4 - $this->GetY() +1 , '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, 1 , '' , 'LRB', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, $yligne5 - $yligne4 + 1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) + 1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left ,$this->GetY());
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        }



        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Physical Infrastructure', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/2) + 1, $yligne3);
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Database instance', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');


        $applicationItems = new Appliance_Item();



        $yligne3 = $this->GetY();

        foreach (array("Computer", "Printer", "Phone", "NetworkEquipment") as $itemtype) {
            $physicalinfraDatas = $applicationItems->find(['appliances_id' => $this->id, 'itemtype' => $itemtype]);
            foreach ($physicalinfraDatas as $physicalinfraData) {
                $item = new $physicalinfraData['itemtype']();
                $item->getFromDB($physicalinfraData['items_id']);
                $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($item->fields['name'])) , 'LR',  'C', false);
                $this->setXY($this->margin_left,$this->GetY());
            }
        }


        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne3);

        $databasesInstanceDatas = $applicationItems->find(['appliances_id' => $this->id, 'itemtype' => 'DatabaseInstance']);
        foreach ($databasesInstanceDatas as $databasesInstanceData) {
            $databaseInstance = new DatabaseInstance();
            $databaseInstance->getFromDB($databasesInstanceData['items_id']);
            $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($databaseInstance->fields['name'])) , 'LR',  'C', false);
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2)-1, $yligne4 - $this->GetY() +1 , '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, 1 , '' , 'LRB', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, $yligne5 - $yligne4 + 1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) + 1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left ,$this->GetY());
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        }




        $this->setXY($this->margin_left, $this->GetY() + 2);
        $yligne3 = $this->GetY();
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Certificates', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');
        $this->setXY($this->margin_left + ($largeurdispo/2) + 1, $yligne3);
        $this->MultiCell(($largeurdispo/2) -1, ($yligne2 - $yligne) /2, Toolbox::decodeFromUtf8(htmlspecialchars_decode(__('Flow', 'webapplications'))) , 'TLRB', 'C', true, 0, '', 'black');

        $certificatItem = new Certificate_Item();
        $certificatItemDatas = $certificatItem->find(['items_id'=>$this->id, 'itemtype'=>'Appliance']);

        $webapplicationstream = new PluginWebapplicationsStream();
        $webapplicationstreamDatas = $webapplicationstream->find();

        $yligne3 = $this->GetY();

        foreach ($certificatItemDatas as $certificatItemData) {
            $certificat = new Certificate();
            $certificat->getFromDB($certificatItemData['certificates_id']);
            $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($certificat->fields['name'])) , 'LR',  'C', false, $docurl, 'black');
            $this->setXY($this->margin_left,$this->GetY());
        }

        $yligne4 = $this->GetY();

        $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne3);
        foreach ($webapplicationstreamDatas as $webapplicationstreamData) {
            $this->MultiCell(($largeurdispo/2) -1, 7, Toolbox::decodeFromUtf8(htmlspecialchars_decode($webapplicationstreamData['name'])) , 'LR',  'C', false, $docurl, 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$this->GetY());
        }
        if ($this->GetY() < $yligne4) {
            $this->MultiCell(($largeurdispo/2)-1, $yligne4 - $this->GetY() +1 , '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, 1 , '' , 'LRB', 'C', false, 0, '', 'black');
        } elseif ($this->GetY() > $yligne4) {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left,$yligne4);
            $this->MultiCell(($largeurdispo/2) -1, $yligne5 - $yligne4 + 1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) + 1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        } else {
            $yligne5 = $this->GetY();
            $this->setXY($this->margin_left ,$this->GetY());
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
            $this->setXY($this->margin_left + ($largeurdispo/2) +1,$yligne5);
            $this->MultiCell(($largeurdispo/2) -1,  1, '' , 'LRB', 'C', false, 0, '', 'black');
        }


        $this->Output('D', $appliance->fields['name'] . '.pdf');
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

    /**
     * @param $name
     * @param $tickets_id
     * @param $entities_id
     *
     * @return \Document_Item
     */
    public function addDocument($name, $itemtype, $items_id, $entities_id)
    {
        //Construction du chemin du fichier
        //      $filename = "metademand_" . $idTicket . ".pdf";
        $filename = $name . ".pdf";
        $this->Output(GLPI_DOC_DIR . "/_uploads/" . $filename, 'F');

        //Création du document
        $doc = new Document();
        //Construction des données
        $input = [];
        $input["name"] = addslashes($filename);
        $input["upload_file"] = $filename;
        $input["mime"] = "application/pdf";
        $input["date_mod"] = date("Y-m-d H:i:s");
        $input["users_id"] = Session::getLoginUserID();
        $input["entities_id"] = $entities_id;
        if ($itemtype == 'Ticket') {
            $input["tickets_id"] = $items_id;
        }
        //Initialisation du document
        $newdoc = $doc->add($input);
        $docitem = new Document_Item();

        //entities_id
        $docitem->add(['itemtype' => $itemtype,
            "documents_id" => $newdoc,
            "items_id" => $items_id,
            "entities_id" => $entities_id]);
        return $docitem;
    }
}
