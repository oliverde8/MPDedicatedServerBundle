<?php

namespace oliverde8\MPDedicatedServerBundle\Controller;

use Manialib\Formatting\String;
use oliverde8\MPDedicatedServerBundle\Service\DedicatedServer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    public function serverInfoAction($login)
    {
        /** @var DedicatedServer $dedicated */
        $dedicated = $this->get('oliverde8_mp_dedicated_server.dedicated_server');
        $serverInfo = $dedicated->getServerInfo($login);

        if (!$serverInfo) {
            $serverInfo = $this->serverUnAvailableError();
        } else {
            // Remove some information
            unset($serverInfo->serverOptions->password);
            unset($serverInfo->serverOptions->passwordForSpectator);
            unset($serverInfo->serverOptions->currentUseChangingValidationSeed);
            unset($serverInfo->serverOptions->nextUseChangingValidationSeed);
            unset($serverInfo->serverOptions->useChangingValidationSeed);
            unset($serverInfo->serverOptions->refereePassword);
            unset($serverInfo->serverOptions->refereeMode);

            $serverInfo->maps = $dedicated->getMapList($login);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setMaxAge(60);
        $response->setPublic();
        $response->setContent(json_encode($serverInfo));
        $response->setLastModified(new \DateTime());


        return $response;
    }

    public function serverChatLinesAction($login)
    {
        /** @var DedicatedServer $dedicated */
        $dedicated = $this->get('oliverde8_mp_dedicated_server.dedicated_server');
        $chatLines = $dedicated->getServerChatLines($login);

        if (is_null($chatLines)) {
            $chatLines = $this->serverUnAvailableError();
        }

        return $this->prepareResponse($chatLines);
    }

    public function serverMapListAction($login)
    {
        /** @var DedicatedServer $dedicated */
        $dedicated = $this->get('oliverde8_mp_dedicated_server.dedicated_server');
        $mapList = $dedicated->getMapList($login);

        if (!$mapList) {
            $mapList = $this->serverUnAvailableError();
        }

        return $this->prepareResponse($mapList);
    }

    protected function prepareResponse($data) {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setMaxAge(60);
        $response->setPublic();
        $response->setContent(json_encode($data));
        $response->setLastModified(new \DateTime());

        return $response;
    }

    protected function serverUnAvailableError()
    {
        return array("error" => true, "message" => "Can't connect to server");
    }
}
