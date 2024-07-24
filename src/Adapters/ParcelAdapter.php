<?php

namespace MarkoSirec\GlsItaly\SDK\Adapters;

use Exception;
use MarkoSirec\GlsItaly\SDK\Models\Auth as Auth;
use MarkoSirec\GlsItaly\SDK\Models\Parcel as Parcel;

use MarkoSirec\GlsItaly\SDK\Exceptions\ValidationException as ValidationException;
use MarkoSirec\GlsItaly\SDK\Exceptions\DeleteParcelException as DeleteParcelException;
use MarkoSirec\GlsItaly\SDK\Exceptions\AddParcelException as AddParcelException;
use MarkoSirec\GlsItaly\SDK\Exceptions\CloseParcelException as CloseParcelException;

use MarkoSirec\GlsItaly\SDK\Responses\AddParcelResponse as AddParcelResponse;
use SimpleXMLElement;

/**
 * Author: Marko Sirec [m.sirec@gmail.com]
 * Authors-Website: https://github.com/markosirec
 * Date: 27.06.2019
 * Version: 1.0.0
 *
 * Notes: Adapter which transforms a parcel to the format specified by GLS Italy
 */

/**
 * Class ParcelAdapter
 *
 * @package MarkoSirec\GlsItaly\SDK
 */
final class ParcelAdapter extends BaseAdapter
{
    private Parcel $parcel;
    private Auth $auth;
    private array $currentMapping;

    const PARCEL_STATUS_MAPPING = [
        'IN ATTESA DI CHIUSURA.' => 'waiting',
        'CHIUSA.' => 'closed'
    ];

    /**
     * @var array|null
     */
    private $parcelMapping;

    /**
     * Class construct
     * @param Auth   $auth   An instance of the Auth model
     * @param Parcel $parcel An instance of the Parcel model
     */
    public function __construct(Auth $auth, Parcel $parcel, array $parcelMapping = null)
    {
        $this->parcel = $parcel;
        $this->auth = $auth;

        if (!empty($parcelMapping)) {
            $this->currentMapping = $parcelMapping;
        } else {
            $basicMapper = include(dirname(__FILE__, 2) . '/BaseParcelMapper.php');
            $this->currentMapping = $basicMapper;
        }
    }

    /**
     * Transforms the parcel object properties into a format specified by Gls
     * @throws ValidationException in case required params are missing
     * @return RequestData         Parcel data in a format Gls can understand
     */
    public function get(): RequestData
    {
        $requestData = new RequestData();

        foreach ($this->currentMapping as $object => $data) {
            foreach ($data as $properties) {
                $value = $this->{$object}->{$properties['getter']}();

                if ($value !== null) {
                    if (isset($properties['maxLength'])) {
                        $requestData->{$properties['xmlElement']} = $this->formatStringForXml($value, $properties['maxLength']);
                    } else {
                        $requestData->{$properties['xmlElement']} = $value;
                    }
                } elseif (isset($properties['required']) && $properties['required'] === true) {
                    throw new ValidationException($properties['errorMessage']);
                }
            }
        }

        // automatically generate the PDF label upon request
        $requestData->GeneraPdf = 4;

        return $requestData;
    }

    /**
     * Maps the Gls status (in italian) to our status
     * @param  string $string Gls status
     * @return string         Our status
     */
    public static function convertStatus(string $string): string
    {
        if (isset(ParcelAdapter::PARCEL_STATUS_MAPPING[$string])) {
            return ParcelAdapter::PARCEL_STATUS_MAPPING[$string];
        }

        return $string;
    }

    /**
     * Parses the delete response from Gls
     * @param  string $response      The original xml response as a string.
     * @param  int    $parcelId      The id of the parcel to delete.
     * @return bool                  True on success
     * @throws Exception             If the XML data could not be parsed.
     * @throws DeleteParcelException If the parcel can't be found.
     */
    public static function parseDeleteResponse(string $response, int $parcelId): bool
    {
        $response = new SimpleXMLElement($response);
        $response = (string)$response[0];

        if ($response == 'Spedizione ' . $parcelId . ' non presente.') {
            $error = 'Can\'t find parcel ' . $parcelId;
        }

        if (isset($error)) {
            throw new DeleteParcelException($error);
        }

        return true;
    }

    /**
     * Parses the response when trying to list parcels
     * @param string $result  The raw xml response from Gls.
     * @return array          List of parcels.
     * @throws Exception      If the XML data could not be parsed.
     */
    public static function parseListResponse(string $result): array
    {
        $result = new SimpleXMLElement($result);

        if (!isset($result->Parcel)) {
            return [];
        }

        $parcels = [];

        foreach ($result->Parcel as $pr) {
            $parcels[] = ParcelAdapter::parseListParcel($pr);
        }

        return $parcels;
    }

    /**
     * Parses the individual parcel from Gls
     * @param  SimpleXMLElement $pr The parcel XML element
     * @return Parcel                Instance of the parcel object
     */
    public static function parseListParcel(SimpleXMLElement $pr): Parcel
    {
        $parcel = new Parcel();
        $parcel->setStatus(ParcelAdapter::convertStatus((string)$pr->StatoSpedizione));
        $parcel->setParcelId((string)$pr->NumSpedizione);
        $parcel->setOrderId((int)$pr->Ddt);
        $parcel->setName((string)$pr->DenominazioneDestinatario);
        $parcel->setCity((string)$pr->CittaDestinatario);
        $parcel->setProvince((string)$pr->ProvinciaDestinatario);
        $parcel->setAddress((string)$pr->IndirizzoDestinatario);
        $parcel->setNumOfPackages((int)$pr->TotaleColli);

        return $parcel;
    }

    /**
     * Parses the Gls response when adding a parcel
     * @param  string $response  The raw response
     * @throws AddParcelException if Gls returns an error
     * @return array Array of AddParcelResponse response objects carrying the parcel id and pdf label
     */
    public static function parseAddResponse(string $response): array
    {
        try {
            $xmlResponse = new SimpleXMLElement($response);
        } catch (Exception $e) {
            $exception = new AddParcelException('GLS IT returned non-xml response.');
            $exception->setResponse($response);
            throw $exception;
        }

        $responseObjects = [];

        foreach ($xmlResponse->Parcel as $parcel) {
            $response = new AddParcelResponse();

            if (!isset($parcel->NumeroSpedizione)) {
                $response->setError('Unknown error. The parcel id was not returned.');
            } elseif ($parcel->NumeroSpedizione == '999999999') {
                $response->setError('Please make sure you defined all the parcel parameters correctly.');
            } else {
                $response->setParcelId((int)$parcel->NumeroSpedizione);
            }

            $response->setPdfLabel((string)$parcel->PdfLabel);
            $response->setZplLabel((string)$parcel->Zpl);
            $response->setSenderName((string)$parcel->DenominazioneMittente);
            $response->setVolumeWeight((string)$parcel->RapportoPesoVolume);
            $response->setShippingDate((string)$parcel->DataSpedizione);
            $response->setGlsDestination((string)$parcel->DescrizioneSedeDestino);
            $response->setCSM((string)$parcel->SiglaCSM);
            $response->setAreaCode((string)$parcel->CodiceZona);
            $response->setInfoPrivacy((string)$parcel->InfoPrivacy);
            $response->setReceiverName((string)$parcel->DenominazioneDestinatario);
            $response->setAddress((string)$parcel->IndirizzoDestinatario);
            $response->setCity((string)$parcel->CittaDestinatario);
            $response->setProvince((string)$parcel->ProvinciaDestinatario);
            $response->setDescription1((string)$parcel->DescrizioneCSM1);
            $response->setDescription2((string)$parcel->DescrizioneCSM2);
            $response->setShippingWeight((string)$parcel->PesoSpedizione);
            $response->setShippingNotes((string)$parcel->NoteSpedizione);
            $response->setTransportType((string)$parcel->DescrizioneTipoPorto);
            $response->setSenderInitials((string)$parcel->SiglaMittente);
            $response->setProgressiveParcel((string)$parcel->ProgressivoCollo);
            $response->setParcelType((string)$parcel->TipoCollo);
            $response->setGlsDestinationAbbr((string)$parcel->SiglaSedeDestino);
            $response->setPrinter((string)$parcel->Sprinter);
            $response->setBda((string)$parcel->Bda);
            $response->setTotalPackages((int)$parcel->TotaleColli);
            $responseObjects[] = $response;
        }

        return $responseObjects;
    }

    /**
     * Parses the Gls response when closing a parcel
     * @param  string $response     Raw Gls response
     * @throws CloseParcelException if Gls returns an error
     * @return bool                 Success on true
     */
    public static function parseCloseResponse(string $response): bool
    {
        try {
            $xmlResponse = new SimpleXMLElement($response);
        } catch (Exception $e) {
            $exception = new CloseParcelException('GLS IT returned non-xml response.');
            $exception->setResponse($response);
            throw $exception;
        }

        if ((string)$xmlResponse[0] == 'OK') {
            return true;
        }

        $exception = new CloseParcelException('Please make sure you defined a' .
            'll the parcel parameters correctly. To get the response xml, please call the method getXmlResponse() on ' .
            ' the exception object.'
        );

        $exception->setResponse($response);
        $exception->setXmlResponse($xmlResponse);

        throw $exception;
    }

    public function getCurrentMapping(): array
    {
        return $this->currentMapping;
    }

    public function setCurrentMapping(array $currentMapping): void
    {
        $this->currentMapping = $currentMapping;
    }
}
