{set-block scope=root variable=subject}{'Benvenuto in %1'|i18n('openpa/mail/registration',,array(ezini( 'SiteSettings', 'SiteName' )))}{/set-block}
<table border='0' cellpadding='30' cellspacing='0' style='margin-left: auto;margin-right: auto;width:600px;text-align:center;' width='600'>
    <tr>
        <td align='left' style='background: #ffffff; border: 1px solid #dce1e5;' valign='top' width=''>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='center' valign='top'>
                        <h2>{'Grazie di esserti iscritto!'|i18n('openpa/mail/registration')}</h2>
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='top'>
                        <h4 style='color: #f90f00 !important'>{'Ecco le informazione del tuo account SensorCivico'|i18n('openpa/mail/registration')}</h4>
                    </td>
                </tr>
                <tr>
                    <td align='center' style='border-top: 1px solid #dce1e5;border-bottom: 1px solid #dce1e5;' valign='top'>
                        <p>
                            <strong>{'Nome'|i18n('openpa/mail/registration')}:</strong>
                            {$user.contentobject.name|wash()}
                        </p>
                        <p>
                            <strong>{'Indirizzo email'|i18n('openpa/mail/registration')}:</strong>
                            {$user.email|wash()}
                        </p>
                    </td>
                </tr>                
                <tr>
                    <td align='center' valign='top'>
                        <p>                            
                            {'Se desideri cambiare le impostazioni del tuo profilo clicca %profile_link_start%qui%profile_link_end%'|i18n('openpa/mail/registration',, hash( '%profile_link_start%', concat( '<a href=http://', ezini( 'SiteSettings', 'SiteURL' ), '/user/edit/>' ), '%profile_link_end%', '</a>' ))}<br />                            
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>