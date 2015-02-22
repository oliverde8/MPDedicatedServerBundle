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

namespace oliverde8\MPDedicatedServerBundle\Service;

use Doctrine\Common\Cache\Cache;
use Maniaplanet\DedicatedServer\Connection;
use oliverde8\MPDedicatedServerBundle\Data\ServerInfo;

/**
 * Class DedicatedServer is an abstraction layer that adds a caching of the dedicated server data.
 *
 * @package oliverde8\MPDedicatedServerBundle\Service
 */
class DedicatedServer {

    private $servers;

    /** @var  Cache */
    private $serverInfoCache;

    /** @var Connection[] */
    private $connections = array();

    function __construct($servers, Cache $serverInfoCache)
    {
        $this->servers = $servers['servers'];
        $this->serverInfoCache = $serverInfoCache;
    }

    /**
     * Returns server information. If server information not in cache will make call to the dedicated server.
     * Try to use this with ajax calls in order to have a faster website.
     *
     * @param string $login Login of the server
     *
     * @return ServerInfo|null
     */
    public function getServerInfo($login)
    {
        if (isset($this->servers[$login])) {
            $cacheKey = $this->_getServerInfoCacheKey($login);
            $cacheResult = $this->serverInfoCache->fetch($cacheKey);
            if ($cacheResult) {
                return $cacheResult;
            } else {
                $data = $this->_getServerInfo($login);

                $this->serverInfoCache->save($cacheKey, $data, 60);

                return $data;
            }
        }

        return null;
    }

    /**
     * Returns list of maps on the server. If list of map not in cache will make a call to the dedicatd server.
     * Try to use this with ajax calls in order to have a faster website.
     *
     * @param string $login Login of the server
     *
     * @return \Maniaplanet\DedicatedServer\Structures\Map[]
     */
    public function getMapList($login) {
        if (isset($this->servers[$login])) {
            $cacheKey = $this->_getServerMapsCacheKey($login);
            $cacheResult = $this->serverInfoCache->fetch($cacheKey);
            if ($cacheResult) {
                return $cacheResult;
            } else {
                $data = array();
                try {
                    $connection = $this->getConnection($login);
                    if ($connection) {
                        $data = $connection->getMapList(-1, 0);
                        $this->serverInfoCache->save($login, $data, 360);
                    }
                } catch (\Exception $e) {
                    //If can't connect keep shorter in cache
                    $this->serverInfoCache->save($login, array(), 60);
                }

                return $data;
            }
        }
    }

    /**
     * Returns login, server name association.
     * This is the name you defined in the config and therfore won't make any dedicated calls.
     *
     * @return String[]
     */
    public function getServerNames() {
        $serverNames = array();
        foreach($this->servers as $login => $data) {
            $serverNames[$login] = $data['name'];
        }
        return $serverNames;
    }

    /**
     * Get the cache key for server information
     *
     * @param $login
     *
     * @return string
     */
    protected function _getServerInfoCacheKey($login)
    {
        return 'mp_server_info__'.$login;
    }

    /**
     * Get the cache key for server maps
     *
     * @param $login
     *
     * @return string
     */
    protected function _getServerMapsCacheKey($login)
    {
        return 'mp_server_maps__'.$login;
    }

    protected function _getServerInfo($login)
    {
        try {
            $connection = $this->getConnection($login);

            if ($connection) {
                $serverInfo = new ServerInfo();
                $serverInfo->updateTime = time();

                $serverInfo->serverOptions = $connection->getServerOptions();

                $serverInfo->currentMap = $connection->getCurrentMapInfo();

                $players = $connection->getPlayerList(-1, 0);
                foreach ($players as $player) {
                    if ($player->spectator) {
                        $serverInfo->serverSpectators[] = $player;
                    } else {
                        $serverInfo->serverPlayers[] = $player;
                    }
                }

                return $serverInfo;
            }

        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Connects to the dedicated server API, if connection already established will send existing connextion
     *
     * @param string $login Server to connect to
     *
     * @return Connection
     */
    public function getConnection($login) {

        if (isset($this->servers[$login])) {
            if (!isset($this->connections[$login])) {
                $conf = $this->servers[$login];
                $this->connections[$login] = Connection::factory($conf['host'], $conf['port'], 1, $conf['user'], $conf['password']);
            }

            return $this->connections[$login];
        }
        return null;
    }
}