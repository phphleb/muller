<?php

declare(strict_types=1);

namespace Phphleb\Muller\Src;

abstract class DefaultMail
{
    public const ALL_DESIGN = ['base', 'dark'];

    /* Значение для возможности множественных адресатов отправки */
    protected static $multiple = false;

    /* Имя отправителя */
    protected $nameFrom;

    /*  E-mail отправителя */
    protected $addressFrom;

    /* Заголовок письма */
    protected $title;

    /* Контент письма */
    protected $messageHtml;

    /* Отладочный режим */
    protected $debug = false;

    /* Путь до директории для сохранения логов ошибок отправки при включенном debug-режиме */
    protected $debugPath;

    /* Массив ошибок, возникших по ходу выполнения */
    protected $errors = [];

    /* Адресат(-ы) */
    protected $to = [];

    /* Регулярное выражение для проверки имени адресата */
    protected $patternUsername = '/^[\w\._\-\s\№\?\@\:\$\+\!\;]+$/iu';

    /* Регулярное выражение для проверки E-mail адресата */
    protected $patternMail = '/^[-_\+\w\.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,10}$/';

    /* Директория для сохранения писем в файлы */
    protected $logDirectory;

    /* Сохранять только в файл */
    protected $onlyFile = false;

    /* Заголовки для отправки */
    protected $headers = [];

    /* Установка HTML-шаблона для письма ('base', 'dark', ...)*/
    protected $design;

    /* Кодировка */
    protected $charset = 'UTF-8';

    /*  Шапка письма */
    protected $templateHeader = 'Account';

    /* Подпись в подвале письма  */
    protected $templateSign = 'Automatic writing';

    /**
     * Установка имени отправителя
     * @param string $name
     */
    public function setNameFrom(string $name) {
        $this->nameFrom = $name;
    }

    /**
     * Возвращает установленное ранее имя отправителя
     * @return string|null
     */
    public function getNameFrom() {
        return $this->nameFrom;
    }

    /**
     * Устанавливает кодировку письма
     * @param string $charset
     */
    public function setCharset(string $charset) {
        $this->charset = $charset;
    }

    /**
     * Установка адреса E-mail для отправителя
     * @param string $address
     */
    public function setAddressFrom(string $address) {
        $this->addressFrom = $address;
    }

    /**
     * Возвращает установленный ранее E-mail отправителя
     * @return string|null
     */
    public function getAddressFrom() {
        return $this->addressFrom;
    }

    /**
     * Установка заголовка для письма
     * @param string $title
     */
    public function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * Возвращает установленный ранее заголовок для письма
     * @return string $title
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Текст HTML содержимого письма
     * @param string $messageHtml
     */
    public function setContent(string $messageHtml) {
        if ($this->design === null && $this->messageHtml === null) {
            $this->messageHtml = $messageHtml;
        }
    }

    /**
     * Установка режима отладки
     * @param bool $debug
     */
    public function setDebug(bool $debug) {
        $this->debug = $debug;
    }

    /**
     * Устанавливает папку для сохранения логов
     * @param string $path
     */
    public function setDebugPath(string $path) {
        $this->debugPath = $path;
    }

    /**
     * Возвращает результат отправки письма
     * @return bool
     */
    abstract public function send();

    /**
     * Конструктор объекта для отправки писем
     * @param bool $multiple - разрешить отправку нескольким адресатам (!)
     */
    public function __construct(bool $multiple = false) {
        self::$multiple = $multiple;
    }

    /**
     * Назначение адресата
     * @param string $mail - E-mail
     * @param string|null $name - имя пользователя
     */
    public function setTo(string $mail, $name = null) {
        $this->createRecipient([(!\is_null($name) ? $name : 0) => $mail]);
    }

    /**
     * Возвращает массив с ошибками или их отсутствие
     * @return array|false
     */
    public function getErrors() {
        return \count($this->errors) ? $this->errors : false;
    }

    /**
     * Возвращает последнюю ошибку
     * @return string|false
     */
    public function getLastError() {
        return \count($this->errors) ? \end($this->errors) : false;
    }

    /**
     * Возвращает первую ошибку
     * @return string|false
     */
    public function getFirstError() {
        return \count($this->errors) ? $this->errors[0] : false;
    }

    /**
     * Назначение отправки множественным адресатам в массиве,
     * где ключ массива - имя, значение - E-mail
     * @param array $list
     * ['user@example.com']
     * ['User' => 'user@example.com']
     * ['User' => 'user@example.com', 'Another User' => 'anotheruser@example.com']
     */
    public function setToMultiple(array $list) {
        $this->createRecipient($list);
    }

    /**
     * Устанавливает директорию для сохранения пиисем в файлы
     * @param string $path
     */
    public function saveFileIntoDirectory(string $path) {
        $this->logDirectory = $path;
    }

    /**
     * Устанавливает сохранение только в файл
     * @param bool $toFile
     */
    public function saveOnlyToFile(bool $toFile = true) {
        $this->onlyFile = $toFile;
    }

    /**
     * Установка регулярного выражения для проверки имени пользователя (!)
     * @param string $pattern
     */
    public function setPatternFromUsername(string $pattern) {
        $this->patternUsername = $pattern;
    }

    /**
     * Установка регулярного выражения для проверки E-mail пользователя (!)
     * @param string $pattern
     */
    public function setPatternFromEmail(string $pattern) {
        $this->patternMail = $pattern;
    }

    /**
     * Добавление заголовков
     * @param array $headers
     */
    public function addHeaders(array $headers) {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }
    }

    /**
     * Добавление отдельного заголовка
     * @param string $name
     * @param string $value
     */
    public function setHeaders(string $name, string $value) {
        $this->headers[$name] = $name;
    }

    /**
     * Устанавливает шаблонный дизайн вместо использования ->setContent(...)
     * @param string $design
     * @param string $messageText
     */
    public function setMessage(string $design, string $messageText) {
        if (\is_null($this->design) && \is_null($this->messageHtml)) {
            $this->design = \in_array($design, self::ALL_DESIGN) ? $design : self::ALL_DESIGN[0];
            $this->messageHtml = $messageText;
        }
    }

    /**
     * Устанавливает заголовок письма для шаблона
     * @param string $text
     */
    public function setTemplateHeader(string $text) {
        $this->templateHeader = $text;
    }

    /**
     * Устанавливает подпись в подвале письма для шаблона
     * @param string $text
     */
    public function setTemplateSign(string $text) {
        $this->templateSign = $text;
    }

    /**
     * Возвращает результат проверки адреса E-mail
     * @param string $address
     * @return bool
     */
    protected function checkEmailAddress(string $address) {
        return (bool)\preg_match($this->patternMail, $address);
    }

    /**
     * Сортировка адресатов в общий список
     * @param array $list
     */
    protected function createRecipient(array $list) {
        foreach ($list as $key => $value) {
            if (\is_int($key)) {
                $this->to [] = [0 => $value];
            } else if (\is_string($key)) {
                $this->checkName($key);
                $this->to [] = [$key => $value];
            } else {
                $this->errors[] = \sprintf(Errors::ERROR_NAME_FORMAT, \gettype($key));
            }
        }
    }

    /**
     * Преобразование адресатов в строку через запятую
     * @return string
     */
    protected function convertRecipientsToString() {
        $list = [];
        foreach ($this->to as $item) {
            foreach ($item as $key => $value) {
                if (\is_int($key)) {
                    $list[] = $value;
                }
                break;
            }
        }
        return \implode(', ', $list);
    }

    /**
     * Преобразование адресатов в строку через запятую
     * @return string
     */
    protected function convertNamedRecipientsToString() {
        $list = [];
        foreach ($this->to as $item) {
            foreach ($item as $key => $value) {
                if (!\is_int($key)) {
                    $list[] = $key . ' <' . $value . '>';
                }
                break;
            }
        }
        return (string)\implode(', ', $list);
    }

    /**
     * Проверка имени пользователя с занесением ошибки при её возникновении
     * @param string $name
     */
    protected function checkName($name) {
        if (!$this->validateName($name)) {
            $this->errors[] = \sprintf(Errors::ERROR_VALIDATE_NAME, \htmlspecialchars($name));
        }
    }

    /**
     * Проверка E-mail пользователя с занесением ошибки при её возникновении
     * @param string $mail
     */
    protected function checkEmail($mail) {
        if (!$this->checkEmailAddress($mail)) {
            $this->errors[] = \sprintf(Errors::ERROR_VALIDATE_EMAIL, \htmlspecialchars($mail));
        }
    }

    /**
     * Проверка данных для имени пользователя
     * @param string $name
     * @return bool
     */
    protected function validateName(string $name) {
        return (bool)\preg_match($this->patternUsername, $name);
    }

    /**
     * Проверка заголовков
     */
    protected function checkHeaders() {
        foreach ($this->headers as $key => $value) {
            if (empty($key) || empty($value)) {
                $this->errors[] = Errors::ERROR_HEADERS_MISSING;
            }
        }
    }

    /**
     * Добавляет заголовки при их отсутствии
     * @param array $list
     */
    protected function addingMissingHeaders(array $list) {
        foreach ($list as $key => $value) {
            if (!isset($this->headers[$key])) {
                $this->headers[$key] = $value;
            }
        }
    }

    /**
     * Общие проверки добавленных данных
     */
    protected function standardDataValidate() {
        // Проверка наличия E-mail адресата
        if (!\count($this->to)) {
            $this->errors[] = Errors::ERROR_EMAIL_ADDRESS_NOT_SPECIFIED;
        }

        // Проверка разрешения множественных адресатов
        if (\count($this->to) > 1 && !self::$multiple) {
            $this->errors[] = Errors::ERROR_MULTIPLE_RECIPIENTS_ARE_PROHIBITED;
        }

        // Проверка наличия заголовка для письма
        if ($this->title === null || empty(\trim($this->title))) {
            $this->errors[] = Errors::ERROR_TITLE_MISSING;
        }

        // Валидация имени отправителя
        if (empty($this->nameFrom) || !$this->validateName($this->nameFrom)) {
            $this->errors[] = Errors::ERROR_USER_NAME_MISSING_OR_NOT_VALIDATED;
        }

        // Валидация E-mail отправителя
        if (empty($this->addressFrom) || !$this->checkEmailAddress($this->addressFrom)) {
            $this->errors[] = Errors::ERROR_EMAIL_MISSING_OR_NOT_VALIDATED;
        }

        // Проверка наличия контента для письма
        if ($this->messageHtml === null || empty(trim($this->messageHtml))) {
            $this->errors[] = Errors::ERROR_CONTENT_MISSING;
        }

        // Если не задана директория сохранения письма и сохранение только в файл
        if ($this->onlyFile && empty($this->logDirectory)) {
            $this->errors[] = Errors::ERROR_WRONG_CONDITION_TO_SAVE;
        }

        $this->checkHeaders();
    }

    /**
     * Преобразование заголовков в строку для отправки
     * @return string
     */
    protected function convertHeadersToString() {
        $result = '';
        foreach ($this->headers as $key => $value) {
            $result .= $key . ': ' . $value . "\r\n";
        }
        return $result;
    }

    /**
     * Сохранение логов в указанный файл
     * @return bool
     */
    protected function saveLogInFile() {
        try {
            if ($this->debugPath && \is_dir($this->debugPath)) {
                $file = $this->debugPath . DIRECTORY_SEPARATOR . \date('Y_m_d') . '_mail.error.log';
                $save = \file_put_contents($file, '[' . \date('Y-m-d H:i:s') . '] ' . Errors::ERROR_SEND_EMAIL . PHP_EOL, FILE_APPEND);
                if ($save) {
                    $num = 0;
                    foreach ($this->errors as $error) {
                        \file_put_contents($file, ' #' . ++$num . ' ' . $error . PHP_EOL, FILE_APPEND);
                    }
                }
                \file_put_contents($file, PHP_EOL, FILE_APPEND);
            } else {
                $this->errors[] = Errors::ERROR_SAVE_EMAIL_LOG;
            }
        } catch (\Exception $exception) {
            $this->errors[] = Errors::ERROR_SAVE_EMAIL_LOG;
            \error_log($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Сохранение письма в файл, если задан путь
     */
    abstract protected function savePostInFile();

    /**
     * Создание шаблона письма из предустановленных
     */
    protected function createHtmlTemplate() {
        if (empty($this->design)) return;

        if (!\file_exists(__DIR__ . '/../Templates/' . $this->design . '.php')) {
            $this->design = 'base';
        }
        $designFile = __DIR__ . '/../Templates/' . $this->design . '.php';
        if (\file_exists($designFile)) {
            $templateTitle = $this->title;
            $templateContent = $this->messageHtml;
            $templateCharset = $this->charset;
            $teplateHeader = $this->templateHeader;
            $templateSign = $this->templateSign;
            $templateSite = '';
            if (!empty($_SERVER['HTTP_HOST'])) {
                $templateSite = $_SERVER['HTTP_HOST'];
            }
            \ob_start();
            require "$designFile";
            $result = \ob_get_contents();
            \ob_end_clean();
            $this->messageHtml = $result;
        } else {
            $this->errors[] = Errors::ERROR_LACK_OF_BASIC_DESIGN;
        }
    }

}

