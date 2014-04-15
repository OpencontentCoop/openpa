<?php

include( 'autoload.php' );

$fileList = array();
eZDir::recursiveList( 'settings/siteaccess', 'settings/siteaccess', $fileList );
$siteaccess = array();
foreach( $fileList as $file )
{
    if ( $file['type'] == 'dir' && strpos( $file['name'], '_frontend' ) !== false )
    {
        $siteaccess[$file['name']] = $file['name'];
    }
}
ksort($siteaccess);

function wikiFormat( $index, $name, $opencontentUrl, $url, $isValidUrl, $time, $seo )
{
    $isValid = ( $isValidUrl == true ) ? '[[span(style=color: #FF0000, si )]]' : 'no';
    
    $seo = $seo != '' ? '{{{' . $seo . '}}}' : '?';
    $time = date( 'd/m/Y', $time );
    $string = "||";
    $string .= $index . "||";
    $string .= $name . "||";
    $string .= $isValidUrl ? "'''" . $url . "'''" : $url;
    $string .= "||";
    $string .= $isValidUrl ? $opencontentUrl : "'''" . $opencontentUrl . "'''";
    $string .= "||";
    $string .= $time . "||";
    $string .= $isValid . "||";
    $string .= $seo . "||";
    return $string;
}

function getIP( $url )
{
    $dns = dns_get_record( $url );    
    foreach( $dns as $dnsItem )
    {
        if ( isset( $dnsItem['type'] ) && $dnsItem['type'] == 'A' )
        {
            return $dnsItem['ip'];
        }
        elseif ( isset( $dnsItem['type'] ) && $dnsItem['type'] == 'CNAME' )
        {
            return getIP( $dnsItem['target'] );
        }
    }
}

function isValidUrl( $url )
{
    if ( stripos( $url, 'opencontent' ) === false )
    {    
        $url = rtrim( $url, '/' );
        $url = str_replace( 'http://', '', $url );
        $ip = getIP( $url );
        //eZCLI::instance()->output( $url . ' ' . $ip . ' ' . intval( $ip == ' 194.105.50.4' ));    
        return $ip == '194.105.50.4';    
    }
    return false;
}

$data = array();
foreach( $siteaccess as $sa )
{
    $ini = new eZINI( 'site.ini.append.php', 'settings/siteaccess/' . $sa );
    $openpaIni = new eZINI( 'openpa.ini.append.php', 'settings/siteaccess/' . $sa );
    $seoCode = $openpaIni->variable( 'Seo', 'GoogleAnalyticsAccountID' );
    $inputFile = 'settings/siteaccess/' . $sa . '/site.ini.append.php';    
    $opencontentUrl = 'http://' . str_replace( '_frontend', '', $sa ) . '.opencontent.it';
    $name = $ini->variable( 'SiteSettings', 'SiteName' );
    $url = 'http://' .  $ini->variable( 'SiteSettings', 'SiteURL' );
    $isValidUrl = isValidUrl( $ini->variable( 'SiteSettings', 'SiteURL' ) );    
    $data[$name] = array( 'name' => $name,
                          'ocurl' => $opencontentUrl,
                          'url' => $url,
                          'valid' => $isValidUrl,
                          'time' => filemtime( $inputFile ),
                          'seo' => $seoCode );    
}
ksort( $data );
$output1 = $output2 = array();
$index1 = $index2 = 1;
foreach( $data as $name => $item )
{
  if ( strpos( $name, 'Comune' ) === false )
  {
    $output1[] = wikiFormat( $index1, $item['name'], $item['ocurl'], $item['url'], $item['valid'], $item['time'], $item['seo'] );
    $index1++;
  }
  else
  {
    $output2[] = wikiFormat( $index2, $item['name'], $item['ocurl'], $item['url'], $item['valid'], $item['time'], $item['seo'] );
    $index2++;
  }  
}

$headers = "||=N=||=Ente=||=Dominio di produzione=||=Dominio di staging=||=Data=||=In prod=||=GoogleID=||";

eZCLI::instance()->output( $headers );
foreach( $output1 as $item )
{
  eZCLI::instance()->output( $item );  
}

eZCLI::instance()->output( $headers );
foreach( $output2 as $item )
{
  eZCLI::instance()->output( $item );  
}


?>
