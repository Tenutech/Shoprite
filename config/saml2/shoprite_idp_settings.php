<?php

// If you choose to use ENV vars to define these values, give this IdP its own env var names
// so you can define different values for each IdP, all starting with 'SAML2_'.$this_idp_env_id
$this_idp_env_id = 'SHOPRITE';

// This is a variable for simpleSAML example only.
// For a real IdP, you must set the URL values in the 'idp' config to conform to the IdP's real URLs.
$idp_host = env('SAML2_' . $this_idp_env_id . '_IDP_HOST', 'http://localhost:8000/simplesaml');

return $settings = array(

    /*****
     * OneLogin Settings
     */

    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted.
    // It will also reject messages that do not strictly follow the SAML
    // standard: Destination, NameId, Conditions, etc., are validated too.
    'strict' => env('SAML2_' . $this_idp_env_id . '_STRICT', true),

    // Enable debug mode (to print errors)
    'debug' => env('SAML2_' . $this_idp_env_id . '_DEBUG', false),

    // Service Provider (SP) Data that we are deploying
    'sp' => array(

        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Refer to lib/Saml2/Constants.php to see the supported NameIdFormat values.
        'NameIDFormat' => env('SAML2_' . $this_idp_env_id . '_NAMEIDFORMAT', 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'),

        // Usually, the x509 certificate and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters:
        'x509cert' => env('SAML2_' . $this_idp_env_id . '_SP_x509', ''),
        'privateKey' => env('SAML2_' . $this_idp_env_id . '_SP_PRIVATEKEY', ''),

        // Identifier (URI) of the SP entity.
        // Leave blank to use the '{idpName}_metadata' route, e.g., 'test_metadata'.
        'entityId' => env('SAML2_' . $this_idp_env_id . '_SP_ENTITYID', ''),

        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case, our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-POST binding.
            // Leave blank to use the '{idpName}_acs' route, e.g., 'test_acs'.
            'url' => env('SAML2_' . $this_idp_env_id . '_ASSERTIONCONSUMERSERVICE_URL', ''),
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case, our SP.
        // Remove this part to not include any URL Location in the metadata.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-Redirect binding.
            // Leave blank to use the '{idpName}_sls' route, e.g., 'test_sls'.
            'url' => env('SAML2_' . $this_idp_env_id . '_SINGLELOGOUTSERVICE_URL', ''),
        ),
    ),

    // Identity Provider (IdP) Data that we want to connect with our SP
    'idp' => array(
        // Identifier of the IdP entity (must be a URI)
        'entityId' => env('SAML2_' . $this_idp_env_id . '_IDP_ENTITYID', ''),
        // SSO endpoint info of the IdP (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_' . $this_idp_env_id . '_IDP_SSO_URL', ''),
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_' . $this_idp_env_id . '_IDP_SL_URL', ''),
        ),
        // Public x509 certificate of the IdP
        'x509cert' => env('SAML2_' . $this_idp_env_id . '_IDP_x509', ''),
        /*
         * Instead of using the entire x509 certificate, you can use a fingerprint
         * (use the command: openssl x509 -noout -fingerprint -in "idp.crt" to generate it)
         */
        // 'certFingerprint' => '',
    ),

    /***
     *
     * OneLogin advanced settings
     *
     */
    // Security settings
    'security' => array(

        /** Signatures and encryptions offered */

        // Indicates that the NameID of the <samlp:LogoutRequest> sent by this SP
        // will be encrypted.
        'nameIdEncrypted' => false,

        // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
        // will be signed. [The Metadata of the SP will offer this info]
        'authnRequestsSigned' => false,

        // Indicates whether the <samlp:LogoutRequest> messages sent by this SP
        // will be signed.
        'logoutRequestSigned' => false,

        // Indicates whether the <samlp:LogoutResponse> messages sent by this SP
        // will be signed.
        'logoutResponseSigned' => false,

        /* Sign the Metadata
         False || True (use sp certs) || array (
                                                    keyFileName => 'metadata.key',
                                                    certFileName => 'metadata.crt'
                                                )
        */
        'signMetadata' => false,


        /** Signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => false,

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed. [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => false,

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => false,

        // Authentication context.
        // Set to false and no AuthContext will be sent in the AuthNRequest,
        // Set true or don't present this parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
        'requestedAuthnContext' => true,
    ),

    // Contact information template, it is recommended to supply technical and support contacts
    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'Support',
            'emailAddress' => 'support@otbgroup.co.za'
        ),
        'support' => array(
            'givenName' => 'Support',
            'emailAddress' => 'support@otbgroup.co.za'
        ),
    ),

    // Organization information template, the info in en_US lang is recommended, add more if required
    'organization' => array(
        'en-US' => array(
            'name' => 'OTB Group',
            'displayname' => 'OTB Group',
            'url' => 'https://otbgroup.co.za'
        ),
    ),

    /* Interoperable SAML 2.0 Web Browser SSO Profile [saml2int] http://saml2int.org/profile/current

       'authnRequestsSigned' => false,    // SP SHOULD NOT sign the <samlp:AuthnRequest>,
                                          // MUST NOT assume that the IdP validates the sign
       'wantAssertionsSigned' => true,
       'wantAssertionsEncrypted' => true, // MUST be enabled if SSL/HTTPs is disabled
       'wantNameIdEncrypted' => false,
    */

);