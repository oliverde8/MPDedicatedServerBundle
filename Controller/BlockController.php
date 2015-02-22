<?php
/**
 * @author      Oliver de Cramer (oliverde8 at gmail.com)
 * @copyright    GNU GENERAL PUBLIC LICENSE
 *                     Version 3, 29 June 2007
 *
 * PHP version 5.3 and above
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see {http://www.gnu.org/licenses/}.
 */

namespace oliverde8\MPDedicatedServerBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BlockController extends Controller {

    public function serverListAction($urlPath, $class)
    {
        $serverNames = $this->get('oliverde8_mp_dedicated_server.dedicated_server')->getServerNames();
        return $this->render(
            'oliverde8MPDedicatedServerBundle:Block:server.list.html.twig',
            array('serverNames' => $serverNames, 'urlPath' => $urlPath, 'class' => $class));
    }

    public function serverWidgetAction($login, $urlPath, $class = '', $standolone = true)
    {
        if ($standolone) {
            $class .= 'mp_server__info';
        }

        return $this->render(
            'oliverde8MPDedicatedServerBundle:Block:server.widget.html.twig',
            array('login' => $login, 'urlPath' => $urlPath, 'class' => $class));
    }
} 