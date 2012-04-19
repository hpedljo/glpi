<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Contact class
**/
class Contact extends CommonDBTM{

   // From CommonDBTM
   public $dohistory = true;


   static function getTypeName($nb=0) {
      return _n('Contact', 'Contacts', $nb);
   }


   function canCreate() {
      return Session::haveRight('contact_enterprise', 'w');
   }

   function canView() {
      return Session::haveRight('contact_enterprise', 'r');
   }


   function cleanDBonPurge() {

      $cs = new Contact_Supplier();
      $cs->cleanDBonItemDelete($this->getType(), $this->fields['id']);
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab('Contact_Supplier', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Link', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   /**
    * Get address of the contact (company one)
    *
    *@return string containing the address
   **/
   function GetAddress() {
      global $DB;

      $query = "SELECT `glpi_suppliers`.`name`, `glpi_suppliers`.`address`,
                       `glpi_suppliers`.`postcode`, `glpi_suppliers`.`town`,
                       `glpi_suppliers`.`state`, `glpi_suppliers`.`country`
                FROM `glpi_suppliers`, `glpi_contacts_suppliers`
                WHERE `glpi_contacts_suppliers`.`contacts_id` = '".$this->fields["id"]."'
                      AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            if ($data = $DB->fetch_assoc($result)) {
               return $data;
            }
         }
      }
      return "";
   }


   /**
    * Get website of the contact (company one)
    *
    *@return string containing the website
   **/
   function GetWebsite() {
      global $DB;

      $query = "SELECT `glpi_suppliers`.`website` as website
                FROM `glpi_suppliers`, `glpi_contacts_suppliers`
                WHERE `glpi_contacts_suppliers`.`contacts_id` = '".$this->fields["id"]."'
                      AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
            return $DB->result($result, 0, "website");
         }
         return "";
      }
   }


   /**
    * Print the contact form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
   **/
   function showForm($ID, $options=array()) {

      $this->initForm($ID, $options);
      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Surname')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td rowspan='4' class='middle right'>".__('Comments')."</td>";
      echo "<td class='middle' rowspan='4'>";
      echo "<textarea cols='45' rows='7' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('First name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "firstname");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Phone')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "phone");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Phone 2')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "phone2");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Mobile phone')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "mobile");
      echo "</td>";
      echo "<td class='middle'>".__('Address')."</td>";
      echo "<td class='middle'>";
      echo "<textarea cols='37' rows='3' name='address'>".$this->fields["address"]."</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Fax')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "fax");
      echo "</td>";
      echo "<td>".__('Postal code')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "postcode", array('size' => 10));
      echo "&nbsp;&nbsp;". __('City'). "&nbsp;";
      Html::autocompletionTextField($this, "town", array('size' => 23));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>"._n('Email', 'Emails', 1)."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "email");
      echo "</td>";
      echo "<td>".__('State')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "state");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type')."</td>";
      echo "<td>";
      Dropdown::show('ContactType', array('value' => $this->fields["contacttypes_id"]));
      echo "</td>";
      echo "<td>".__('Country')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "country");
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Title') . "</td><td>";
      Dropdown::show('UserTitle', array('value' => $this->fields["usertitles_id"]));
      echo "<td>&nbsp;</td><td class='center'>";
      if ($ID > 0) {
         echo "<a target=''_blank' href='".$this->getFormURL().
                "?getvcard=1&amp;id=$ID'>".__('Vcard')."</a>";
      }
      echo "</td></tr>";


      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;
   }


   function getSearchOptions() {

      $tab                      = array();
      $tab['common']            = __('Characteristics');

      $tab[1]['table']          = $this->getTable();
      $tab[1]['field']          = 'name';
      $tab[1]['name']           = __('Surname');
      $tab[1]['datatype']       = 'itemlink';
      $tab[1]['itemlink_type']  = $this->getType();
      $tab[1]['massiveaction']  = false;

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'firstname';
      $tab[11]['name']          = __('First name');

      $tab[2]['table']          = $this->getTable();
      $tab[2]['field']          = 'id';
      $tab[2]['name']           = __('ID');
      $tab[2]['massiveaction']  = false;

      $tab[3]['table']          = $this->getTable();
      $tab[3]['field']          = 'phone';
      $tab[3]['name']           = __('Phone');
      $tab[3]['datatype']       = 'string';

      $tab[4]['table']          = $this->getTable();
      $tab[4]['field']          = 'phone2';
      $tab[4]['name']           = __('Phone 2');
      $tab[4]['datatype']       = 'string';

      $tab[10]['table']         = $this->getTable();
      $tab[10]['field']         = 'mobile';
      $tab[10]['name']          = __('Mobile phone');
      $tab[10]['datatype']      = 'string';

      $tab[5]['table']          = $this->getTable();
      $tab[5]['field']          = 'fax';
      $tab[5]['name']           = __('Fax');
      $tab[5]['datatype']       = 'string';

      $tab[6]['table']          = $this->getTable();
      $tab[6]['field']          = 'email';
      $tab[6]['name']           = _n('Email', 'Emails', 1);
      $tab[6]['datatype']       = 'email';
      $tab[6]['datatype']       = 'string';

      $tab[82]['table']         = $this->getTable();
      $tab[82]['field']         = 'address';
      $tab[82]['name']          = __('Address');

      $tab[84]['table']         = $this->getTable();
      $tab[84]['field']         = 'town';
      $tab[84]['name']          = __('City');

      $tab[83]['table']         = $this->getTable();
      $tab[83]['field']         = 'postcode';
      $tab[83]['name']          = __('Postal code');

      $tab[85]['table']         = $this->getTable();
      $tab[85]['field']         = 'state';
      $tab[85]['name']          = __('State');

      $tab[87]['table']         = $this->getTable();
      $tab[87]['field']         = 'country';
      $tab[87]['name']          = __('Country');


      $tab[9]['table']          = 'glpi_contacttypes';
      $tab[9]['field']          = 'name';
      $tab[9]['name']           = __('Type');

      $tab[81]['table']         = 'glpi_usertitles';
      $tab[81]['field']         = 'name';
      $tab[81]['name']          = __('Title');

      $tab[8]['table']          = 'glpi_suppliers';
      $tab[8]['field']          = 'name';
      $tab[8]['name']           = __('Associated suppliers');
      $tab[8]['forcegroupby']   = true;
      $tab[8]['datatype']       = 'itemlink';
      $tab[8]['itemlink_type']  = 'Supplier';
      $tab[8]['joinparams']     = array('beforejoin'
                                         => array('table'      => 'glpi_contacts_suppliers',
                                                  'joinparams' => array('jointype' => 'child')));

      $tab[16]['table']         = $this->getTable();
      $tab[16]['field']         = 'comment';
      $tab[16]['name']          = __('Comments');
      $tab[16]['datatype']      = 'text';

      $tab[90]['table']         = $this->getTable();
      $tab[90]['field']         = 'notepad';
      $tab[90]['name']          = __('Notes');
      $tab[90]['massiveaction'] = false;

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = __('Entity');
      $tab[80]['massiveaction'] = false;

      $tab[86]['table']         = $this->getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = __('Child entities');
      $tab[86]['datatype']      = 'bool';

      return $tab;
   }


   /**
    * Print the HTML array for entreprises on the current contact
    *
    *@return Nothing (display)
   **/
   function showSuppliers() {
      global $DB,$CFG_GLPI;

      $instID = $this->fields['id'];

      if (!$this->can($instID,'r')) {
         return false;
      }

      $canedit = $this->can($instID,'w');

      $query = "SELECT `glpi_contacts_suppliers`.`id`,
                       `glpi_suppliers`.`id` AS entID,
                       `glpi_suppliers`.`name` AS name,
                       `glpi_suppliers`.`website` AS website,
                       `glpi_suppliers`.`fax` AS fax,
                       `glpi_suppliers`.`phonenumber` AS phone,
                       `glpi_suppliers`.`suppliertypes_id` AS type,
                       `glpi_suppliers`.`is_deleted`,
                       `glpi_entities`.`id` AS entity
                FROM `glpi_contacts_suppliers`, `glpi_suppliers`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id`=`glpi_suppliers`.`entities_id`)
                WHERE `glpi_contacts_suppliers`.`contacts_id` = '$instID'
                      AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`".
                      getEntitiesRestrictRequest(" AND","glpi_suppliers",'','',true) ."
                ORDER BY `glpi_entities`.`completename`, `name`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $i      = 0;

      echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/front/contact.form.php\">";
      echo "<div class='spaced'><table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='7'>";
      if ($DB->numrows($result) == 0) {
         _e('No associated supplier');
      } else {
         echo _n('Associated supplier', 'Associated suppliers', $DB->numrows($result));
      }
      echo "</th></tr>";

      echo "<tr><th>".__('Supplier')."</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Third party type')."</th>";
      echo "<th>". __('Phone')."</th>";
      echo "<th>".__('Fax')."</th>";
      echo "<th>".__('Website')."</th>";
      echo "<th>&nbsp;</th></tr>";

      $used = array();
      if ($number > 0) {
         Session::initNavigateListItems('Supplier',
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                $this->getTypeName(1), $this->getName()));

         while ($data = $DB->fetch_assoc($result)) {
            $ID = $data["id"];
            Session::addToNavigateListItems('Supplier', $data["entID"]);
            $used[$data["entID"]] = $data["entID"];
            $website              = $data["website"];

            if (!empty($website)) {
               $website = $data["website"];

               if (!preg_match("?https*://?",$website)) {
                  $website = "http://".$website;
               }
               $website = "<a target=_blank href='$website'>".$data["website"]."</a>";
            }

            echo "<tr class='tab_bg_1".($data["is_deleted"]?"_2":"")."'>";
            echo "<td class='center'>";
            echo "<a href='".$CFG_GLPI["root_doc"]."/front/supplier.form.php?id=".$data["entID"]."'>".
                   Dropdown::getDropdownName("glpi_suppliers", $data["entID"])."</a></td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data["entity"]);
            echo "</td>";
            echo "<td class='center'>".Dropdown::getDropdownName("glpi_suppliertypes", $data["type"]);
            echo "</td>";
            echo "<td class='center' width='80'>".$data["phone"]."</td>";
            echo "<td class='center' width='80'>".$data["fax"]."</td>";
            echo "<td class='center'>".$website."</td>";
            echo "<td class='tab_bg_2 center'>";

            if ($canedit) {
               echo "<a href='".$CFG_GLPI["root_doc"]."/front/contact.form.php?deletecontactsupplier=1".
                     "&amp;id=$ID&amp;contacts_id=$instID'>
                     <span class='b'>".__('Delete')."</span></a>";
            } else {
               echo "&nbsp;";
            }
            echo "</td></tr>";
         }
      }

      if ($canedit) {
         if ($this->fields["is_recursive"]) {
            $nb = countElementsInTableForEntity("glpi_suppliers", getSonsOf("glpi_entities",
                                                $this->fields["entities_id"]));
         } else {
            $nb = countElementsInTableForEntity("glpi_suppliers", $this->fields["entities_id"]);
         }

         if ($nb > count($used)) {
            echo "<tr class='tab_bg_1'><td>&nbsp;</td><td class='center' colspan='4'>";
            echo "<input type='hidden' name='contacts_id' value='$instID'>";
            Dropdown::show('Supplier', array('used'        => $used,
                                             'entity'      => $this->fields["entities_id"],
                                             'entity_sons' => $this->fields["is_recursive"]));

            echo "&nbsp;&nbsp;<input type='submit' name='addcontactsupplier'
                               value='"._sx('button', 'Add')."' class='submit'>";
            echo "</td><td>&nbsp;</td><td>&nbsp;</td>";
            echo "</tr>";
         }
      }
      echo "</table></div></form>";
   }


   /**
    * Generate the Vcard for the current Contact
    *
    *@return Nothing (display)
   **/
   function generateVcard() {

      include (GLPI_ROOT . "/lib/vcardclass/classes-vcard.php");

      if (!$this->can($this->fields['id'],'r')) {
         return false;
      }

      // build the Vcard
      $vcard = new vCard();

      $vcard->setName($this->fields["name"], $this->fields["firstname"], "", "");

      $vcard->setPhoneNumber($this->fields["phone"], "PREF;WORK;VOICE");
      $vcard->setPhoneNumber($this->fields["phone2"], "HOME;VOICE");
      $vcard->setPhoneNumber($this->fields["mobile"], "WORK;CELL");

      $addr = $this->GetAddress();
      if (is_array($addr)) {
         $vcard->setAddress($addr["name"], "", $addr["address"], $addr["town"], $addr["state"],
                            $addr["postcode"], $addr["country"], "WORK;POSTAL");
      }
      $vcard->setEmail($this->fields["email"]);
      $vcard->setNote($this->fields["comment"]);
      $vcard->setURL($this->GetWebsite(), "WORK");

      // send the  VCard
      $output   = $vcard->getVCard();
      $filename = $vcard->getFileName();      // "xxx xxx.vcf"

      @Header("Content-Disposition: attachment; filename=\"$filename\"");
      @Header("Content-Length: ".Toolbox::strlen($output));
      @Header("Connection: close");
      @Header("content-type: text/x-vcard; charset=UTF-8");

      echo $output;
   }

}
?>