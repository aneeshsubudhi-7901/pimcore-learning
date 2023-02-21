<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\DataObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pimcore\Model\Element\Service;

class ObjectCreateController extends AbstractController
{
    // /**
    //  * @Route("/createTrial", methods={"POST"},name="create_trial")
    //  */
    public function createObj(Request $request): JsonResponse
    {
        //name of the object
        //parent id of the object
        //name property of the object
        //sku property of the object
        //description property of the object
        // $obj = json_decode($request->getContent());
        $obj = $request->toArray();
        $trialObject = new DataObject\TrialClass();
        $trialObject->setKey(Service::getValidKey($obj["nameObj"], "object"));
        $trialObject->setParentId(8);
        $trialObject->setSku($obj["sku"]);
        $trialObject->setName($obj["name"]);
        $trialObject->setDescription($obj["description"]);
        try {
            $trialObject->save();
            return new JsonResponse([
                "data" => $obj,
                "message" => "Successful!",
            ]);
        } catch (\Throwable $th) {
            // return new JsonResponse(["data" => $obj, "message" => "Error"]);
            throw $th;
        }
    }
}
