<?php

namespace Getresponse\WordPress\Tests;

use Getresponse\WordPress\FlashMessages;

/**
 * Class FlashMessagesTest
 * @package Getresponse\WordPress\Tests
 */
class FlashMessagesTest extends BaseTestCase
{
    
    /**
     * @test
     */
    public function shouldGetFlashErrorMessage()
    {
        $flashMessage = new FlashMessages();
        $flashMessage->addErrorMessage('message1');
        $flashMessage->addErrorMessage('message2');

        self::assertEquals(
            ['message1', 'message2'],
            $flashMessage->getErrorMessages()
        );

        self::assertEquals(
            [],
            $flashMessage->getErrorMessages()
        );
    }

    /**
     * @test
     */
    public function shouldGetFlashSuccessMessage()
    {
        $flashMessage = new FlashMessages();
        $flashMessage->addSuccessMessage('message1');
        $flashMessage->addSuccessMessage('message2');

        self::assertEquals(
            ['message1', 'message2'],
            $flashMessage->getSuccessMessages()
        );

        self::assertEquals(
            [],
            $flashMessage->getSuccessMessages()
        );
    }

}