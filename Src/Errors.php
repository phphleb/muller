<?php


namespace Phphleb\Muller\Src;


class Errors
{
    const ERROR_NAME_FORMAT = 'Ungültiges Benutzernamenformat: [%s]';

    const ERROR_VALIDATE_NAME = 'Ungültiger Benutzername: [%s]';

    const ERROR_VALIDATE_EMAIL = 'E-Mail-Adresse [%s] wurde nicht validiert';

    const ERROR_SEND_EMAIL = 'E-Mail konnte nicht gesendet werden';

    const ERROR_SAVE_EMAIL_LOG = 'Fehler beim Speichern des Fehlerprotokolls in der Datei.';

    const ERROR_EMAIL_ADDRESS_NOT_SPECIFIED = 'Die E-Mail-Adresse des Empfängers ist nicht festgelegt';

    const ERROR_MULTIPLE_RECIPIENTS_ARE_PROHIBITED = 'Das Senden an mehrere Empfänger ist verboten';

    const ERROR_USER_NAME_MISSING_OR_NOT_VALIDATED = 'Benutzername fehlt oder Validierung fehlgeschlagen';

    const ERROR_EMAIL_MISSING_OR_NOT_VALIDATED = 'Die E-Mail des Absenders fehlt oder wurde nicht validiert';

    const ERROR_CONTENT_MISSING = 'Fehlender E-Mail-Inhalt';

    const ERROR_TITLE_MISSING = 'Fehlender E-Mail-Header';

    const ERROR_HEADERS_MISSING = 'Ungültiger Header übergeben';

    const ERROR_WRONG_CONDITION_TO_SAVE = 'Ungültige Bedingung zum Speichern einer Nachricht in einer Datei und zum Speichern nur in einer Datei';

    const ERROR_LACK_OF_BASIC_DESIGN = 'Grundlegende Designdatei nicht gefunden';

}

