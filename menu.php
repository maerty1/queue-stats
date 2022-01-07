<?php
/*
Copyright 2017, https://asterisk-pbx.ru

This file is part of Asterisk Call Center Stats.
Asterisk Call Center Stats is free software: you can redistribute it
and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

Asterisk Call Center Stats is distributed in the hope that it will be
useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Asterisk Call Center Stats.  If not, see
<http://www.gnu.org/licenses/>.
 */
?>
<div id="sidebar">&nbsp;</div>
<div id="content">
    <div id='header'>
        <ul id='primary'>
            <?php
            /* меню */
            $menu[] = $lang["$language"]['menu_home'];
            $menu[] = $lang["$language"]['menu_answered'];
            $menu[] = $lang["$language"]['ans_cdr'];
            $menu[] = $lang["$language"]['menu_unanswered'];
            $menu[] = $lang["$language"]['unans_cdr'];
            $menu[] = $lang["$language"]['outbound'];
            $menu[] = $lang["$language"]['menu_distribution'];
            $menu[] = $lang["$language"]['distr_by_agents_dates'];
            //$menu[] = $lang["$language"]['distr_by_agents_hours'];
            $menu[] = $lang["$language"]['compare'];
            $menu[] = $lang["$language"]['search'];
            $menu[] = $lang["$language"]['realtime'];

            /* линки к меню */
            $link[] = "index.php";
            $link[] = "answered.php";
            $link[] = "answered_cdr.php";
            $link[] = "unanswered.php";
            $link[] = "unanswered_cdr.php";
            $link[] = "outbound.php";
            $link[] = "distribution.php";
            $link[] = "areport.php";
            //$link[] = "qreport.php";
            $link[] = "compare.php";
            $link[] = "search.php";
            $link[] = "realtime.php";

            $anchor = array();

            for ($a = 0; $a < count($menu); $a++) {
                if (basename($self) == $link[$a]) {
                    echo "<li><span>" . $menu[$a] . "</span></li>\n";
                    if (count($anchor) > 0 && $a = $b) {
                        echo "<ul id='secondary'>\n";
                        $contador = 1;
                        foreach ($anchor as $item) {
                            echo "<li><a href='#$contador'>$item</a></li>\n";
                            $contador++;
                        }
                        echo "</ul>\n";
                    }

                } else {
                    if (isset($_SESSION['QSTATS']['start'])) {
                        echo "<li><a href='" . $link["$a"] . "'>" . $menu["$a"] . "</a></li>\n";
                    }
                }
            }
            ?>
        </ul>

    </div>
