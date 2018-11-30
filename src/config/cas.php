<?php
return [
    /*
    |--------------------------------------------------------------------------
    | CAS Hostname
    |--------------------------------------------------------------------------
    | Example: 'cas.myuniv.edu'.
    */
    'cas_hostname'        => env('CAS_HOSTNAME', 'cas.myuniv.edu'),

    /*
    |--------------------------------------------------------------------------
    | CAS Authorized Hosts
    |--------------------------------------------------------------------------
    | Example: 'cas.myuniv.edu'.  This is used when SAML is active and is
    | recommended for protecting against DOS attacks.  If using load
    | balanced hosts, then separate each with a comma.
    */
    'cas_real_hosts'      => env('CAS_REAL_HOSTS', 'cas.myuniv.edu'),


    /*
    |--------------------------------------------------------------------------
    | Customize CAS Session Cookie Name
    |--------------------------------------------------------------------------
    */
    'cas_session_name'    => env('CAS_SESSION_NAME', 'CASAuth'),

    /*
    |--------------------------------------------------------------------------
    | Laravel has it's own authentication sessions. Unless you want phpCAS
    | to manage the session, leave this set to false.  Note that the
    | middleware and redirect classes will be handling removal
    | of the Laravel sessions when this is set to false.
    |--------------------------------------------------------------------------
    */
    'cas_control_session' => env('CAS_CONTROL_SESSIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Enable using this as a cas proxy
    |--------------------------------------------------------------------------
    */
    'cas_proxy'           => env('CAS_PROXY', false),

    /*
    |--------------------------------------------------------------------------
    | Cas Port
    |--------------------------------------------------------------------------
    | Usually 443
    */
    'cas_port'            => env('CAS_PORT', 443),

    /*
    |--------------------------------------------------------------------------
    | CAS URI
    |--------------------------------------------------------------------------
    | Sometimes is /cas
    */
    'cas_uri'             => env('CAS_URI', '/cas'),

    /*
    |--------------------------------------------------------------------------
    | CAS Validation
    |--------------------------------------------------------------------------
    | CAS server SSL validation: 'self' for self-signed certificate, 'ca' for
    | certificate from a CA, empty for no SSL validation.
    |
    | VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL
    */
    'cas_validation'          => env('CAS_VALIDATION', ''),

    /*
    |--------------------------------------------------------------------------
    | CA Certificate
    |--------------------------------------------------------------------------
    | Path to the CA certificate file.  For production use set
    | the CA certificate that is the issuer of the cert
    */
    'cas_cert'                => env('CAS_CERT', ''),

    /*
    |--------------------------------------------------------------------------
    | CN Validation (if you are using CA certs)
    |--------------------------------------------------------------------------
    | If for some reason you want to disable validating the certificate
    | intermediaries, here is where you can.  Recommended to leave
    | this set with default (true).
    */
    'cas_validate_cn'     => env('CAS_VALIDATE_CN', true),

    /*
    |--------------------------------------------------------------------------
    | CAS Login URI
    |--------------------------------------------------------------------------
    | Empty is fine
    */
    'cas_login_url'       => env('CAS_LOGIN_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | CAS Logout URI
    |--------------------------------------------------------------------------
    */
    'cas_logout_url'      => env('CAS_LOGOUT_URL', 'https://cas.myuniv.edu/cas/logout'),

    /*
    |--------------------------------------------------------------------------
    | CAS Logout Redirect Services
    |--------------------------------------------------------------------------
    | If your server supports redirection services, enter the redirect url
    | in this section.  If left blank, it will default to disabled.
    */
    'cas_logout_redirect' => env('CAS_LOGOUT_REDIRECT', ''),

    /*
    |--------------------------------------------------------------------------
    | CAS Logout Redirect URL
    |--------------------------------------------------------------------------
    | CAS can display a link on the logout page, enter the url in this section.
    | If left blank, it will default to disabled.
    */
    'cas_logout_redirect_url' => env('CAS_LOGOUT_REDIRECT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | CAS Post-login Redirect
    |--------------------------------------------------------------------------
    | After a successful login, this is where the CAS package will redirect
    | users if there is not an intended URL. Typically the logged-in home
    | page of the application.
    */
    'cas_login_redirect_url'    => env('CAS_LOGIN_REDIRECT_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | CAS Base Service URL
    |--------------------------------------------------------------------------
    | This is the base service URL required by v1.6.0+ of the phpCAS library.
    */
    'cas_base_service_url'   => config('app.url'),

    /*
    |--------------------------------------------------------------------------
    | CAS Redirect Path
    |--------------------------------------------------------------------------
    | This is the URL that CAS will return the user to after successful
    | authentication. This should be the /login route which will handle
    | the intended redirection or redirect to home.
    */
    'cas_redirect_path'   => env('CAS_REDIRECT_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | CAS Supports SAML 1.1, allowing you to retrieve more than just the
    | user identifier.  If your CAS authentication service supports
    | this feature, you may be able to retrieve user meta data.
    |--------------------------------------------------------------------------
    */
    'cas_enable_saml'     => env('CAS_ENABLE_SAML', true),

    /*
    |--------------------------------------------------------------------------
    | CAS will support version 1.0, 2.0, 3.0 of the protocol.  It is recommended
    | to use version 2.0, 3.0, or SAML 1.1.  If you enable SAML, then that
    | will override this configuration.
    |--------------------------------------------------------------------------
    */
    'cas_version'         => env('CAS_VERSION', "2.0"),
    
    /*
    |--------------------------------------------------------------------------
    | Enable PHPCas Debug Mode
    | Options are:
    | 1) true (log file name is set below, default path is storage/logs)
    | 2) false (no logging occurs)
    |--------------------------------------------------------------------------
    */
    'cas_debug'           => env('CAS_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | CAS Log Path
    |
    | When CAS_DEBUG is enabled, this path will be used for the CAS log.
    |--------------------------------------------------------------------------
    */
    'cas_log_path'           => env('CAS_LOG_PATH', storage_path('logs/')),

    /*
    |--------------------------------------------------------------------------
    | CAS Log Filename
    |
    | When CAS_DEBUG is enabled, this filename will be used for the CAS log.
    |--------------------------------------------------------------------------
    */
    'cas_log_name'           => env('CAS_LOG_NAME', 'cas.log'),

    /*
    |--------------------------------------------------------------------------
    | Enable Verbose error messages. Not recommended for production.
    | true | false
    |--------------------------------------------------------------------------
    */
    'cas_verbose_errors'  => env('CAS_VERBOSE_ERRORS', false),

    /*
    |--------------------------------------------------------------------------
    | This will cause CAS to skip authentication and assume this user id.
    | This should only be used for developmental purposes.  getAttributes()
    | will return null in this condition.
     */
    'cas_masquerade'      => env('CAS_MASQUERADE', '')
];
