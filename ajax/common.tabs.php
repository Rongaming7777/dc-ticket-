<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2022 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

include('../inc/includes.php');

if (isset($_GET['full_page_tab'])) {
    Html::header('Only tab for debug', $_SERVER['PHP_SELF']);
} else {
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

// Not possible to check right for anonymous FAQ
//Session::checkLoginUser();

if (!isset($_GET['_glpi_tab'])) {
    exit();
}

if (!isset($_GET['_itemtype']) || empty($_GET['_itemtype'])) {
    exit();
}

if (!isset($_GET["sort"])) {
    $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
    $_GET["order"] = "";
}

if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $_GET['id'] = (int)$_GET['id'];
}

if ($item = getItemForItemtype($_UGET['_itemtype'])) {
    if ($item->get_item_to_display_tab) {
       // No id if ruleCollection but check right
        if ($item instanceof RuleCollection) {
            if (!$item->canList()) {
                exit();
            }
        } else if (!isset($_GET["id"]) || $item->isNewID($_GET["id"])) {
            if (!$item->can(-1, CREATE, $_GET)) {
                exit();
            }
        } else if (!$item->can($_GET["id"], READ)) {
            exit();
        }
    }
}

if (isset($_GET['_target'])) {
    $_GET['_target'] = Toolbox::cleanTarget($_GET['_target']);
}

$tabs = Toolbox::getAvailablesTabs($_UGET['_itemtype'], $_GET['id'] ?? null);
if (isset($tabs[$_GET['_glpi_tab']])) {
    Session::setActiveTab($_UGET['_itemtype'], $_GET['_glpi_tab']);
}

$notvalidoptions = ['_glpi_tab', '_itemtype', 'sort', 'order', 'withtemplate', 'formoptions'];
$options         = $_GET;
foreach ($notvalidoptions as $key) {
    if (isset($options[$key])) {
        unset($options[$key]);
    }
}
if (isset($options['locked'])) {
    ObjectLock::setReadOnlyProfile();
}

CommonGLPI::displayStandardTab($item, $_UGET['_glpi_tab'], $_GET["withtemplate"], $options);


if (isset($_GET['full_page_tab'])) {
   // I think that we should display this warning, because tabs are not prepare
   // for being used full space ...
    if (!isset($_SESSION['glpi_warned_about_full_page_tab'])) {
       // Debug string : not really need translation.
        $msg  = 'WARNING: full page tabs are only for debug/validation purpose !\n';
        $msg .= 'Actions on this page may have undefined result.';
        echo "<script type='text/javascript' >\n";
        echo "alert('$msg')";
        echo "</script>";
        $_SESSION['glpi_warned_about_full_page_tab'] = true;
    }

    Html::footer();
} else {
    Html::ajaxFooter();
}
