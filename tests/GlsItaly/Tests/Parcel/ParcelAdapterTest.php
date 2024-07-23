<?php

namespace GlsItaly\Tests;

use MarkoSirec\GlsItaly\SDK\Exceptions\CloseParcelException;
use MarkoSirec\GlsItaly\SDK\Exceptions\DeleteParcelException;
use MarkoSirec\GlsItaly\SDK\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use MarkoSirec\GlsItaly\SDK\Adapters\ParcelAdapter as ParcelAdapter;
use MarkoSirec\GlsItaly\SDK\Models\Parcel as Parcel;
use MarkoSirec\GlsItaly\SDK\Models\Auth as Auth;
use MarkoSirec\GlsItaly\SDK\Adapters\RequestData as RequestData;

class ParcelAdapterTest extends \PHPUnit\Framework\TestCase
{

    public function testMissingDataValidation(): void
    {
        $parcel = new Parcel();
        $parcelAdapter = new ParcelAdapter($this->getAuth(), $parcel);

        $this->expectException(ValidationException::class);
        $this->assertTrue((bool)$parcelAdapter->get());
    }

    public function testMissingNameValidation(): void
    {
        $parcel = new Parcel();

        $parcel->setAddress('Test street, 191');
        $parcel->setCity('SOS ALINOS');
        $parcel->setPostcode('08028');
        $parcel->setProvince('NU');

        $parcelAdapter = new ParcelAdapter($this->getAuth(), $parcel);

        $this->expectException(ValidationException::class);
        $this->assertTrue((bool)$parcelAdapter->get());
    }

    public function testValidationSuccess(): void
    {
        $parcel = new Parcel();

        $parcel->setName('John Smith');
        $parcel->setAddress('Test street, 191');
        $parcel->setCity('SOS ALINOS');
        $parcel->setPostcode('08028');
        $parcel->setProvince('NU');
        $parcel->setWeight('2,7');

        $parcelAdapter = new ParcelAdapter($this->getAuth(), $parcel);
        $this->assertInstanceOf(RequestData::class, $parcelAdapter->get());
    }

    public function testConvertStatus(): void
    {
        $this->assertEquals(
            'waiting',
            ParcelAdapter::convertStatus('IN ATTESA DI CHIUSURA.')
        );

        $this->assertEquals(
            'closed',
            ParcelAdapter::convertStatus('CHIUSA.')
        );
    }

    public function testParseList(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <ListParcel>
              <Parcel>
                <Data>25/06/2019 15:43</Data>
                <NumSpedizione>590734654</NumSpedizione>
                <RiferimentiCliente />
                <Ddt>1234564</Ddt>
                <DenominazioneDestinatario>John Smith</DenominazioneDestinatario>
                <CittaDestinatario>SOS ALINOS</CittaDestinatario>
                <ProvinciaDestinatario>NU</ProvinciaDestinatario>
                <IndirizzoDestinatario>Via su vrangone, 191</IndirizzoDestinatario>
                <TotaleColli>2</TotaleColli>
                <PesoSpedizione>5,4</PesoSpedizione>
                <StatoSpedizione>IN ATTESA DI CHIUSURA.</StatoSpedizione>
              </Parcel>
            </ListParcel>';

        $this->assertCount(
            1,
            ParcelAdapter::parseListResponse($xml)
        );
    }

    public function testParseAddResponseMissingParcelNumber(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <InfoLabel>
                <Parcel>
                </Parcel>
            </InfoLabel>';

        $response = ParcelAdapter::parseAddResponse($xml);

        $this->assertEquals(
            'Unknown error. The parcel id was not returned.',
            $response[0]->getError()
        );
    }

    public function testParseAddResponseErrorParcelNumber(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <InfoLabel>
                <Parcel>
                    <NumeroSpedizione>999999999</NumeroSpedizione>
                </Parcel>
            </InfoLabel>';

        $response = ParcelAdapter::parseAddResponse($xml);

        $this->assertEquals(
            'Please make sure you defined all the parcel parameters correctly.',
            $response[0]->getError()
        );
    }

    public function testParseAddResponseGetParcelId(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <InfoLabel>
                <Parcel>
                    <NumeroSpedizione>123</NumeroSpedizione>
                </Parcel>
            </InfoLabel>';

        $response = ParcelAdapter::parseAddResponse($xml);

        $this->assertEquals(
            123,
            $response[0]->getParcelId()
        );
    }

    public function testParseAddResponseGetPdf(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <InfoLabel>
                <Parcel>
                    <NumeroSpedizione>123</NumeroSpedizione>
                    <PdfLabel>foo</PdfLabel>
                </Parcel>
            </InfoLabel>';

        $response = ParcelAdapter::parseAddResponse($xml);

        $this->assertEquals(
            'foo',
            $response[0]->getPdfLabel()
        );
    }

    public function testParseCloseResponseError(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <DescrizioneErrore>foo</DescrizioneErrore>';

        $this->expectException(CloseParcelException::class);
        $this->assertTrue(
            ParcelAdapter::parseCloseResponse($xml)
        );
    }

    public function testParseCloseResponse(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <DescrizioneErrore>OK</DescrizioneErrore>';

        $this->assertEquals(
            true,
            ParcelAdapter::parseCloseResponse($xml)
        );
    }

    public function testParseDeleteResponse(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <DescrizioneErrore>Eliminazione della spedizione 123 avvenuta.</DescrizioneErrore>';

        $this->assertTrue(
            ParcelAdapter::parseDeleteResponse($xml, 123)
        );
    }


    public function testParseDeleteResponseError(): void
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
            <DescrizioneErrore>Spedizione 123 non presente.</DescrizioneErrore>';

        $this->expectException(DeleteParcelException::class);
        $this->assertTrue(
            ParcelAdapter::parseDeleteResponse($xml, 123)
        );
    }

    public function testDifferentMapping(): void
    {
        $parcel = new Parcel();

        $name = 'i have to many characters in the name property';
        $parcel->setName($name);
        $parcel->setAddress('Test street, 191');
        $parcel->setCity('SOS ALINOS');
        $parcel->setPostcode('08028');
        $parcel->setProvince('NU');
        $parcel->setWeight('2,7');

        $parcelAdapter = new ParcelAdapter($this->getAuth(), $parcel);

        $customMapping = require ('CustomParcelMapping.php');
        $mapping = array_merge($parcelAdapter->getCurrentMapping(), $customMapping);

        $parcelAdapter->setCurrentMapping($mapping);

        $result = $parcelAdapter->get();
        $this->assertEquals(strlen($name), strlen($result->RagioneSociale));
    }

    private function getAuth(): Auth
    {
        $auth = new Auth();
        $auth->setBranchId('1');
        $auth->setClientId('1');
        $auth->setPassword('1');
        $auth->setContractId('1');
        return $auth;
    }
}