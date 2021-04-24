<?php


namespace Phphleb\Muller;

use Phphleb\Muller\Src\DefaultMail;
use Phphleb\Muller\Src\Errors;

/**
 * Класс для отправки сообщений на E-mail
 * при помощи стандартной PHP-функции mail(...)
 * @package Phphleb\Muller
 */
final class StandardMail extends DefaultMail
{
    private $parameters = null;

    private $standardHeaders = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset="utf-8"',
        'X-Mailer' => 'Phphleb/Muller'
    ];

    /**
     * Добавление параметров
     * @param string $parameters
     */
    public function setParameters(string $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function send() {
        $this->standardDataValidate();

        // Проверка наличия ошибок
        if (count($this->errors)) {
            if ($this->debug) {
                $this->saveLogInFile();
            }
            return false;
        }

        $this->headers['To'] = $this->convertNamedRecipientsToString();

        $this->addingMissingHeaders($this->standardHeaders);

        $this->headers['From'] = $this->nameFrom . " <" . $this->addressFrom . ">";

        $this->createHtmlTemplate();

        if (!empty($this->logDirectory)) {
            $this->savePostInFile();
        }
        if (!$this->onlyFile) {
            return (bool)mail($this->convertRecipientsToString(), trim($this->title), trim($this->messageHtml), $this->convertHeadersToString(), $this->parameters);
        }
    }

    /**
     * @inheritDoc
     */
    protected function savePostInFile() {
        $emails = [];
        $email = $this->convertRecipientsToString();
        $nammedEmail = $this->convertNamedRecipientsToString();
        if (!empty(trim($email))) {
            $emails[] = $email;
        }
        if (!empty(trim($nammedEmail))) {
            $emails[] = $nammedEmail;
        }
        $txtEOL = '
';
        $headers = $this->headers;
        $headers['To'] = implode(', ', $emails);
        $content = '======================== Message (' . count($this->to) . ') ========================' . $txtEOL;
        $content .= 'Date: ' . date(DATE_RFC1123) . $txtEOL;
        $content .= 'Subject: ' . trim($this->title) . $txtEOL;
        foreach ($headers as $key => $value) {
            $content .= $key . ': ' . $value . $txtEOL;
        }
        $content .= $txtEOL;
        $content .= trim($this->messageHtml) . $txtEOL . $txtEOL;
        try {
            if (is_dir($this->logDirectory)) {
                $file = rtrim($this->logDirectory, "\\/ ") . DIRECTORY_SEPARATOR . date('Y-m-d') . '_mail.log';
                file_put_contents($file, $content, FILE_APPEND);
            }
        } catch (\Exception $exception) {
            $this->errors[] = Errors::ERROR_SAVE_EMAIL_LOG;
            error_log($exception->getMessage());
        }
    }

}

