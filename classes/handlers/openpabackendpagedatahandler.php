<?php

class OpenpaBackendPageDataHandler implements OCPageDataHandlerInterface
{
    public function siteTitle()
    {
        return eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
    }

    public function siteUrl()
    {
        $currentSiteaccess = eZSiteAccess::current();
        $sitaccessIdentifier = $currentSiteaccess['name'];
        $path = "settings/siteaccess/{$sitaccessIdentifier}/";
        $ini = new eZINI( 'site.ini.append', $path, null, null, null, true, true );
        return rtrim( $ini->variable( 'SiteSettings', 'SiteURL' ), '/' );
    }

    public function assetUrl()
    {
        $siteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $siteUrl );
        if ( count( $parts ) >= 2 )
        {
            array_pop( $parts );
            $siteUrl = implode( '/', $parts );
        }
        return rtrim( $siteUrl, '/' );
    }

    public function logoPath()
    {
        $data = false;
        $rootNode = OpenPaFunctionCollection::fetchHome();
        if ( $rootNode instanceof eZContentObjectTreeNode  ) {
            $root = $rootNode->attribute('object');
            if ($root instanceof eZContentObject) {
                $rootHandler = OpenPAObjectHandler::instanceFromContentObject($root);
                if ($rootHandler->hasAttribute('logo')) {
                    $attribute = $rootHandler->attribute('logo')->attribute('contentobject_attribute');
                    if ($attribute instanceof eZContentObjectAttribute && $attribute->hasContent()) {
                        /** @var eZImageAliasHandler $content */
                        $content = $attribute->content();
                        $original = $content->attribute('original');
                        $data = $original['full_path'];
                    } else {
                        $data = '';
                    }
                }
            }
        }
        return $data;
    }

    public function logoTitle()
    {
        return $this->siteTitle();
    }

    public function logoSubtitle()
    {
        return $this->siteTitle();
    }

    public function headImages()
    {
        return OpenPaFunctionCollection::fetchHeaderImage();
    }

    public function needLogin()
    {
        // TODO: Implement needLogin() method.
    }

    public function attributeContacts()
    {
        $data = array();
        $homePage = OpenPaFunctionCollection::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode  )
        {
            $homeObject = $homePage->attribute( 'object' );
            if ( $homeObject instanceof eZContentObject )
            {
                $dataMap = $homeObject->attribute( 'data_map' );
                if ( isset( $dataMap['contacts'] )
                    && $dataMap['contacts'] instanceof eZContentObjectAttribute
                    && $dataMap['contacts']->attribute( 'data_type_string' ) == 'ezmatrix'
                    && $dataMap['contacts']->attribute( 'has_content' ) )
                {
                    $trans = eZCharTransform::instance();
                    $matrix = $dataMap['contacts']->attribute( 'content' )->attribute( 'matrix' );
                    foreach( $matrix['rows']['sequential'] as $row )
                    {
                        $columns = $row['columns'];
                        $name = $columns[0];
                        $identifier = $trans->transformByGroup( $name, 'identifier' );
                        if ( !empty( $columns[1] ) )
                        {
                            $data[$identifier] = $columns[1];
                        }
                    }
                }
                else
                {
                    if( isset( $dataMap['facebook'] )
                        && $dataMap['facebook'] instanceof eZContentObjectAttribute
                        && $dataMap['facebook']->attribute( 'has_content' ) )
                    {
                        $data['facebook'] = $dataMap['facebook']->toString();
                    }
                    if( isset( $dataMap['twitter'] )
                        && $dataMap['twitter'] instanceof eZContentObjectAttribute
                        && $dataMap['twitter']->attribute( 'has_content' ) )
                    {
                        $data['twitter'] = $dataMap['twitter']->toString();
                    }
                }
            }
        }
        return $data;
    }

    public function attributeFooter()
    {
        return OpenPaFunctionCollection::fetchFooterNotes();
    }

    public function textCredits()
    {
        // TODO: Implement textCredits() method.
    }

    public function googleAnalyticsId()
    {
        return OpenPAINI::variable( 'Seo', 'GoogleAnalyticsAccountID', false );
    }

    public function cookieLawUrl()
    {
        // TODO: Implement cookieLawUrl() method.
    }

    public function menu()
    {
        // TODO: Implement menu() method.
    }

    public function userMenu()
    {
        // TODO: Implement userMenu() method.
    }

    public function bannerPath()
    {
        $data = false;
        $rootNode = OpenPaFunctionCollection::fetchHome();
        if ( $rootNode instanceof eZContentObjectTreeNode  )
        {
            $root = $rootNode->attribute('object');
            if ($root instanceof eZContentObject)
            {
                $rootHandler = OpenPAObjectHandler::instanceFromContentObject($root);
                if ($rootHandler->hasAttribute('image')) {
                    $attribute = $rootHandler->attribute('image')->attribute('contentobject_attribute');
                    if ($attribute instanceof eZContentObjectAttribute && $attribute->hasContent()) {
                        /** @var eZImageAliasHandler $content */
                        $content = $attribute->content();
                        $original = $content->attribute('original');
                        $data = $original['full_path'];
                    }
                }
            }
        }
        return $data;
    }

    public function bannerTitle()
    {
        return $this->siteTitle();
    }

    public function bannerSubtitle()
    {
        return $this->siteTitle();
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getAttributeString( $identifier )
    {
        $data = '';
        $rootNode = OpenPaFunctionCollection::fetchHome();
        if ( $rootNode instanceof eZContentObjectTreeNode  )
        {
            $root = $rootNode->attribute('object');
            if ($root instanceof eZContentObject)
            {
                $rootHandler = OpenPAObjectHandler::instanceFromContentObject($root);
                if ($rootHandler->hasAttribute($identifier)) {
                    $attribute = $rootHandler->attribute($identifier)->attribute('contentobject_attribute');
                    if ($attribute instanceof eZContentObjectAttribute) {
                        $data = static::replaceBracket($attribute->toString());
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Replace [ ] with strong html tag
     * @param string $string
     * @return string
     */
    public static function replaceBracket( $string )
    {
        $string = str_replace( '[', '<strong>', $string );
        $string = str_replace( ']', '</strong>', $string );
        return $string;
    }

}