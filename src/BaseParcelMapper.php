<?php

/**
 * The main mapping constant. Fields are converted to Gls specific
 * field names with the help of this class constant.
 */

return [
    'auth' => [
        [
            'getter' => 'getContractId',
            'xmlElement' => 'CodiceContrattoGls',
            'required' => true,
            'errorMessage' => 'Missing contract id.'
        ]
    ],

    'parcel' => [
        [
            'getter' => 'getName',
            'xmlElement' => 'RagioneSociale',
            'maxLength' => 35,
            'required' => true,
            'errorMessage' => 'Missing name.'
        ],
        [
            'getter' => 'getAddress',
            'xmlElement' => 'Indirizzo',
            'maxLength' => 35,
            'required' => true,
            'errorMessage' => 'Missing address.'
        ],
        [
            'getter' => 'getCity',
            'xmlElement' => 'Localita',
            'maxLength' => 30,
            'required' => true,
            'errorMessage' => 'Missing city.'
        ],
        [
            'getter' => 'getPostcode',
            'xmlElement' => 'Zipcode',
            'maxLength' => 5,
            'required' => true,
            'errorMessage' => 'Missing postcode.'
        ],
        [
            'getter' => 'getProvince',
            'xmlElement' => 'Provincia',
            'maxLength' => 2,
            'required' => true,
            'errorMessage' => 'Missing province.'
        ],
        [
            'getter' => 'getOrderId',
            'xmlElement' => 'Bda',
            'maxLength' => 11
        ],
        [
            'getter' => 'getOrderId',
            'xmlElement' => 'ContatoreProgressivo',
            'maxLength' => 11
        ],
        [
            'getter' => 'getNumOfPackages',
            'xmlElement' => 'Colli',
            'maxLength' => 5,
            'required' => true,
            'errorMessage' => 'Missing number of packages.'
        ],
        [
            'getter' => 'getIncoterm',
            'xmlElement' => 'Incoterm',
            'maxLength' => 2
        ],
        [
            'getter' => 'getPortType',
            'xmlElement' => 'TipoPorto',
            'maxLength' => 1
        ],
        [
            'getter' => 'getInsuranceAmount',
            'xmlElement' => 'Assicurazione',
            'maxLength' => 11
        ],
        [
            'getter' => 'getVolumeWeight',
            'xmlElement' => 'PesoVolume',
            'maxLength' => 11
        ],
        [
            'getter' => 'getCustomerReference',
            'xmlElement' => 'RiferimentoCliente',
            'maxLength' => 600
        ],
        [
            'getter' => 'getWeight',
            'xmlElement' => 'PesoReale',
            'maxLength' => 6,
            'required' => true,
            'errorMessage' => 'Missing weight.'
        ],
        [
            'getter' => 'getPaymentAmount',
            'xmlElement' => 'ImportoContrassegno',
            'maxLength' => 10
        ],
        [
            'getter' => 'getNoteOnLabel',
            'xmlElement' => 'NoteSpedizione',
            'maxLength' => 40
        ],
        [
            'getter' => 'getPdcNote',
            'xmlElement' => 'NoteAggiuntive',
            'maxLength' => 40
        ],
        [
            'getter' => 'getCustomerId',
            'xmlElement' => 'CodiceClienteDestinatario',
            'maxLength' => 30
        ],
        [
            'getter' => 'getPackageType',
            'xmlElement' => 'TipoCollo',
            'maxLength' => 1,
            'required' => true,
            'errorMessage' => 'Missing package type.'
        ],
        [
            'getter' => 'getEmail',
            'xmlElement' => 'Email',
            'maxLength' => 70
        ],
        [
            'getter' => 'getPrimaryMobilePhoneNumber',
            'xmlElement' => 'Cellulare1',
            'maxLength' => 10
        ],
        [
            'getter' => 'getSecondaryMobilePhoneNumber',
            'xmlElement' => 'Cellulare2',
            'maxLength' => 10
        ],
        [
            'getter' => 'getAdditionalServices',
            'xmlElement' => 'ServiziAccessori',
            'maxLength' => 50
        ],
        [
            'getter' => 'getPaymentMethod',
            'xmlElement' => 'ModalitaIncasso',
            'maxLength' => 4
        ],
        [
            'getter' => 'getDeliveryDate',
            'xmlElement' => 'DataPrenotazioneGDO',
            'maxLength' => 6
        ],
        [
            'getter' => 'getLabelFormat',
            'xmlElement' => 'FormatoPdf',
            'maxLength' => 2
        ],
        [
            'getter' => 'getIdentPin',
            'xmlElement' => 'IdentPIN',
            'maxLength' => 12
        ],
        [
            'getter' => 'getInsuranceType',
            'xmlElement' => 'AssicurazioneIntegrativa',
            'maxLength' => 1
        ],
        [
            'getter' => 'getAdditionalPrivacyText',
            'xmlElement' => 'InfoPrivacy',
            'maxLength' => 50
        ],
        [
            'getter' => 'getPickUpDelivery',
            'xmlElement' => 'FermoDeposito',
            'maxLength' => 1
        ],
        [
            'getter' => 'getPickUpPoint',
            'xmlElement' => 'SiglaSedeFermoDeposito',
            'maxLength' => 4
        ],
        [
            'getter' => 'getShipmentType',
            'xmlElement' => 'TipoSpedizione',
            'maxLength' => 1
        ],
        [
            'getter' => 'getReferencePersonName',
            'xmlElement' => 'PersonaRiferimento',
            'maxLength' => 50
        ],
        [
            'getter' => 'getReferencePersonPhoneNumber',
            'xmlElement' => 'TelefonoDestinatario',
            'maxLength' => 16
        ],
    ]
];