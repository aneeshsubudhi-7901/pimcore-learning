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
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Common\SymmetricKey;

class ConsumeEncryptController extends AbstractController
{
    //GET /filterGet
    public function filterGetEnc(
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
    public function filterPostEnc(
        Request $request,
        ApplicationLoggerTZ $logger
    ): Response {
        //SETTING REQUEST CIPHER
        $requestCipher = new AES("ecb");
        $requestKey = "1a2b3c4d5e6f7g8h";
        $requestCipher->setKey($requestKey);

        //SETTING RESPONSE CIPHER
        $responseKey = "1h2g3f4e5d6c7b8a";
        $responseCipher = new AES("ecb");
        $responseCipher->setKey($responseKey);

        try {
            // $body = $request->toArray();
            $entries = new DataObject\TrialClass\Listing();
            $entries->setUnpublished(true);

            $logger->setTimezone("Asia/Manila");

            //DECRYPTING REQUEST
            //decrypting the value of property data in the request json, decrypting it will give us the intended json, which we will convert to associative array using json_decode
            $encryptedReq = $request->getContent(); //which is in base64(expected)
            $reqBase64Decode = base64_decode($encryptedReq); //converts normal string that is needed for decryption
            $decryptedJSON = $requestCipher->decrypt($reqBase64Decode);
            $reqBody = json_decode($decryptedJSON);

            $j = 0;
            foreach ($reqBody as $key => $value) {
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

            //SENDING ENCRYPTED RESPONSE
            $responseBody = [
                "data" => $data,
                "count" => [$j, $i],
                "message" => "Successful!",
                "timezone" => $logger->timezone,
            ];
            //converting the $responseBody to JSON string
            $responseJSON = json_encode($responseBody);
            $responseCipherText = $responseCipher->encrypt($responseJSON);
            $responseBase64CT = base64_encode($responseCipherText);
            return new Response($responseBase64CT);

            //SENDING UNENCRYPTED RESPONSE

            // return new JsonResponse([
            //     "data" => $data,
            //     "count" => [$j, $i],
            //     "message" => "Successful!",
            //     "timezone" => $logger->timezone,
            // ]);
        } catch (Exception $th) {
            $logger->error($th->getMessage());

            //SENDING ENCRYPTED ERROR RESPONSE
            $errorBody = [
                "errorMessage" => $th->getMessage(),
            ];

            $errorJSON = json_encode($errorBody);
            $errorCipherText = $responseCipher->encrypt($errorJSON);
            $errorBase64CT = base64_encode($errorCipherText);
            return new Response($errorBase64CT);

            //SENDING UNECNRYPTED ERROR RESPONSE

            // return new JsonResponse([
            //     "errorMessage" => $th->getMessage()
            // ]);
        }
    }
}
