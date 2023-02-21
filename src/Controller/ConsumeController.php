<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Pimcore\Model\DataObject;
// use Pimcore\Log\ApplicationLogger;
use App\Service\ApplicationLoggerTZ;
use App\Service\MessageGenerator;
use \Exception;

class ConsumeController extends AbstractController
{
    //GET /filterGet
    public function filterGet(
        Request $request,
        ApplicationLoggerTZ $logger
    ): JsonResponse {
        try {
            $queries = $request->query->all();
            $entries = $entries = new DataObject\TrialClass\Listing();
            $entries->setUnpublished(true);
            $j = 0;

            //to set timezone manually

            $logger->setTimezone("Asia/Kolkata");

            foreach ($queries as $key => $value) {
                $j++;
                if ($key == "name") {
                    $entries->filterByName($value);
                } elseif ($key == "sku") {
                    $entries->filterBySku($value);
                } elseif ($key == "description") {
                    $entries->filterByDescription($value);
                } else {
                    $message = "Undefined field for filtering : " . $key;
                    $logger->warning($message);
                    return new JsonResponse([
                        "data" => [],
                        "count" => [$j, 0],
                        "message" => "Successful!",
                    ]);
                }
            }
            $i = 0;
            $data = [];
            foreach ($entries as $entry) {
                array_push($data, [
                    "name" => $entry->getName(),
                    "sku" => $entry->getSku(),
                    "description" => $entry->getDescription(),
                ]);
                $i++;
            }

            $logger->info("Data filtered successfully.");
            return new JsonResponse([
                "data" => $data,
                "count" => [$j, $i],
                "message" => "Successful!",
                "timezone" => $logger->timezone,
            ]);
        } catch (Exception $th) {
            $logger->error($th->getMessage());
            return new JsonResponse([
                "errorMessage" => $th->getMessage(),
            ]);
        }
    }

    //POST /filterPost
    public function filterPost(
        Request $request,
        ApplicationLoggerTZ $logger
    ): JsonResponse {
        try {
            $body = $request->toArray();
            $entries = new DataObject\TrialClass\Listing();
            $entries->setUnpublished(true);

            $logger->setTimezone("Asia/Manila");

            $j = 0;
            foreach ($body as $key => $value) {
                $j++;
                if ($key == "name") {
                    $entries->filterByName($value, "=");
                } elseif ($key == "sku") {
                    $entries->filterBySku($value, "=");
                } elseif ($key == "description") {
                    $entries->filterByDescription($value, "=");
                } else {
                    $message = "Undefined field for filtering : " . $key;
                    $logger->warning($message);
                    return new JsonResponse([
                        "data" => [],
                        "count" => [$j, 0],
                        "message" => "Successful!",
                    ]);
                }
            }
            $i = 0;
            $data = [];
            foreach ($entries as $entry) {
                $i++;
                array_push($data, [
                    "name" => $entry->getName(),
                    "sku" => $entry->getSku(),
                    "description" => $entry->getDescription(),
                ]);
            }

            $logger->info("Data filtered successfully.");
            return new JsonResponse([
                "data" => $data,
                "count" => [$j, $i],
                "message" => "Successful!",
                "timezone" => $logger->timezone,
            ]);
        } catch (Exception $th) {
            $logger->error($th->getMessage());
            return new JsonResponse([
                "errorMessage" => $th->getMessage(),
            ]);
        }
    }
}
