{if and( is_set( $survey_validation ), is_set($survey_validation.error), is_set($survey_validation.warning), $survey_validation.error|eq( false() ), $survey_validation.warning|eq( false() ) )}
    <div class="message-feedback alert alert-success">
        <h2>Il questionario è stato inviato.</h2>
        <p>La ringraziamo peer la gentile collaborazione.</p>
    </div>
{/if}