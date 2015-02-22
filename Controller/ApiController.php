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
        /** @var \Doctrine\Common\Cache\Cache $cache */
        $cache = $this->container->get('doctrine_cache.providers.oliverde8_mp_dedicated_server__info');

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



    protected function serverUnAvailableError()
    {
        return array("error" => true, "message" => "Can't connect to server");
    }
}
