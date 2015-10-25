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
use Kitpages\SemaphoreBundle\Manager\ManagerInterface as SemaphoreInterface;
use Maniaplanet\DedicatedServer\Connection;
use oliverde8\MPDedicatedServerBundle\Data\ServerInfo;

/**
 * Class DedicatedServer is an abstraction layer that adds a caching of the dedicated server data.
 *
 * @package oliverde8\MPDedicatedServerBundle\Service
 */
class DedicatedServer {

    private $servers;

    private $cacheTimeOutInfo;
    private $cacheTimeOutMap;
    private $cacheTimeOutMapRetry;
    private $cacheTimeOutChat;

    /** @var  Cache */
    private $serverInfoCache;

    /** @var SemaphoreInterface */
    private $semaphore;

    /** @var Connection[] */
    private $connections = array();

    function __construct($servers, Cache $serverInfoCache, SemaphoreInterface $semaphore)
    {
        $this->servers = $servers['servers'];
        $this->cacheTimeOutInfo = $servers['cache']['info_timeout'];
        $this->cacheTimeOutMap = $servers['cache']['map_timeout'];
        $this->cacheTimeOutMapRetry = $servers['cache']['map_retry_timeout'];
        $this->cacheTimeOutChat = $servers['cache']['chat_timeout'];

        $this->serverInfoCache = $serverInfoCache;
        $this->semaphore = $semaphore;
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
        static $lockAcquired = false;

        if (isset($this->servers[$login])) {
            $cacheKey = $this->_getServerInfoCacheKey($login);
            $cacheResult = $this->serverInfoCache->fetch($cacheKey);
            if ($cacheResult) {
                return $cacheResult;
            } else if (!$lockAcquired) {
                // We don't have the lock, we need to get it before asking the dedicated.

                // Start Protected code.
                $this->semaphore->aquire($cacheKey);
                $lockAcquired = true;

                // Ask for data again, if cache filled from other instance then data from cache, if not wil do a call.
                $data = $this->getServerInfo($login);

                // End protected code.
                $lockAcquired = false;
                $this->semaphore->release($cacheKey);

                return $data;
            } else {
                // We have the lock, data not in cache get data from the server.
                $data = $this->_getServerInfo($login);
                $this->serverInfoCache->save($cacheKey, $data, $this->cacheTimeOutInfo);

                return $data;
            }
        }

        return null;
    }

    /**
     * Returns chat lines.
     * Try to use this with ajax calls in order to have a faster website.
     *
     * @param string $login Login of the server
     *
     * @return ServerInfo|null
     */
    public function getServerChatLines($login)
    {
        static $lockAcquired = false;

        if (isset($this->servers[$login])) {
            $cacheKey = $this->_getServerChatCacheKey($login);
            $cacheResult = $this->serverInfoCache->fetch($cacheKey);
            if ($cacheResult) {
                return $cacheResult;
            } else if (!$lockAcquired) {
                // We don't have the lock, we need to get it before asking the dedicated.

                // Start Protected code.
                $this->semaphore->aquire($cacheKey);
                $lockAcquired = true;

                // Ask for data again, if cache filled from other instance then data from cache, if not wil do a call.
                $data = $this->getServerChatLines($login);

                // End protected code.
                $lockAcquired = false;
                $this->semaphore->release($cacheKey);

                return $data;
            } else {
                $data = array();
                try {
                    $connection = $this->getConnection($login);
                    if ($connection) {
                        $data = $connection->getChatLines();
                    }
                } catch (\Exception $e) {

                }
                $this->serverInfoCache->save($cacheKey, $data, $this->cacheTimeOutChat);

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
        static $lockAcquired = false;

        if (isset($this->servers[$login])) {
            $cacheKey = $this->_getServerMapsCacheKey($login);
            $cacheResult = $this->serverInfoCache->fetch($cacheKey);
            if ($cacheResult) {
                return $cacheResult;
            } else if (!$lockAcquired) {
                // We don't have the lock, we need to get it before asking the dedicated.

                // Start Protected code.
                $this->semaphore->aquire($cacheKey);
                $lockAcquired = true;

                // Ask for data again, if cache filled from other instance then data from cache, if not wil do a call.
                $data = $this->getMapList($login);

                // End protected code.
                $lockAcquired = false;
                $this->semaphore->release($cacheKey);

                return $data;
            } else {
                $data = array();
                try {
                    $connection = $this->getConnection($login);
                    if ($connection) {
                        $data = $connection->getMapList(-1, 0);
                        $this->serverInfoCache->save($cacheKey, $data, $this->cacheTimeOutMap);
                    }
                } catch (\Exception $e) {
                    //If can't connect keep shorter in cache
                    $this->serverInfoCache->save($cacheKey, array(), $this->cacheTimeOutMapRetry);
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

    public function getServerName($login) {
        return (isset($this->servers[$login]) ? $this->servers[$login]['name'] : '');
    }

    /**
     * Get the cache key for server chat
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
     * Get the cache key for server information
     *
     * @param $login
     *
     * @return string
     */
    protected function _getServerChatCacheKey($login)
    {
        return 'mp_server_cache__'.$login;
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

    public static function formatPastTime($time, $nbDetails, $precisionToIgnore = 1) {
        $info = array();
        $totalMinutes = ((int) ($time / 60));
        // Number of seconds.
        $info[] = $time - ($totalMinutes * 60) . ' ' . 's';
        if ($totalMinutes > 0) {
            $totalHours = ((int) ($totalMinutes / 60));
            // Number of minutes.
            $info[] = $totalMinutes - ($totalHours * 60) . ' ' . 'min';
            if ($totalHours > 0) {
                $totalDays = ((int) ($totalHours / 24));
                // Number of hours.
                $info[] = $totalHours - ($totalDays * 24) . ' ' . 'h';
                if ($totalDays > 0) {
                    $info[] = $totalDays . ' ' . 'd';
                }
            }
        }
        $start = count($info) - 1;
        $stop = $start - $nbDetails + 1;
        if ($stop < $precisionToIgnore) {
            $stop = $precisionToIgnore;
        }
        elseif ($stop < 0) {
            $stop = 0;
        }
        $content = '';
        for ($i = $start; $i >= $stop; $i--) {
            $content .= $info[$i] . ' ';
        }
        return $content;
    }
}