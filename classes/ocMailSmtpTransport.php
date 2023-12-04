<?php

class ocMailSmtpTransport extends ezcMailSmtpTransport
{
    protected function startTls()
    {
        // start TLS authentication process
        $this->sendData('STARTTLS');
        if ( $this->getReplyCode( $response ) !== '220' )
        {
            throw new ezcMailTransportSmtpException( "STARTTLS failed with error: {$response}." );
        }

        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        // Fix for PHP >=5.6.7 and <7.2 not including TLS 1.1 and 1.2
        if(defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT'))
        {
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }

        // setup the current connection for TLS
        if ( !stream_socket_enable_crypto($this->connection, true, $crypto_method) )
        {
            throw new ezcMailTransportSmtpException( "Error enabling TLS on existing SMTP connection." );
        }

    }

}