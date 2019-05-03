<?php
namespace Getresponse\WordPress;

/**
 * Class FlashMessages
 * @package Getresponse\WordPress
 */
class FlashMessages
{
    const ERROR_MESSAGE_INDEX = 'error';
    const SUCCESS_MESSAGE_INDEX = 'success';

    public function __construct()
    {
        if (!isset($_SESSION[self::ERROR_MESSAGE_INDEX])) {
            $_SESSION[self::ERROR_MESSAGE_INDEX] = [];
        }

        if (!isset($_SESSION[self::SUCCESS_MESSAGE_INDEX])) {
            $_SESSION[self::SUCCESS_MESSAGE_INDEX] = [];
        }
    }

    /**
     * @param string $message
     */
    public function addErrorMessage($message)
    {
        $this->addMessage($message, self::ERROR_MESSAGE_INDEX);
    }

    public function addSuccessMessage($message)
    {
        $this->addMessage($message, self::SUCCESS_MESSAGE_INDEX);
    }

    /**
     * @param string $message
     * @param string $type
     */
    private function addMessage($message, $type)
    {
        if (empty($message)) {
            return;
        }
        $_SESSION[$type][] = $message;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->getMessages(self::ERROR_MESSAGE_INDEX);
    }

    /**
     * @return array
     */
    public function getSuccessMessages()
    {
        return $this->getMessages(self::SUCCESS_MESSAGE_INDEX);
    }

    /**
     * @param string $type
     * @return array
     */
    private function getMessages($type)
    {
        if (!isset($_SESSION[$type])) {
            return [];
        }

        $messages = $_SESSION[$type];
        $_SESSION[$type] = [];

        return $messages;
    }
}